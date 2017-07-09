<?php
include '../core.php';

if(isset($_GET['auth'])){
    $user = getUser();
    if($user){
        if(isset($_POST['email'])){
            if(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) die('Invalid email');
            $res = $db->prepare("SELECT * FROM users WHERE email=?");
            $res->execute(array($_POST['email']));
            if($res->fetch()) die('Email taken');
            $db->prepare("UPDATE users SET email=? WHERE auth=?")->execute(array($_POST['email'],$user['auth']));
            die('success:New email set');
        }elseif(isset($_POST['pass'])){
            if(strlen($_POST['pass']) < 6) die('Password too short');
            $db->prepare("UPDATE users SET password=? WHERE auth=?")->execute(array(md5(md5($_POST['pass'])),$user['auth']));
            die('success:New password set');
        }
    }
}