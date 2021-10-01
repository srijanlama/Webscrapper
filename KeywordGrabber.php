<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use database\PdoCrud\PdoCrud;

require_once './PHPMailer/Exception.php';
require_once './PHPMailer/PHPMailer.php';
require_once './PHPMailer/SMTP.php';
require_once './database/PdoCrud.php';
require_once './simple_html_dom.php';


class KeywordGrabber
{

    private $url;

    private $page_location;

    private $keywords = [];

    private $recipients = [];

    public $results = [];

    private $db;

    private $mailer;

    function __construct($url, $keywords, $recipients)
    {
        $this->setUrl($url);
        $this->setKeywords($keywords);
        $this->setRecipients($recipients);
        $this->setConnection();
        $this->setUpMailer();
    }

    function setConnection()
    {
        $this->db = new PdoCrud();
        $this->db->connect(['dsn' => 'mysql:host=localhost;dbname=db_keywords', 'user' => 'root', 'password' => '']);
    }

    function setUpMailer()
    {
        $this->mailer = new PHPMailer(true);

        try {
            //Setup SMTP
            $this->mailer->isSMTP();                      // Set mailer to use SMTP 
            $this->mailer->Host = 'localhost';       // Specify main and backup SMTP servers 
            $this->mailer->SMTPAuth = false;               // Enable SMTP authentication 
            $this->mailer->Username = 'user@gmail.com';   // SMTP username 
            $this->mailer->Password = 'gmail_password';   // SMTP password 
            $this->mailer->SMTPSecure = '';            // Enable TLS encryption, `ssl` also accepted 
            $this->mailer->Port = 25;
        } catch (Exception $e) {
            die('Mailer Error occured!' . $e->getMessage());
        }
    }
    function setUrl($url)
    {
        $this->url = $url;
    }

    function setKeywords($keywords)
    {
        $this->keywords = explode(',', $keywords);
    }

    function setRecipients($recipients)
    {
        $this->recipients = explode(',', $recipients);
    }

    public function crawl()
    {

        $file = file_get_contents($this->url);
        $html = new simple_html_dom();
        $html->load($file);
        $data = $html->find('body', 0)->innertext;

        foreach ($this->keywords as $keyword) {
            if (strpos($data, $keyword) !== false) {
                array_push($this->results, $keyword);
            }
        }

        $this->updateUrl();
        return $this->results;
    }

    function updateUrl()
    {
        $result = $this->db->select('id, url', 'page', "url = '" . $this->url . "' ");
        $page = NULL;

        //if record exits
        if (count($result)) {
            $page = $result[0];
        } else {
            $id = $this->db->insert('page', ['url' => $this->url]);
            $result = $this->db->select('id, url', 'page', "id = {$id}");
            $page = $result[0];
        }

        $this->updateKeywords($page);
    }

    function updateKeywords($page)
    {
        $this->db->delete('keywords', " `page_id` = " . $page['id'] . ' ');

        if (count($this->results)) {
            foreach ($this->results as $result) {
                $this->db->insert('keywords', ['keyword' => $result, 'page_id' => $page['id']]);
            }

            $this->mailToRecipients($page);
        }
    }

    function mailToRecipients($page)
    {

        if (count($this->recipients) < 0) {
            return;
        }

        $this->db->delete('recipients', " `page_id` = " . $page['id'] . ' ');
        foreach($this->recipients as $recipient){
            $this->db->insert('recipients', ['email' => $recipient, 'page_id' => $page['id'] ]);
        }

        $this->mailer->Subject = 'Keyword Search';
        $this->mailer->Body = $this->url . ' has been searched and found these keyword' . implode(' ,  ', $this->results);
        $this->mailer->setFrom('crawl@example.com', 'Keyword Search');
        $this->mailer->addAddress($this->recipients[0]);
        $this->mailer->isHTML(true);

        if (count($this->recipients) > 1) {

            $counter = 0;
            foreach ($this->recipients as $recipient) {
                if ($counter > 0) {
                    $this->mailer->addBCC($recipient);
                }
                $counter++;
            }
        }

        $this->mailer->send();
    }
}
