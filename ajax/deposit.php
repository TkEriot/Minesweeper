<?php
include '../core.php';

if(isset($_GET['auth'])){
    $user = getUser();
    setcli($user['btc']);
    $uid = $user['id'];
    $confirmed = $unconfirmed = 0;
    if($user['lock_dep']) exit;
    lock($uid);
    $u = $btc?1000000:10000;
    foreach(cli('listtransactions',array($uid)) as $tx){
        if($tx['confirmations'] > 0){
            if(!$db->query("SELECT * FROM deposits WHERE txid='{$tx['txid']}' AND btc=$btc")->fetch()){
                $amount = floor($tx['amount'] * $u);
                $db->query("INSERT INTO deposits(uid,txid,amount,btc) VALUES($uid,'{$tx['txid']}',$amount,$btc)");
                $db->query("UPDATE users SET balance=balance+$amount WHERE id=$uid");
                $confirmed += (int)$amount;
            }
        }else{
            $amount = floor($tx['amount'] * $u);
            $unconfirmed += (int)$amount;
        }
    }
    unlock($uid);
    echo "$confirmed|$unconfirmed";
}