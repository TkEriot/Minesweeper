<?php
include 'core.php';
if(!isset($_GET['auth'])) makeRedirect();
$user = getUser();
if(!$user) makeRedirect();
include 'web/header.php';
?>
<center>
<div id="bet_container" class="ui segment stacked">
    <div class="column" style="margin-left:0px;">
        <div class="ui input focus" style="max-width:150px;max-height:40px;margin-right:4px;">
            <input type="text" id="bet" placeholder="Bet" maxlength="7" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 0">
        </div>
        <button id="double"class="ui grey button">x2</button> <button id="half" class="ui grey button">/2</button> <button id="max" class="ui grey button">ALL</button><br><br>
        <button class="ui blue button spbutton" onclick="splay(1)"><i class="icon bomb"></i>1</button>
        <button class="ui blue button orange spbutton" onclick="splay(3)"><i class="icon bomb"></i>3</button>
        <button class="ui blue button spbutton" onclick="splay(5)"><i class="icon bomb"></i>5</button>
        <button class="ui blue button spbutton" onclick="splay(24)"><i class="icon bomb"></i>24</button><br><br>
        <button id="play" class="ui blue button" onclick="play($(this))">Play</button> <button class="ui blue button" onclick="clean()">Clean</button> <button class="circular ui green icon button" id="sound" onclick="switchSound()"><i class='volume icon'></i></button>
    </div>
</div>
<div id="ltc_container" class="ui segment stacked">
    <div class="column" style="margin-left:0px;">
        <input type="button" value="Deposit" class="ui blue button halfb" onclick="dop=true;showModal('#deposit_modal',300)">
        <input type="button" value="Withdraw" class="ui blue button halfb" onclick="showModal('#withdraw_modal',200)">
        <div class="balance_text">Balance: <span id="balance"><?php echo $user['balance'];?></span></div>
    </div>
</div>
<div id="aff_container" class="ui segment stacked">
    <div class="column" style="margin-left:0px;">
        <span class="balance_text">Your affiliate url:</span><br><br>
        <div class="ui input focus" style="max-width:150px;max-height:40px;margin-right:4px;font-size:17px;">
            <input type="text" id="aff_url" onfocus="this.select()" onkeydown="return false;" value="https://sweepcoins.com/?r=<?php echo $user['id']+12052;?>">
        </div><br><br>
        <span class="aff_text">
            Share it and earn 5% of every win forever<br>
            You have <?php echo $db->query("SELECT COUNT(*) FROM users WHERE ref={$user['id']}")->fetchColumn();?> referrals
        </span>
    </div>
</div>
<div id="menu_container" class="ui segment stacked">
    <div class="column" style="margin-left:0px;">
        <div class="balance_text">Menu</div><br><br>
        <a href="#" onclick="showModal('#faq_modal',300)">FAQ</a>
        <div class="ui divider"></div>
        <a href="#" onclick="showModal('#fair_modal',300)">Provably Fair</a>
        <div class="ui divider"></div>
        <a href="mailto:sweepcoins@gmail.com">Contact</a>
        <div class="ui divider"></div>
        <a href="#" onclick="if(confirm('Want to leave? Make sure you saved the player url'))location.href='index.php';">Home</a>
    </div>
</div>
<div id="acc_container" class="ui segment stacked">
    <div class="column" style="margin-left:0px;">
        <div class="balance_text">Account</div><br>
        <span class="aff_text">
            Currency: <span class="currency"><?php echo $user['btc']?'BTC':'LTC';?></span>
        </span>
        <button class="ui blue button acc_button" style="margin-top:10px;" onclick="showModal('#email_modal',200)">Set Email</button>
        <button class="ui blue button acc_button" style="margin-top:10px;" onclick="showModal('#pass_modal',200)">Set Password</button>
    </div>
</div>
<div id="games">
<?php
$first = true;
foreach($db->query("SELECT * FROM games WHERE uid={$user['id']} ORDER BY time DESC")->fetchAll() as $game):
    $square = json_decode($game['square']);
    $hash = md5($game['secret']);
?>
    <div id='<?php echo $hash;?>' class='ui segment game stacked'<?php echo ($first?'':' style="margin-top:20px;"');?>>
        Hash: <span class='hash'><?php echo $hash;?></span><br><br>
        <button onclick='cashout($(this))' class='ui button yellow'>Cashout <?php echo number_format(getWin($square,$game['bet']));?></button>
        <span class="pnext">Next: <span class='next'><?php echo number_format(getNext($square,$game['bet']));?></span></span><br><br>
        <?php for($i=1;$i<26;$i++): ?>
        <button onclick='clickq(<?php echo ($i-1);?>,$(this))' class='ui icon button<?php echo ($square[$i-1]==1?' green':'');?>'
                style='width:62.25px;height:60px;<?php echo ($i%5==0?'margin-bottom:6px;':''); echo (($i-1)%5==0?'margin-left:5px;':'');?>'>
            <?php echo ($square[$i-1]==1?'<i class=\'checkmark icon\'></i>':'<i class=\'icon\'></i>');?></button> <?php echo ($i%5==0?'<br>':'');
        endfor; ?>
    </div>
<?php $first = false; endforeach; ?>
    <div class="ui negative message" style="max-width:400px;min-width:400px;margin-left:-1%;">
        <div class="header">
            Save your URL and NEVER share it with anyone
        </div>
    </div>
    <div class="ui positive message" style="max-width:400px;min-width:400px;margin-left:-1%;">
        <div class="header">
            Use bets higher than your balance for practice
        </div>
    </div>
</div>
<div id="fair_modal" style="display: none" class="uid modal">
    <center>
    <div class="ui segment stacked" style="max-width:500px;">
        <div class="ui input focus" style="min-width:430px;max-height:40px;margin-top:4px;"><input type="text" value="Hash" id="fair_hash" disabled></div><br><br>
        <div class="ui input focus" style="min-width:430px;max-height:40px;margin-top:4px;"><input type="text" placeholder="Secret" id="fair_secret"></div><br><br>
        <button class="ui blue button" style="min-width:150px;" onclick="fair()">Calculate</button><br><br>
        <div class="balance_text">How provably fair works?</div><br>
        The server generates a random string called secret before each game.<br>
        The secret is used to set the position of the bombs as:<br><br>
        <code class="code javascript">
            <b>bombIndex = rand[seed(secretString[charIndex] + nonceRand)]</b>
        </code><br><br>
            The user then receives the hash as MD5(secretString), at the end of the game the secret is sent and bombs positions revealed. This process guarantees that the server can't manipulate the game while the user is playing and it is therefore entirely up to your luck.
    </div>
    </center>
</div>
<div id="deposit_modal" style="display: none">
    <center>
        <div class="ui segment stacked" style="max-width:520px;">
            <div class="balance_text">Deposit Address</div><br>
                <div class="ui input focus" style="min-width:470px;max-height:40px;margin-top:4px;font-size:16px;;">
                    <input type="text" style="text-align:center;padding-bottom:10px;" onfocus="if(dop){this.blur();dop=false;}else this.select();" value="<?php echo $user['wallet'];?>">
                </div><br>
                <img src="http://chart.apis.google.com/chart?cht=qr&chs=250x250&choe=UTF-8&chld=H|0&chl=<?php echo $user['wallet'];?>"><br><br>
           <button class="ui blue button" onclick="check_deposit($(this))" style="min-width:200px;margin-left:4px;margin-top:-10px;">Check Deposits</button>
        </div>
    </center>
</div>
<div id="withdraw_modal" style="display: none">
    <center>
        <div class="ui segment stacked" style="max-width:500px;">
            <div class="balance_text">Instant Withdraw</div><br>
            <div class="ui input focus" style="min-width:418px;max-height:40px;margin-top:4px;"><input type="text" id="wallet_withdraw" placeholder="Your Address"></div><br><br>
            <div class="ui input focus" style="min-width:193px;max-height:40px;margin-top:4px;margin-right:20px;">
                <input type="text" id="amount_withdraw" placeholder="Amount" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 0">
            </div><button class="ui blue button" style="min-width:200px;" onclick="withdraw($(this))">Withdraw</button><br><br>
            <b>Minimum withdraw is 2000 while standard tax is 400</b>
        </div>
    </center>
</div>
<div id="faq_modal" style="display: none">
    <center>
        <div class="ui segment stacked" style="max-width:500px;">
            <div class="balance_text">How the game works?</div><br>
            A grid is generated containing a fixed number of "bombs" and it is up to the player to decide when to cashout.
            When the player clicks a free square he gets a reward, this amount is summed to the final stake.
            You can practice using a bet higher than your balance before starting a game.
            <div class="balance_text">How to deposit?</div><br>
            Press deposit on the left and send any amount to the address you see, remembering that:
            <b><?php if($user['btc']): ?>1 point = 1 bit = 0.000001 BTC<?php else: ?>1 point = 1 mcLTC = 0.0001 LTC<?php endif;?></b><br>
            Only one confirmation is necessary, you can use web wallets as well.
            <div class="balance_text">Can I have multiple accounts?</div><br>
            Yes, but you can't use the same email for all, every player can have up to 5 accounts.
            Please do not abuse the system, you may lose your accounts.
            <div class="balance_text">I need more help?</div><br>
            You can send us an email here: sweepcoins@gmail.com
        </div>
    </center>
</div>
<div id="email_modal" style="display: none">
    <center>
        <div class="ui segment stacked" style="max-width:460px;">
            <div class="ui input focus" style="min-width:300px;max-height:40px;margin-top:4px;margin-right:20px;">
                <input type="text" id="email" value="<?php echo $user['email'];?>" placeholder="Email">
            </div><button class="ui blue button" style="min-width:100px;" onclick="setemail($(this))">Set Email</button>
        </div>
    </center>
</div>
<div id="pass_modal" style="display: none">
    <center>
        <div class="ui segment stacked" style="max-width:460px;">
            <div class="ui input focus" style="min-width:280px;max-height:40px;margin-top:4px;margin-right:20px;">
                <input type="text" id="pass" placeholder="Password">
            </div><button class="ui blue button" style="min-width:100px;" onclick="setpass($(this))">Set Password</button>
        </div>
    </center>
</div>
</center>
<script>
    var dop = false; var cur = '<?php echo $user['btc']?'bits':'mcLTC';?>';
    var auth = '<?php echo $_GET['auth'];?>';
    $('#double').click(function(){
        var d = getBet()*2,max=1000000;
        setBet(d>max?max:d);
    });
    $('#half').click(function(){
        setBet(parseInt(getBet()/2));
    });
    $('#max').click(function(){
        setBet(getBalance());
    });
    $(window).on('beforeunload', function(){
        return 'Want to leave? Make sure you saved the player url';
    });
    var audio = localStorage.getItem('audio');
    $('#sound').find('i').addClass((audio==null||audio=='true')?'up':'off');
</script>
<script src="js/md5.js" type="text/javascript"></script>
<script src="js/game.js" type="text/javascript"></script>
<?php include 'web/footer.php'; ?>
