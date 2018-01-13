var eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';
var eventer = window[eventMethod];
var messageEvent = eventMethod == 'attachEvent' ? 'onmessage' : 'message';

// Listen to message from child window
eventer(messageEvent,function(e) {
    if(typeof e.data != 'undefined'){
        if(e.data.indexOf('webrtcNoti')!== -1){
            if(e.data.indexOf('webrtcNoti')!== -1){
                if(typeof(e.data.split('^')[1]) != 'undefined' && e.data.split('^')[1] == 'add'){
                    if(typeof(e.data.split('^')[2]) != 'undefined' && e.data.split('^')[2] == 'chrome'){
                        jqcc(document).find('body').prepend('<div id="webrtcArrow""><img src="../../images/notifyarrow.png"></div>');
                    }else{
                        jqcc(document).find('body').prepend('<div id="webrtcArrow">&nbsp;</div>');
                    }
                }
                if(typeof(e.data.split('^')[1]) != 'undefined' && e.data.split('^')[1] == 'remove'){
                    jqcc(document).find("#webrtcArrow").remove();
                }
            }
        }
    }
},false);
var isIE = /*@cc_on!@*/false || !!document.documentMode;
jqcc(function(){
    if(isIE) {
        jqcc('#ie_fix').show();
        jqcc('#webrtc').addClass('ie_iframefix');
        jqcc('#avchatButtons').addClass('ie_buttonfix');
    }
    jqcc('#webrtcArrow').click(function(){
        jqcc(this).remove();
    });
});
var controlparameters = JSON.stringify({
    type:'plugins',
    name:'cometchat',
    method:'setInternalVariable',
    params:{
        type:'endcallOnce',
        grp:grp,
        value:'0',
        chatroommode:chatroommode
    }
});
parent.postMessage('CC^CONTROL_'+controlparameters,'*');

controlparameters = JSON.stringify({
    'type':'plugins',
    'name':'cometchat',
    'method':'setInternalVariable',
    'params':{
        type:'endcallOnceWindow',
        grp:grp,
        value:'0',
        chatroommode:chatroommode
    }
});
parent.postMessage('CC^CONTROL_'+controlparameters,'*');

function endCall(caller){
    if(typeof(parent) === 'undefined' || parent == self){
        controlparameters = JSON.stringify({
            type:'plugins',
            name:'cometchat',
            method:'setInternalVariable',
            params:{
                type:'endcallOnceWindow',
                grp:grp,
                value:1,
                chatroommode:chatroommode
            }
        });
        window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');

        controlparameters = JSON.stringify({
            type:'plugins',
            name:'ccavchat',
            method:'end_call',
            params:{
                to:to,
                grp:grp,
                chatroommode:chatroommode
            }
        });
        window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');

        window.close();
    } else {
        controlparameters = JSON.stringify({
            type:'plugins',
            name:'ccavchat',
            method:'end_call',
            params:{
                to:to,
                grp:grp,
                chatroommode:chatroommode
            }
        });
        parent.postMessage('CC^CONTROL_'+controlparameters,'*');

        controlparameters = JSON.stringify({
            type:'plugins',
            name:'cometchat',
            method:'setInternalVariable',
            params:{
                type:'endcallOnce',
                grp:grp,
                value:'1',
                chatroommode:chatroommode
            }
        });
        parent.postMessage('CC^CONTROL_'+controlparameters,'*');
        if(caller){
            controlparameters = JSON.stringify({
                type:'plugins',
                name:'audiovideochat',
                method:'closeCCPopup',
                params:{
                    name:'audiovideochat'
                }
            });
            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
        }
    }
}

function closeWin(){
    if(typeof(parent) === 'undefined'  || parent == self){
        controlparameters = JSON.stringify({
            type:'plugins',
            name:'ccavchat',
            method:'end_call',
            params:{
                to:to,
                grp:grp,
                chatroommode:chatroommode
            }
        });
        window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
        window.close();
    } else {
        controlparameters = JSON.stringify({
            type:'plugins',
            name:'ccavchat',
            method:'end_call',
            params:{
                to:to,
                grp:grp,
                chatroommode:chatroommode
            }
        });
        parent.postMessage('CC^CONTROL_'+controlparameters,'*');

        controlparameters = JSON.stringify({
            type:'plugins',
            name:'audiovideochat',
            method:'closeCCPopup',
            params:{
                name:'audiovideochat'
            }
        });
        parent.postMessage('CC^CONTROL_'+controlparameters,'*');
    }
}
