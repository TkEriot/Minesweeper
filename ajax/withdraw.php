<?php
include '../core.php';

if(isset($_GET['auth'])){
    $user = getUser();
    setcli($user['btc']);
    $uid = $user['id'];
    $amount = (int)$_POST['amount'];
    if($amount < 2000) die('Minimum withdraw is 2000');
    if($amount > $user['balance']) die('Not enough funds');
    if($user['lock_dep']) exit;
    lock($uid);
    $u = $btc?1000000:10000;
    if(floor(cli('getbalance') * $u) >= $amount) {
        $db->query("UPDATE users SET balance=balance-$amount WHERE id=$uid");
        $tx = cli('sendtoaddress', array($_POST['wallet'], ($amount-400) / $u));
        if ($tx) {
            $db->query("INSERT INTO withdraws(uid,txid,amount,btc) VALUES($uid,'{$tx}',$amount,$btc)");
            echo $tx . '|' . $amount;
        }
        else
            echo 'Unexpected error happened';
    }else
        echo 'Server error, retry later';
    unlock($uid);
}