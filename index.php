<?php
include 'core.php';
if(isset($_GET['r'])) setcookie('r',(int)$_GET['r'],time()+86400*10);
include 'web/header.php';
?>
<center>
<h1 id="logo"><img src="img/logo.png" height="50"></h1>
<div style="max-width:1000px;font-size: 18px;font-weight: bold;padding-top:30px;" class="ui segment stacked">
    SweepCoins is a minesweeper game that you can play to gamble with Bitcoin or Litecoin.<br><br>
    Each player has an unique url for accessing the game, do not share the url for any reason.<br><br>
    You can earn free balance using our affiliate program to invite other players.<br><br>
    <button class="ui button green" style="padding:15px 36px 15px 36px;margin-right:30px;font-size:18px;" onclick="location.href='play.php?btc=1';">Play with BTC</button>
    <button class="ui button green" style="padding:15px 36px 15px 36px;margin-right:30px;font-size:18px;" onclick="location.href='play.php?btc=0';">Play with LTC</button>
    <button class="ui button green" style="padding:15px 36px 15px 36px;margin-right:30px;font-size:18px;" onclick="showModal('#login_modal',300)">Sign In</button><br><br>
    <img src="img/test2.jpg" style="margin-bottom:2.5px;">  <img src="img/test3.jpg"> <img src="img/test4.jpg" style="margin-bottom:4px;">
    <br><br>
    There are <span class="stats"><?php echo $db->query("SELECT COUNT(*) FROM users WHERE (".time()."-online)<1800")->fetchColumn();?></span> online players and <span class="stats"><?php echo $db->query("SELECT COUNT(*) FROM games WHERE (".time()."-time)<1800")->fetchColumn();?></span> active games
    <br><br>
    <span style="font-size:14px">Copyright <?php echo date('Y');?> SweepCoins</span>
</div>
<div id="login_modal" style="display: none">
    <center>
        <div class="ui segment stacked" style="max-width:500px;">
            <div class="ui input focus" style="min-width:300px;max-height:40px;margin-top:4px;">
                <input type="text" id="email" placeholder="Email">
            </div><br><br>
            <div class="ui input focus" style="min-width:300px;max-height:40px;margin-top:4px;">
                <input type="password" id="pass" placeholder="Password" onkeyup="if(event.keyCode==13)login($('button').eq(3))">
            </div><br><br>
            <div class="g-recaptcha" data-sitekey="6Lcn0SQUAAAAAGwIoQTSwWKmXJ-p8kXu-zgeNIEA"></div><br>
            <button class="ui blue button" style="min-width:150px;" onclick="login($(this))">Login</button>
        </div>
    </center>
</div>
</center>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script>
    function login(elem){
        elem.addClass('loading');
        $.post('ajax/login.php',{pass:$('#pass').val(),email:$('#email').val(),"g-recaptcha-response":grecaptcha.getResponse()},function(data){
           elem.removeClass('loading');
           if(data.indexOf(':') > -1){
               location.href = data.split(':')[1];
           }else {
               error(data,elem.parent());
               grecaptcha.reset();
           }
        });
    }
</script>
<?php include 'web/footer.php'; ?>