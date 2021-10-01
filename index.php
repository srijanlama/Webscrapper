<?php 

require_once('./KeywordGrabber.php');

if($_POST){

    $url = $_POST['url'];
    $keywords = $_POST['keywords'];
    $email = $_POST['email'];

    $keywordGrabber = new KeywordGrabber($url, $keywords, $email);
    $results= $keywordGrabber->crawl();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($results);
    exit;
}
