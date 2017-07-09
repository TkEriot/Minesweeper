<?php
include '../core.php';

if(isset($_POST['email'])){
    if(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) die('Invalid email');
    if(strlen($_POST['pass']) < 6) die('Invalid password');
    verifyCaptcha();
    $res = $db->prepare("SELECT auth FROM users WHERE email=? AND password=?");
    $res->execute(array($_POST['email'],md5(md5($_POST['pass']))));
    if($auth = $res->fetchColumn())
        die('success:'.$auth);
    die('Wrong email and or password');
}

function verifyCaptcha(){
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch,array(
        CURLOPT_POST => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS => array(
            'secret' => '6Lcn0SQUAAAAACXdBnCpKDE4BEcnhEFg-8KqV9PG',
            'response' => $_POST['g-recaptcha-response'],
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ),
        CURLOPT_RETURNTRANSFER => true
    ));
    $resp = json_decode(curl_exec($ch));
    if(!is_object($resp) || !$resp->success)
        die('Wrong captcha, try again!');
}