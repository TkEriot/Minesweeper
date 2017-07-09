<?php
error_reporting(0);
set_time_limit(30);
$db = new PDO('mysql:dbname=ltcmines;host=localhost', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$server_ip = '127.0.0.1';
$server_btc = 'http://EVK87D6754SD37G535G723SG4897FA:EZKD8SO64D9GD6AS2DA8K1AS3G584AS@'.$server_ip.':587/';
$server_ltc = 'http://EVK87D6754SD37G535G723SG4897FA:EZKD8SO64D9GD6AS2DA8K1AS3G584AS@'.$server_ip.':588/';
$bitcoin = null; $litecoin = null; $btc = true;
function cli($command,array $params=array()){
    global $btc,$server_btc,$server_ltc,$bitcoin,$litecoin;
    $coin = $btc?$bitcoin:$litecoin;
    if(is_null($coin)){
        if(!class_exists('jsonRPCClient')) include 'jsonRPCClient.php';
        $coin = new jsonRPCClient($btc?$server_btc:$server_ltc);
    }
    try{
        return $coin->__call($command,$params);
    }catch(Exception $e){
        return false;
    }
}
function setcli($type){
    global $btc;
    $btc = $type?1:0;
}

function makeRedirect(){
    global $db;
    if($db->query("SELECT COUNT(*) FROM users WHERE ip='{$_SERVER['REMOTE_ADDR']}'")->fetchColumn() > 5)
        die('Limit reached for this endpoint');
    $auth = md5(uniqid(null,true)).substr(md5(uniqid(null,true)),0,8);
    $ref = isset($_COOKIE['r'])?(int)$_COOKIE['r']-12052:0;
    $btc = isset($_GET['btc'])?(int)$_GET['btc']:1;
    setcli($btc);
    $wallet = cli('getnewaddress');
    $db->query("INSERT INTO users(auth,ref,wallet,ip,btc) VALUES('$auth','$ref','$wallet','{$_SERVER['REMOTE_ADDR']}',$btc)");
    $uid = $db->lastInsertId();
    cli('setaccount',array($wallet,$uid));
    $secret = md5(str_replace('.', '#', uniqid(null, true)));
    $secret_final = substr(chunk_split($secret, 4, '-'), 0, 39);
    $square = array_fill(0, 25, 0);
    for ($i = 0; $i < 3; $i++) {
        srand(ord($secret[$i]) . rand(0, 10000));
        $index = rand(0, 24);
        if (!$square[$index]) $square[$index] = 2; else $i--;
    }
    $json = json_encode($square);
    $db->query("INSERT INTO games(uid,secret,square,bet,time,test) VALUES($uid,'$secret_final','$json',10000,".time().",1)");
    header('Location: '.$auth);
    exit;
}

function getUser(){
    global $db;
    $db->prepare("UPDATE users SET online=".time()." WHERE auth=?")->execute(array($_GET['auth']));
    $user = $db->prepare("SELECT * FROM users WHERE auth=?");
    $user->execute(array($_GET['auth']));
    return $user->fetch();
}

function getWin($square,$bet){
    $values = array_count_values($square);
    if($values[2] == 24 && isset($values[1])) return floor($bet * 23.04) + $bet;
    $sum = $bet;
    if(isset($values[1])) {
        $divs = array('1' => array(25.032, 0.5, 0), '3' => array(7.639, 0.42, 0.16), '5' => array(4.166, 0.22, 0.16));
        $div = $divs[(string)$values[2]];
        for ($i = 0; $i < $values[1]; $i++) {
            $sum += floor($sum / $div[0]);
            $div[0] -= ($div[1] - ($i < 1 ? $div[2] : 0));
        }
    }
    return $sum;
}

function getNext($square,$bet){
    $base = getWin($square,$bet);
    foreach($square as $k=>$v){
        if($v==0)
        {
            $square[$k] = 1;
            return getWin($square,$bet)-$base;
        }
    }
}

function lock($uid){
    global $db;
    $db->query("UPDATE users SET lock_dep=1 WHERE id=$uid");
}
function unlock($uid){
    global $db;
    $db->query("UPDATE users SET lock_dep=0 WHERE id=$uid");
}