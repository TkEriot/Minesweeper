var bombs = 3, bindex = {1:3,3:4,5:5,24:6};
function splay(b){
    if(b != bombs) {
        $('#bet_container').find('button').eq(bindex[bombs]).removeClass('orange').addClass('blue');
        $('#bet_container').find('button').eq(bindex[b]).removeClass('blue').addClass('orange');
        bombs = b;
    }
}
function play(elem){
    elem.addClass('loading');
    var bet = getBet();
    $.get('ajax/game.php?auth='+auth+'&create='+bombs+'&bet='+bet,function(data){
        elem.removeClass('loading');
        if(data.indexOf(':') > -1) {
            data = data.split(':');
            if(data[2]=='0') updateBalance(-bet);
            make_square(data[0],data[1],bet);
        }
        else
            error(data);
    });
}

function make_square(hash,next,stake){
    cleanGames();
    $('.game').eq(0).css('margin-top','20px');
    var buttons = "Hash: <span class='hash'>"+hash+"</span><br><br>";
    buttons += "<button onclick='cashout($(this))' class='ui button yellow'>Cashout "+thFormat(stake)+"</button> <span class='pnext'>Next: <span class='next'>"+thFormat(next)+"</span></span><br><br>";
    for(var i=1;i<26;i++)
        buttons += "<button onclick='clickq("+(i-1)+",$(this))' class='ui icon button' style='width:62.25px;height:60px;"+
            (i%5==0?'margin-bottom:6px;':'')+((i-1)%5==0?'margin-left:5px;':'')+"'><i class='icon'></i></button> "+(i%5==0?'<br>':'');
    $('#games').prepend("<div id='"+hash+"' class='ui segment game stacked'>"+buttons+"</div>");
}
function cleanGames(all){
    var len = $('.game').length;
    if(len > 4 || all){
        for(var i=len-1; i>=0; i--) {
            if ($('.game').eq(i).html().indexOf('Secret:') > -1){
                $('.game').eq(i).remove();
                if(!all) break;
            }
        }
    }
}
function clean(){
    splay(3);
    setBet('');
    cleanGames(true);
    $('.game').eq(0).css('margin-top','0px');
}

var success = new Audio('sound/success.mp3'), bomb = new Audio('sound/bomb.mp3');
success.volume = bomb.volume = 1;
function switchSound(){
    audio = (audio=='true'||audio==null?'false':'true');
    localStorage.setItem('audio',audio);
    $('#sound').find('i').addClass(audio=='true'?'up':'off').removeClass(audio=='true'?'off':'up');
}
function clickq(square,elem){
    elem.addClass('loading');
    var hash = elem.parent().attr('id');
    $.get('ajax/game.php?hash='+hash+'&auth='+auth+'&click='+square,function(data){
        elem.removeClass('loading');
        if(data.indexOf(':') > -1){
            data = data.split(':');
            if(data[0] == 'bomb') {
                if(audio==null || audio=='true') {
                    bomb.currentTime = 0;
                    bomb.play();
                }
                elem.addClass('red').html('<i class=\'bomb icon\'></i>');
                elem.parent().find('button').eq(0).addClass('red').removeClass('yellow').html('Lost '+thFormat(data[2]));
                elem.parent().prepend('Secret: '+data[1]+'<br>');
                showBombs(elem,data[3]);
            }else {
                if(audio==null || audio=='true') {
                    success.currentTime = 0;
                    success.play();
                }
                elem.addClass('green').html(formatEarn(data[1]));
                elem.parent().find('.next').html(thFormat(data[2]));
                elem.parent().find('button').eq(0).html('Cashout '+thFormat(data[3]));
            }
        }
    })
}
function showBombs(elem,square){
    square = JSON.parse(square);
    var bts = elem.parent().find('button');
    for(var i=0;i<square.length;i++)
        if(square[i] == 2)
            bts.eq(i+1).addClass('red').html('<i class=\'bomb icon\'></i>');
}
function formatEarn(num){
    return num > 999 ? (num/1000).toFixed(1) + 'k' : num;
}
function thFormat(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function cashout(elem){
    var hash = elem.parent().attr('id');
    $.get('ajax/game.php?hash='+hash+'&auth='+auth+'&cashout=1',function(data){
        if(data.indexOf(':') > -1) {
            data = data.split(':');
            if(data[4]=='0') updateBalance(data[2]);
            elem.parent().find('button').eq(0).addClass('green').removeClass('yellow').html('Cashed out '+thFormat(data[2]));
            elem.parent().prepend('Secret: ' + data[1] + '<br>');
            showBombs(elem, data[3]);
        }
    });
}

function getBet(){
    var b = parseInt($('#bet').val());
    return isNaN(b)?0:b;
}
function setBet(num){
    $('#bet').val(num);
}
function updateBalance(num){
    $('#balance').html(getBalance()+parseInt(num));
}
function getBalance(){
    return parseInt($('#balance').html());
}
function disableAll(elem){
    elem.parent().find('button').attr('disabled','disabled');
}

function check_deposit(elem){
    elem.addClass('loading');
    $.get('ajax/deposit.php?auth='+auth,function(data){
        elem.removeClass('loading');
        data = data.split('|');
        if(data[0] > 0) {
            updateBalance(data[0]);
            error("Received "+data[0]+" "+cur,elem.parent(), true, 4000);
        }
        if(data[1] > 0)
            error("Waiting confirmation for "+data[1]+" "+cur,elem.parent(),false,4000);
        if((data[0]+data[1]) < 1)
            error("No funds received yet",elem.parent());
    })
}
function withdraw(elem){
    elem.addClass('loading');
    $.post('ajax/withdraw.php?auth='+auth,{wallet:$('#wallet_withdraw').val(),amount:$('#amount_withdraw').val()},function(data){
        elem.removeClass('loading');
        if(data.indexOf('|') > -1) {
            data = data.split('|');
            updateBalance(-data[1]);
            error("<a href='https://live.blockcypher.com/"+(cur=='bits'?'btc':'ltc')+"/tx/"+data[0]+"'>TXID</a>: Withdraw of "+data[1]+" "+cur+" successful",elem.parent(),true,6000);
        }
        else
            error(data,elem.parent());
    })
}
function fair(){
    $('#fair_hash').val(MD5($('#fair_secret').val()));
}

function setemail(elem){
    elem.addClass('loading');
    $.post('ajax/account.php?auth='+auth,{email:$('#email').val()},function(data){
        elem.removeClass('loading');
        if(data.indexOf(':') > -1)
            error(data.split(':')[1],elem.parent(),true);
        else
            error(data,elem.parent());
    });
}
function setpass(elem){
    elem.addClass('loading');
    $.post('ajax/account.php?auth='+auth,{pass:$('#pass').val()},function(data){
        elem.removeClass('loading');
        if(data.indexOf(':') > -1)
            error(data.split(':')[1],elem.parent(),true);
        else
            error(data,elem.parent());
    });
}