function showModal(modal,top){
    $(modal).modal({closable : false}).modal('show');
    $(modal).find('div').eq(0).css('margin-top',top+'px').click(function(evt){ evt.stopPropagation(); });
    $(modal).modal('refresh');
    $('.dimmer').click(function(){ $(modal).modal('hide'); });
}
var errs = 0;
function error(error,def,pos,time){
    errs++;
    (def?def:$('#games')).prepend('<div class="ui '+(pos?'positive':'negative')+' message err'+errs+'" style="max-width:400px;min-width:400px;margin-left:-1%;"><div class="header">'+error+'</div> </div>');
    setTimeout(function(er){ $('.err'+er).remove(); if(er==errs) errs = 0; },(time?time:3000),errs);
}