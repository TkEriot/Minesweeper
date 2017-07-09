<?php
include '../core.php';
if(isset($_GET['auth'])){
    $user = getUser();
    if($user){
        $uid = $user['id'];
        if(isset($_GET['create'],$_GET['bet'])) {
            if($db->query("SELECT COUNT(*) FROM games WHERE uid=$uid")->fetchColumn() > 4) die('Maximum 5 games open');
            $bet = (int)$_GET['bet'];
            $bombs = (int)$_GET['create'];
            if ($bet < 30) die('Bet must be at least 30');
            if ($bet > 1000000) die('Bet cannot be higher than 1,000,000');
            $test = $bet > $user['balance'] ? 1 : 0;
            if (!in_array($bombs, array(1, 3, 5, 24))) die('Invalid bombs amount');
            $secret = md5(str_replace('.', '#', uniqid(null, true)));
            $secret_final = substr(chunk_split($secret, 4, '-'), 0, 39);
            $hash = md5($secret_final);
            if($bombs < 24) {
                $square = array_fill(0, 25, 0);
                for ($i = 0; $i < $bombs; $i++) {
                    srand(ord($secret[$i]) . rand(0, 10000));
                    $index = rand(0, 24);
                    if (!$square[$index]) $square[$index] = 2; else $i--;
                }
            }else{
                $square = array_fill(0, 25, 2);
                srand(ord($secret[0]) . rand(0, 10000));
                $square[rand(0, 24)] = 0;
            }
            $json = json_encode($square);
            if(!$test) $db->query("UPDATE users SET balance=balance-$bet WHERE id=$uid");
            $db->query("INSERT INTO games(uid,secret,square,bet,time,test) VALUES($uid,'$secret_final','$json',$bet,".time().",$test)");
            die($hash.':'.getNext($square,$bet).':'.$test);
        }else if(isset($_GET['hash'])){
            $game = getGame($user['id'],$_GET['hash']);
            if($game){
                $bet = $game['bet'];
                $square = json_decode($game['square']);
                if(isset($_GET['click'])){
                    $click = (int)$_GET['click'];
                    if(isset($square[$click])){
                        switch($square[$click]){
                            case 2:
                                deleteGame($game);
                                die('bomb:'.$game['secret'].':'.getWin($square,$bet).':'.$game['square']);
                                break;
                            case 1:
                                die('invalid');
                                break;
                            case 0:
                                $base = getWin($square,$bet);
                                $square[$click] = 1;
                                $final = getWin($square,$bet);
                                $earn = $final-$base;
                                updateGame($game,$square);
                                die('found:'.$earn.':'.getNext($square,$bet).':'.$final);
                                break;
                        }
                    }
                }elseif(isset($_GET['cashout'])){
                    deleteGame($game);
                    $win = getWin($square,$bet);
                    if(!$game['test']){
                        $diff = $win-$bet;
                        $db->query("UPDATE users SET balance=balance+".(int)($diff/20)." WHERE id={$user['ref']} AND btc={$user['btc']}");
                        $db->query("UPDATE users SET balance=balance+$win WHERE id={$user['id']}");
                    }
                    die('win:'.$game['secret'].':'.$win.':'.$game['square'].':'.$game['test']);
                }
            }
        }
    }
}

function getGame($uid,$hash){
    global $db;
    $game = $db->prepare("SELECT * FROM games WHERE uid=? AND MD5(secret)=?");
    $game->execute(array($uid,$hash));
    return $game->fetch();
}

function deleteGame($game){
    global $db;
    $db->query("DELETE FROM games WHERE uid={$game['uid']} AND secret='{$game['secret']}'");
}

function updateGame($game,$square){
    global $db;
    $square = json_encode($square);
    $db->query("UPDATE games SET square='$square' WHERE uid={$game['uid']} AND secret='{$game['secret']}'");
}