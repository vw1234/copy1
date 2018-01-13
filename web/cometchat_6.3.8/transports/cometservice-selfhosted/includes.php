<?php
 /*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/
 $callbackfn = '';
 if(!empty($_GET['callbackfn']) && $_GET['callbackfn'] == 'desktop'){
    $desktopmode = 1;
 }else{
    $desktopmode = 0;
 }
 include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'comet.js');
 include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
 ?>
var cometid = '';
var devmode = '<?php echo DEV_MODE;?>';
var COMET = new cs.webrtc2.client("<?php echo CS_TEXTCHAT_SERVER; ?>/cometservice.ashx");
var cslog = function(text){
    if(devmode=='1'){
        console.log(text);
    }
}

<?php
if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php')) {
    include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php');
    if($useGoogle == 1 && !empty($googleKey)){
?>
    cc_translate_use_google = 1;
<?php
    }
}
?>

COMET.connect({
    onSuccess: function(e){
        if (typeof(cometready) == 'function' ) {
          if(!((window.top!=window.self)&&('<?php echo $theme; ?>'=='docked'))){
              cometready();
          }
        }
        if (typeof(cometchatroomready) == 'function' ) {
            cometchatroomready();
        }
        if (typeof(chatroomready) == 'function' ) {
            chatroomready();
        }
        cslog("Connect success!");
    },
    onFailure: function(e){
        cslog("Connect failure!");
    },
    onStreamFailure: function(e){
        cslog("stream failure!");
    }
});

function cometcall_function(id, td, calleeAPI){
    if(id.charAt(0)!="/"){
        id = "/"+id;
    }
    COMET.subscribe({
        channel: id,
        onSuccess: function(e){
            cslog("subscribe success! "+id);
        },
        onFailure: function(e){
            cslog("subscribe failure! "+id);
        },
        onReceive: function(e){
            var incoming=e.getData();
            incoming.selfadded=0;
            incoming.old=0;
            cslog('received message!'+ JSON.stringify(incoming));
            if(jqcc.cookie('<?php echo $cookiePrefix;?>lang')&& incoming.fromid != jqcc.cometchat.getUserID() && (incoming.message).indexOf('CC^CONTROL_') == -1 && cc_translate_use_google){
                var lang = jqcc.cookie('<?php echo $cookiePrefix;?>lang');
                jqcc.ajax({
                    url: "https://www.googleapis.com/language/translate/v2?key=<?php echo $googleKey;?>&callback=?",
                    data: {q: incoming.message, target: lang},
                    dataType: 'jsonp',
                    success: function(data){
                        if(typeof(data.data)!="undefined"){
                            incoming.message = data.data.translations[0].translatedText+' <span class="untranslatedtext">('+incoming.message+')</span>';
                        }
                        if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                            jqcc[calleeAPI].addMessages([incoming]);
                        }
                    }
                });
            }else{
                if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                    jqcc[calleeAPI].addMessages([incoming]);
                }
            }
        }
    });
}


function chatroomcall_function(id,userid){
    if(id.charAt(0)!="/"){
        id = "/"+id;
    }
    COMET.subscribe({
        channel: id,
        onSuccess: function(e){
            cslog("subscribe success! "+id);
        },
        onFailure: function(e){
            cslog("subscribe failure! "+id);
        },
        onReceive: function(e){
            var incoming=e.getData();
            incoming.calledfromsend=0;
            jqcc.cometchat.setChatroomVars('newMessages', jqcc.cometchat.getChatroomVars('newMessages')+1);
            cslog('received message!'+ JSON.stringify(incoming));
           if(jqcc.cookie('<?php echo $cookiePrefix;?>lang') && incoming.fromid != userid && (incoming.message).indexOf('CC^CONTROL_') == -1 && cc_translate_use_google){
               var lang = jqcc.cookie('<?php echo $cookiePrefix;?>lang');
               jqcc.ajax({
                   url: "https://www.googleapis.com/language/translate/v2?key=<?php echo $googleKey;?>&callback=?",
                   data: {q: incoming.message, target: lang},
                   dataType: 'jsonp',
                   success: function(data){
                       if(typeof(data.data)!="undefined"){
                           incoming.message = data.data.translations[0].translatedText+' <span class="untranslatedtext">('+incoming.message+')</span>';
                       }
                       $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
                       $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, incoming.id, '1', parseInt(incoming.sent), incoming.from, '0', incoming.roomid);
                   }
               });
           }else{
               $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
               $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, incoming.id, '1', parseInt(incoming.sent), incoming.from, '0', incoming.roomid);
           }
        }
    });
}

function cometuncall_function(id){
    if(id.charAt(0)!="/"){
        id = "/"+id;
    }
    COMET.unsubscribe({
        channel: id,
        onComplete: function() {
            cslog('unsubscribe complete! '+id);
        },
        onSuccess: function(args) {
            cslog('unsubscribe win! '+id);
        },
        onFailure: function(args) {
            cslog('unsubscribe fail! '+id);
        }
    });
}
