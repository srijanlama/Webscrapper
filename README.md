# Webscrapper
php script for webscrapper along with php mailer

### Folder Structure

    

    
Quick Setup
        
       Upload the database provided to your database  <br/>
       Open file KeywordGrabber.php Edit correct database credential to connect
       
      
        function setConnection()
        {
            $this->db = new PdoCrud();
            $this->db->connect(['dsn' => 'mysql:host=localhost;dbname=db_keywords', 'user' => 'root', 'password' => '']);
        }

       
        
        Apply your SMTP credential for mailer
        
        function setUpMailer()
        {
                $this->mailer = new PHPMailer(true);

                try {
                    //Setup SMTP
                    $this->mailer->isSMTP();                      // Set mailer to use SMTP 
                    $this->mailer->Host = 'localhost';            // Specify main and backup SMTP servers 
                    $this->mailer->SMTPAuth = false;              // Enable SMTP authentication 
                    $this->mailer->Username = 'user@gmail.com';   // SMTP username 
                    $this->mailer->Password = 'gmail_password';   // SMTP password 
                    $this->mailer->SMTPSecure = '';               // Enable TLS encryption, `ssl` also accepted 
                    $this->mailer->Port = 25;
                } catch (Exception $e) {
                    die('Mailer Error occured!' . $e->getMessage());
                }
            }
            
         
   ### Run Application 
   Place files and folder to server document root or for local environment
   ```
   cd keyword-search
   php -S localhost:3000
   ```
  
