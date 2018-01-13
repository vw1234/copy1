/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
 */
 <?php $callbackfn = ''; if (!empty($_GET['callbackfn'])) { $callbackfn = $_GET['callbackfn']; }?>

; if (!Object.keys) Object.keys = function(o) {if (o !== Object(o))throw new TypeError('Object.keys called on a non-object');var k=[],p;for (p in o) if(Object.prototype.hasOwnProperty.call(o,p)) k.push(p);return k;};

(function($){
    $.cometchat = $.cometchat||function(){
        var baseUrl = '<?php echo BASE_URL;?>';
        var ccvariable = {};
        var sendajax = true;
        var broadcastData = [];
        var sendbroadcastinterval = 0;
        var transport = '<?php echo TRANSPORT;?>';
        var webrtcplugins = ['audiovideochat', 'audiochat', 'broadcast', 'screenshare'];
        <?php echo $jssettings; ?>
        <?php echo $mobileappdetails; ?>
        ccvariable.documentTitle = document.title;
        ccvariable.externalVars = {};
        ccvariable.sendVars = {};
        ccvariable.sessionVars = {};
        ccvariable.internalVars = {};
        ccvariable.openChatboxId = '';
        ccvariable.loggedout = 0;
        ccvariable.offline = 0;
        ccvariable.windowFocus = true;
        ccvariable.resynchronize = 0;
        ccvariable.heartbeatTimer;
        ccvariable.heartbeatTime = settings.minHeartbeat;
        ccvariable.heartbeatCount = 1;
        ccvariable.updateSessionVars = 0;
        ccvariable.timestamp = 0;
        ccvariable.lastOnlineNumber = 0;
        ccvariable.trayOpen = '';
        ccvariable.chatroomOpen = '';
        ccvariable.newMessages = 0;
        ccvariable.buddylistName = {};
        ccvariable.buddylistMessage = {};
        ccvariable.buddylistStatus = {};
        ccvariable.buddylistAvatar = {};
        ccvariable.buddylistLink = {};
        ccvariable.buddylistIsDevice = {};
        ccvariable.buddylistChannelHash = {};
        ccvariable.buddylistLastseen = {};
        ccvariable.buddylistLastseensetting = {};
        ccvariable.buddylistUnreadMessageCount = {};
        ccvariable.isJabber = {};
        ccvariable.jabberOnlineNumber = 0;
        ccvariable.chatBoxesOrder = {};
        ccvariable.chatBoxOrder = [];
        ccvariable.trying = {};
        ccvariable.todaysDate = new Date();
        ccvariable.currentTime = ccvariable.idleTime = Math.floor(ccvariable.todaysDate.getTime()/1000);
        ccvariable.todays12am = ccvariable.currentTime -(ccvariable.currentTime%(24*60*60*1000));
        ccvariable.specialChars = /([^\x00-\x80]+)|([&][#])+/;
        ccvariable.idleFlag = 0;
        ccvariable.hasSearchbox = 0;
        ccvariable.currentStatus;
        ccvariable.buddyListHash;
        ccvariable.botListHash;
        ccvariable.callbackfn = '<?php echo $callbackfn;?>';
        ccvariable.baseData = (settings['ccauth']['enabled']==0)?$.cookie(settings.cookiePrefix+'data'):0;
        ccvariable.mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|IEMobile|blackberry|palm|symbian/i);
        ccvariable.initialized = 0;
        ccvariable.crossDomain = '<?php echo CROSS_DOMAIN;?>';
        ccvariable.dataMethod = 'post';
        ccvariable.dataTimeout = '10000';
        ccvariable.isMini = 0;
        ccvariable.desktopNotification = {};
        ccvariable.runHeartbeat = 1;
        ccvariable.userid = 0;
        ccvariable.ccmobileauth =0 ;
        ccvariable.cometid = '';
        ccvariable.prependLimit = (typeof(settings['prependLimit'])!=="undefined")?settings['prependLimit']:0;
        ccvariable.showAvatar = 1;
        ccvariable.userActive = 0;
        ccvariable.lastmessagereadstatus = {};
        ccvariable.windowFocus = true;
        ccvariable.timedifference = 0;
        ccvariable.displayname = '';
        ccvariable.statusmessage = '';
        ccvariable.openedChatbox = '';
        ccvariable.botlistName = {};
        ccvariable.botlistAvatar = {};
        ccvariable.botlistDescription = {};
        ccvariable.botlistApikey = {};
        var calleeAPI = settings.theme;
        ccvariable.baseData=encodeURIComponent(ccvariable.baseData);
        if(typeof (ccvariable.callbackfn)!='undefined'&&ccvariable.callbackfn!=''&&ccvariable.callbackfn!='desktop'){
            calleeAPI = ccvariable.callbackfn;
        }else if( ccvariable.mobileDevice&&settings.disableForMobileDevices&&calleeAPI!='embedded' || ccvariable.mobileDevice&&settings.disableForMobileDevices&&calleeAPI!='embedded'){
            calleeAPI = ccvariable.callbackfn = 'ccmobiletab';
        }
        ccvariable.externalVars["callbackfn"] = ccvariable.callbackfn;
        ccvariable.externalVars["lastseensetting"] = 0;
        ccvariable.externalVars["messagereceiptsetting"] = 0;
        ccvariable.externalVars["activeChatboxIds"] = '';
        $.ajaxSetup({scriptCharset: "utf-8", cache: "false"});

        $(window).focus(function(){
            if(!ccvariable.windowFocus){
                jqcc.each( ccvariable.openChatboxId, function( key, value ) {
                    if(typeof ccvariable.lastmessagereadstatus[value] != 'undefined' && ccvariable.lastmessagereadstatus[value] == 0){
                        var messageid = ccvariable.lastmessagereadstatus[value];
                        var message = {"id": messageid, "from": value, "self": 0};
                        if(ccvariable.currentStatus != 'invisible' && ccvariable.externalVars["messagereceiptsetting"] == 0){
                            jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                        }
                        ccvariable.lastmessagereadstatus[value] = 1;
                    }
                });
            }
            ccvariable.windowFocus = true;
            ccvariable.isMini = 0;
            if(settings.desktopNotifications==1){
                for(x in  ccvariable.desktopNotification){
                    for(y in  ccvariable.desktopNotification[x]){
                        ccvariable.desktopNotification[x][y].close();
                    }
                }
            }
            ccvariable.desktopNotification = {};
        });
        $(window).blur(function(){
            ccvariable.windowFocus = false;
            ccvariable.isMini = 1;
        });
        $(window).on('mouseenter', function() {
            if(!ccvariable.windowFocus){
                jqcc.each( ccvariable.openChatboxId, function( key, value ) {
                    if(typeof ccvariable.lastmessagereadstatus[value] != 'undefined' && ccvariable.lastmessagereadstatus[value] == 0){
                        var messageid = ccvariable.lastmessagereadstatus[value];
                        var message = {"id": messageid, "from": value, "self": 0};
                        if(ccvariable.currentStatus != 'invisible' && ccvariable.externalVars["messagereceiptsetting"] == 0){
                            jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                        }
                        ccvariable.lastmessagereadstatus[value] = 1;
                    }
                });
            }
            ccvariable.windowFocus = true;
        });
        $(window).on('mouseleave', function() {
            ccvariable.windowFocus = false;
        });
        function userClickId(id){
            if(typeof (jqcc[calleeAPI].createChatbox)!=='undefined'){
                jqcc[calleeAPI].createChatbox(id, ccvariable.buddylistName[id], ccvariable.buddylistStatus[id], ccvariable.buddylistMessage[id], ccvariable.buddylistAvatar[id], ccvariable.buddylistLink[id], ccvariable.buddylistIsDevice[id]);
            }
        };
        function branded(){
            $("body").append('<div id="cc_power" style="display:none">Powered By <a href="https://www.cometchat.com">CometChat</a></div>');
            language[1] = 'Powered By <a href="https://www.cometchat.com">CometChat</a>';
            setTimeout(function(){
                if (settings.theme == "embedded" || settings.theme == "synergy") {
                    $('#cometchat_leftbar').append('<div class="right_footer"><a target="_blank" title="Powered by CometChat" href="https://www.cometchat.com/?r=LB1">Powered by CometChat</a></div>');
                } else {
                    var userstabpopup = jqcc('#cometchat_userstab_popup');
                    $('#cometchat_userstab_popup').find('div.cometchat_tabcontent').append('<div class="right_footer"><a target="_blank" title="Powered by CometChat" href="https://www.cometchat.com/?r=LB1">Powered by CometChat</a></div>');
                    $('#cometchat_userstab_popup').find('div#cometchat_userscontent').css('height',"270px");
                    if(jqcc.cometchat.getThemeVariable('hasSearchbox')){
                        var chatlistheight = '240px';
                        $('#cometchat_userstab_popup').find('div#cometchat_userscontent').css('height',chatlistheight);
                     } else {
                        var chatlistheight = '270px';
                     }
                    userstabpopup.find('#cometchat_userscontent #cometchat_userslist > div').css({'height': chatlistheight});
                    userstabpopup.find('#cometchat_userscontent #cometchat_groupslist_content > div').css({'height': chatlistheight});
                    userstabpopup.find('#cometchat_userscontent .slimScrollDiv > div').css({'height': chatlistheight}).next('div').css({'height': chatlistheight});
                }
            },100)
        };
        function preinitialize(){
            if((typeof(cc_synergy_enabled)!="undefined" && cc_synergy_enabled == 1) || (typeof(cc_embedded_enabled)!="undefined" && cc_embedded_enabled == 1) ){
                return;
            }
            if(jqcc.cometchat.getUserAgent()[0]=="MSIE" && parseInt(jqcc.cometchat.getUserAgent()[1])<9){
                settings.windowFavicon=0;
            };
            if(ccvariable.callbackfn==''&&settings.hideBarCheck==1&&$.cookie(settings.cookiePrefix+"loggedin")!=1){
                $.ajax({
                    url: baseUrl+"cometchat_check.php",
                    data: {'init': '1', basedata: ccvariable.baseData},
                    dataType: 'jsonp',
                    type: ccvariable.dataMethod,
                    timeout: ccvariable.dataTimeout,
                    success: setPreInitVars
                });
            }else{
                setPreInitVars(1);
            }
            function setPreInitVars(data){          /*child function of preinitialize*/
                if(data!='0'){
                    if(typeof(jqcc[calleeAPI]) == 'undefined'){
                        return;
                    }
                    $.cookie(settings.cookiePrefix+"loggedin", '1', {path: '/'});
                    if(typeof (jqcc[calleeAPI].initialize)!=='undefined'){
                        jqcc[calleeAPI].initialize();
                    }else if(ccvariable.callbackfn!=''&&typeof (jqcc[calleeAPI].init())=='function'){
                        jqcc[calleeAPI].init();
                    }
                    ccvariable.externalVars["buddylist"] = '1';
                    ccvariable.externalVars["initialize"] = '1';
                    ccvariable.externalVars["currenttime"] = ccvariable.currentTime;
                    if (ccvariable.runHeartbeat == 1) {
                      jqcc.cometchat.chatHeartbeat();
                    }
                }
            }
        };
        arguments.callee.stimulateHeartbeat = function(options){
            var defaults = {};
            var params = $.extend(defaults,options);
            clearTimeout(ccvariable.heartbeatTimer);
            if(ccvariable.loggedout!=1&&ccvariable.offline!=1){
                if(params.hasOwnProperty('heartbeatTime')){
                    ccvariable.heartbeatTime = params.heartbeatTime;
                    ccvariable.heartbeatCount = 1;
                }else{
                    ccvariable.heartbeatCount++;
                    if(ccvariable.heartbeatCount>4){
                        ccvariable.heartbeatTime *= 2;
                        ccvariable.heartbeatCount = 1;
                    }
                    if(ccvariable.heartbeatTime>settings.maxHeartbeat){
                        ccvariable.heartbeatTime = settings.maxHeartbeat;
                    }
                }
                ccvariable.heartbeatTimer = setTimeout(function(){
                    jqcc.cometchat.chatHeartbeat();
                }, ccvariable.heartbeatTime);
            }
        };
        arguments.callee.getUserAgent = function(){
            var ua= navigator.userAgent, tem,
            M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
            if(/trident/i.test(M[1])){
                tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
                return 'IE '+(tem[1] || '');
            }
            if(M[1]=== 'Chrome'){
                tem= ua.match(/\bOPR\/(\d+)/);
                if(tem!= null) return 'Opera '+tem[1];
            }
            M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
            if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
            return M;
        };
        arguments.callee.startGuestChat = function(name){
            if((typeof(cc_synergy_enabled)!="undefined" && cc_synergy_enabled == 1) || (typeof(cc_embedded_enabled)!="undefined" && cc_embedded_enabled == 1) ){
                var controlparameters = {"type":"modules", "name":"cometchat", "method":"startGuestChat", "params":{'name':name}};
                controlparameters = JSON.stringify(controlparameters);
                if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                    jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
            }else{
                ccvariable.externalVars["guest_login"] = 1;
                ccvariable.externalVars["username"] = name;
                jqcc.cometchat.reinitialize();
            }
        };
        arguments.callee.chatHeartbeat = function(force){
            var newMessage = 0;
            var internetconnection = true;

            if(force==1){
                if(typeof window.cometcall_function=='function'){
                    cometcall_function(ccvariable.cometid, 0, calleeAPI);
                }
            }
            ccvariable.externalVars["blh"] = ccvariable.buddyListHash;
            ccvariable.externalVars["botlh"] = ccvariable.botListHash;
            ccvariable.externalVars["status"] = "";
            if((ccvariable.callbackfn!=''&&ccvariable.callbackfn!='desktop')||calleeAPI=='ccmobiletab'){
                ccvariable.externalVars["status"] = 'available';
            }
            if(force==1){
                ccvariable.externalVars["f"] = 1;
            }else{
                delete ccvariable.externalVars["f"];
            }
            var atleastOneNewMessage = 0;
            var nowTime = new Date();
            var n = {};
            var idleDifference = Math.floor(nowTime.getTime()/1000)-ccvariable.idleTime;
            if(idleDifference>=settings.idleTimeout&&ccvariable.idleFlag==0){
                if(ccvariable.currentStatus=='available'){
                    ccvariable.idleFlag = 1;
                    ccvariable.externalVars["status"] = 'away';
                }
            }
            if(idleDifference<settings.idleTimeout&&ccvariable.idleFlag==1){
                if(ccvariable.currentStatus=='away'){
                    ccvariable.idleFlag = 0;
                    ccvariable.externalVars["status"] = 'available';
                }
            }
            if(ccvariable.crossDomain==1){
                ccvariable.externalVars["cookie_"+settings.cookiePrefix+"state"] = $.cookie(settings.cookiePrefix+'state');
                ccvariable.externalVars["cookie_"+settings.cookiePrefix+"hidebar"] = $.cookie(settings.cookiePrefix+'hidebar');
                ccvariable.externalVars["cookie_"+settings.cookiePrefix+"an"] = $.cookie(settings.cookiePrefix+'an');
                ccvariable.externalVars["cookie_"+settings.cookiePrefix+"loggedin"] = $.cookie(settings.cookiePrefix+'loggedin');
            }
            ccvariable.externalVars['currenttime'] = Math.floor(new Date().getTime()/1000);
            ccvariable.externalVars["basedata"] = ccvariable.baseData;
            ccvariable.externalVars["readmessages"] = jqcc.cometchat.getFromStorage('readmessages');
            ccvariable.externalVars["receivedunreadmessages"] = jqcc.cometchat.getFromStorage('receivedunreadmessages');
            if((settings.theme == "synergy" || settings.theme == "embedded") && settings.enableType == 1 && embeddedchatroomid > 0 && ccvariable.externalVars["initialize"] == 1){
                ccvariable.externalVars["buddylist"] = 0;
            }

            if (((settings.theme == "synergy" || settings.theme == "embedded") && settings.enableType == 1 && ccvariable.externalVars["initialize"] == 1) || ((settings.theme == "synergy" || settings.theme == "embedded") && settings.enableType != 1 && embeddedchatroomid == 0) || (settings.theme != "synergy" || settings.theme != "embedded")) {
                $.ajax({
                    url: baseUrl+"cometchat_receive.php",
                    data: ccvariable.externalVars,
                    dataType: 'jsonp',
                    type: ccvariable.dataMethod,
                    timeout: ccvariable.dataTimeout,
                    error: function(xhr){
                        if(!xhr.status && typeof jqcc[settings.theme].nointernetconnection != "undefined") {
                            jqcc[settings.theme].nointernetconnection();
                        }
                        if(!(jqcc(document).find("#cometchat").hasClass('CCReceiveError')) && ccvariable.externalVars["initialize"] == '1'){
                            jqcc(document).find("#cometchat").addClass('CCReceiveError');
                        }
                        jqcc.cometchat.stimulateHeartbeat({heartbeatTime:settings.minHeartbeat});
                    },
                    success: function(data){
                        if(jqcc(document).find("#cometchat").hasClass('CCReceiveError')){
                            jqcc(document).find("#cometchat").removeClass('CCReceiveError');
                        }
                        if(data){
                            jqcc.cometchat.setInternalVariable('allowchatboxpopup', '1');
                            $.each(data, function(type, item){
                                if(type=='blh'){
                                    ccvariable.buddyListHash = item;
                                }
                                if(type=='botlh'){
                                    ccvariable.botListHash = item;
                                }
                                if(type=='buc'){
                                    $("#cometchat_blockeduserscount").html(item);
                                }
                                if(type=='an'){
                                    if(typeof (jqcc[calleeAPI].newAnnouncement)!=='undefined'){
                                        jqcc[calleeAPI].newAnnouncement(item);
                                    }
                                }
                                if(type=='buddylist'){
                                    if(typeof (jqcc[calleeAPI].buddyList)=='function'){
                                        jqcc[calleeAPI].buddyList(item);
                                    }
                                }
                                if(type=='botlist'){
                                    if(typeof (jqcc[calleeAPI].botList)=='function'){
                                        jqcc[calleeAPI].botList(item);
                                    }
                                }
                                if(type=='loggedout'){
                                    jqcc.cometchat.updateToStorage('receivedunreadmessages',{});
                                    jqcc.cometchat.updateToStorage('readmessages',{});
                                    if(typeof(ccvariable.cometid)!='' && typeof(cometuncall_function)==="function"){
                                        cometuncall_function(ccvariable.cometid);
                                        jqcc.cometchat.setThemeVariable('cometid','');
                                    }
                                    $.cookie(settings.cookiePrefix+"loggedin", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"state", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"jabber", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"jabber_type", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"hidebar", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"lang", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"theme", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"color", null, {path: '/'});
                                    if(typeof (jqcc[calleeAPI].loggedOut)!=='undefined'){
                                        jqcc[calleeAPI].loggedOut();
                                    }
                                    jqcc.cometchat.setThemeVariable('loggedout', 1);
                                    clearTimeout(ccvariable.heartbeatTimer);
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomLogout) == "function") {
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomLogout();
                                    }
                                }
                                if(type=='userstatus'){
                                    ccvariable.userid = item.id;
                                    ccvariable.buddylistStatus[item.id] = item.s;
                                    ccvariable.buddylistMessage[item.id] = item.m;
                                    ccvariable.buddylistName[item.id] = item.n;
                                    ccvariable.buddylistAvatar[item.id] = item.a;
                                    ccvariable.buddylistLink[item.id] = item.l;
                                    ccvariable.buddylistChannelHash[item.id] = item.ch;
                                    ccvariable.buddylistLastseen[item.id] = item.ls;
                                    ccvariable.ccmobileauth = item.ccmobileauth;
                                    if(typeof (jqcc[calleeAPI].userStatus)!=='undefined'){
                                        jqcc[calleeAPI].userStatus(item);
                                    }
                                    if(settings.messageBeep==1&&ccvariable.callbackfn==""){
                                        if(typeof (jqcc[calleeAPI].messageBeep)!='undefined'){
                                            jqcc[calleeAPI].messageBeep(baseUrl);
                                        }
                                    }
                                    if(ccvariable.callbackfn!=""&&ccvariable.callbackfn=="desktop"){
                                        var ccpluginindex=(settings.plugins).indexOf('screenshare');
                                        (settings.plugins).splice(ccpluginindex,1);
                                    }
                                    if(parseInt(ccvariable.userid) && typeof jqcc.cometchat.subscribeToStorage !== 'undefined'){
                                        jqcc.cometchat.subscribeToStorage('cometchat_user_'+ccvariable.userid);
                                    }
                                }
                                if(type=='cometid'){
                                    ccvariable.cometid = item.id;
                                    cometcall_function(ccvariable.cometid, 0, calleeAPI);
                                }
                                if(type=='init'){
                                    jqcc.cometchat.setInternalVariable('updatingsession', '1');
                                }
                                if(type=='initialize'){
                                    ccvariable.timestamp = item;
                                    ccvariable.externalVars["timestamp"] = item;
                                    if(typeof (jqcc.cometchat.restoreFromCCState)!=='undefined'){
                                        jqcc.cometchat.restoreFromCCState();
                                        if(typeof (jqcc[calleeAPI].resynch)!=='undefined'){
                                            jqcc[calleeAPI].resynch();
                                        }
                                    }
                                    if(typeof (jqcc[calleeAPI].windowResize)!=='undefined'){
                                        jqcc[calleeAPI].windowResize();
                                    }
                                }
                                if(type=='st'){
                                    ccvariable.timedifference = (item*1000) - parseInt(new Date().getTime());
                                }
                                if(type=='messages'){
                                    if(ccvariable.externalVars['initialize'] != 1){
                                        ccvariable.externalVars["timestamp"] = item[Object.keys(item).sort().reverse()[0]].id;
                                    }
                                    if(typeof (jqcc.cometchat.publishToStorage) !== 'undefined'){
                                        jqcc.cometchat.publishToStorage('cometchat_user_'+ccvariable.userid,item);
                                    }
                                    jqcc.cometchat.stimulateHeartbeat({heartbeatTime:settings.minHeartbeat});
                                }

                            /*chatroom responses start*/
                                if (type == 'logout') {
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomLogout) == "function") {
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomLogout();
                                    }
                                }
                                if (type == 'userid') {
                                    jqcc.cometchat.setChatroomVars('myid',item);
                                    jqcc.cometchat.setChatroomVars('initialize',0);
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')]) != 'undefined' && typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].createChatroomTab) == "function"){

                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].createChatroomTab(item);
                                    }
                                }
                                if (type == 'chatrooms') {
                                    if (jqcc.cometchat.getChatroomVars('initializeAutoLogin') == 1 && jqcc.cometchat.getChatroomVars('themename') == 'embedded') {
                                        var autoLoginCr = jqcc.cometchat.getChatroomVars('autoLogin');
                                        $.each(item, function(i,room) {
                                            if (('_'+autoLoginCr) == i) {
                                                if(typeof(btoa) != 'undefined'){
                                                    var encodedroomname = btoa(room.name);
                                                }else{
                                                    var encodedroomname = base64_encode(room.name);
                                                }
                                                jqcc.cometchat.silentroom(autoLoginCr,'',encodedroomname);
                                                if($('#cometchat_chatroomstab').length > 0) {
                                                    $('#cometchat_chatroomstab').click();
                                                }
                                                if($('#cometchat_chatroomstab_popup').length > 0) {
                                                    $('#cometchat_chatroomstab_popup').addClass("cometchat_tabopen");
                                                }
                                            }
                                        });
                                        jqcc.cometchat.setChatroomVars('initializeAutoLogin',0);
                                    }
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadChatroomList) == "function")
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].loadChatroomList(item);
                                }
                                if (type == 'clh') {
                                    jqcc.cometchat.setChatroomVars('clh',item);
                                }
                                if (type == 'prepend') {
                                    jqcc.cometchat.setChatroomVars('prepend',item);
                                }
                                if (type == 'ulh') {
                                    jqcc.cometchat.setChatroomVars('ulh',item);
                                }
                                if(type == 'chatroomList') {
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].updateCRReadMessages) == "function")
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].updateCRReadMessages(item);
                                }
                                if (type == 'crmessages') {
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].displayChatroomMessage) == "function"){
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].displayChatroomMessage(item,0);
                                    }
                                    if(jqcc.cometchat.getChatroomVars('calleeAPI') == 'embedded'){
                                        if(($("#currentroom_convo")[0].scrollHeight) - ($("#currentroom_convo").scrollTop() + $("#currentroom_convo").innerHeight()) < 70) {
                                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomScrollDown) == "function")
                                            jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].chatroomScrollDown(1);
                                        }
                                    }
                                    jqcc.cometchat.stimulateHeartbeat({heartbeatTime:settings.minHeartbeat});
                                    if(ccvariable.externalVars['initialize'] != 1){
                                        ccvariable.externalVars["crtimestamp"] = item[Object.keys(item).sort().reverse()[0]].id;
                                    }
                                }
                                if (type == 'users') {
                                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].updateChatroomUsers) == "function")
                                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].updateChatroomUsers(item,0);
                                }
                                if (type == 'error') {
                                    jqcc.cometchat.leaveChatroom();
                                }
                                if (type == 'subscribeChatrooms') {
                                    jqcc.cometchat.subscribeCometChatrooms(item);
                                }
                                /*chatroom responses end*/
                            });
                            if(ccvariable.externalVars["status"]!=""){
                                if(typeof (jqcc[calleeAPI].removeUnderline2)!=='undefined'){
                                    jqcc[calleeAPI].removeUnderline2();
                                }
                                if(typeof (jqcc[calleeAPI].updateStatus)!=='undefined'){
                                    jqcc[calleeAPI].updateStatus(ccvariable.externalVars["status"]);
                                }
                            }
                            jqcc.cometchat.setExternalVariable('initialize', '0');
                            jqcc.cometchat.setExternalVariable('currenttime', '0');
                            jqcc.cometchat.stimulateHeartbeat();
                        }


                    }
                });
            }
        };
        /** Local Storage Start **/
        arguments.callee.updateToStorage = function(key,value){
            if(Object.keys(value).length === 0){
                jqcc.jStorage.set(key,{});
            }else{
                jqcc.jStorage.set(key,
                    jqcc.extend(true,
                        {},
                        jqcc.jStorage.get(key,{}),
                        value
                    )
                );
            }
        }
        arguments.callee.getFromStorage = function(key){
            return jqcc.jStorage.get(key,{});
        }
        arguments.callee.publishToStorage = function(channel,value){
            if(Object.keys(value).length === 0){
                jqcc.jStorage.publish(channel,{});
            }else{
                jqcc.jStorage.publish(channel, value);
            }
        }
        arguments.callee.subscribeToStorage = function(channel){
            jqcc.jStorage.subscribe(channel, function(channel, payload){
                if(typeof (jqcc[calleeAPI].addMessages)!=='undefined'){
                    jqcc[calleeAPI].addMessages(payload,1);
                }
            });
        }
        /** Local Storage End **/

        /** External Variable Start **/
        arguments.callee.setExternalVariable = function(name, value){
            ccvariable.externalVars[name] = value;
        };
        arguments.callee.getExternalVariable = function(name){
            if(ccvariable.externalVars[name]){
                return ccvariable.externalVars[name];
            }else{
                return '';
            }
        };
        /** External Variable End **/

        /** Internal Variable Start **/
        arguments.callee.setInternalVariable = function(name, value){
            ccvariable.internalVars[name] = value;
        };
        arguments.callee.getInternalVariable = function(name){
            if(ccvariable.internalVars[name]){
                return ccvariable.internalVars[name];
            }else{
                return '';
            }
        };
        /** Internal Variable Start **/

        /** Session Variable Start **/
        arguments.callee.getSessionVariable = function(name){
            if(ccvariable.sessionVars[name]){
                return ccvariable.sessionVars[name];
            }else{
                return '';
            }
        };

        arguments.callee.setSessionVariable = function(name, value){
            /**
                Session variables:
                chats: The variable indicates whether the "Chats" panel is open or close. The possible values are: (0 or '' ) => close and 1 => Open
                openedtab: The variable stores the tab opened under chats tab. The possible values are: (0 or '' ) => Recent tab, 1 => Contacts tab and 2 => Groups tab
                chatboxes: The variable stores the chatboxes, whether the chat box is related to group or private chat, their states(closed/opened/minimized) and unread message count.
            **/
            ccvariable.sessionVars[name] = value;
            var cc_state = '';
            if(ccvariable.sessionVars['chats']){
                cc_state += ccvariable.sessionVars['chats'];
            }
            cc_state += ':';
            if(ccvariable.sessionVars['openedtab']){
                cc_state += ccvariable.sessionVars['openedtab'];
            }
            cc_state += ':';
            if(ccvariable.sessionVars['chatboxstates']){
                cc_state += ccvariable.sessionVars['chatboxstates'];
            }
            $.cookie(settings.cookiePrefix+'state', cc_state, {path: '/'});
        };
        arguments.callee.restoreFromCCState = function(){
            var cc_state = $.cookie(settings.cookiePrefix+'state');
            if(cc_state!=null){
                var cc_states = cc_state.split(/:/);
                if(cc_states[0]){
                    ccvariable.sessionVars['chats'] = cc_states[0];
                    if(typeof(jqcc[calleeAPI]) == "function" && typeof(jqcc[calleeAPI].openMainContainer)=='function'){
                        jqcc[calleeAPI].openMainContainer();
                    }
                }
                if(cc_states[1]!=undefined){
                    ccvariable.sessionVars['openedtab'] = cc_states[1];
                    if(typeof(jqcc[calleeAPI]) == "function" && typeof(jqcc[calleeAPI].openChatTab)=='function'){
                        jqcc[calleeAPI].openChatTab(cc_states[1]);
                    }
                }
                if(cc_states[2]!=undefined){
                    jqcc.cometchat.setSessionVariable('chatboxstates',cc_states[2]);
                    var chatboxstates = {};
                    var statestoapply = [];
                    var chatboxstatesarray = cc_states[2].split(/,/);
                    for(var i=0,len=chatboxstatesarray.length;i<len;i++){
                        var state = chatboxstatesarray[i].split(/\|/);
                        if(!state[0]){
                            continue;
                        }
                        chatboxstates[state[0]]=chatboxstatesarray[i]; // stores states of chatboxes
                        ccvariable.chatBoxOrder.push(state[0]);// preserves order of chatboxes
                        var params = {};
                        if(state[0].charAt(0)=='_'){
                            params.g=1;
                            state[0] = state[0].replace('_','');
                        }
                        params.id=parseInt(state[0]);
                        if(state[1]){
                            params.s = parseInt(state[1]);
                        }
                        if(state[2]){
                            params.c = parseInt(state[2]);
                        }
                        statestoapply.push(params);
                    }
                    ccvariable.internalVars['chatboxstates'] = chatboxstates;
                    if(typeof(jqcc[calleeAPI]) == "function" && typeof(jqcc[calleeAPI].applyChatBoxStates)=='function'){
                        jqcc[calleeAPI].applyChatBoxStates(statestoapply);
                    }
                }
            }
        }
        arguments.callee.updateChatBoxState = function(params){
            /* params: JSON Object with the properties:
                id: userid or group id
                g: optional indicating id is user or group
                s: state of the chatbox
                c: count of unread messages
            */
            if(!params.hasOwnProperty('id') || isNaN(params.id)){
                return;
            }
            if(!ccvariable.internalVars.hasOwnProperty('chatboxstates')){
                ccvariable.internalVars['chatboxstates'] = {};
            }
            var chatboxstates = ccvariable.internalVars['chatboxstates'];
            var key = ''+parseInt(params.id);
            var unreadcount = 0;
            if(params.hasOwnProperty('g') && params.g==1){
                key = '_'+key;
            }
            if(!params.hasOwnProperty('c')){
                params.c = 0;
            }
            unreadcount += params.c;
            if(chatboxstates.hasOwnProperty(key)){
                var states = chatboxstates[key].split('|');
                if(!params.hasOwnProperty('s')){
                    params.s = states[1];
                }
                switch(params.s){
                    case 0:
                    case '':
                    case '0':
                        if(states[2]){
                            unreadcount += parseInt(states[2]);
                        }
                        if(ccvariable.chatBoxOrder.indexOf(key)>-1 && unreadcount == 0){
                            ccvariable.chatBoxOrder.splice(ccvariable.chatBoxOrder.indexOf(key),1);
                        }
                        break;
                    case 1:
                    case '1':
                        if(calleeAPI == 'embedded') {
                            if(ccvariable.chatBoxOrder.indexOf(key)>-1){
                                ccvariable.chatBoxOrder.splice(ccvariable.chatBoxOrder.indexOf(key),1);
                            }
                            ccvariable.chatBoxOrder.push(key);
                        }else {
                            if(states[1] == 0 || states[1] == ''){
                                if(ccvariable.chatBoxOrder.indexOf(key)==-1){
                                    ccvariable.chatBoxOrder.push(key);
                                }
                            }
                        }
                        unreadcount = 0;
                        break;
                    case 2:
                    case '2':
                        if(states[1]){
                            if(states[2]){
                                unreadcount += parseInt(states[2]);
                            }
                        }
                        break;
                    default:
                        unreadcount = 0;
                        break;
                }
            }else{
                if(ccvariable.chatBoxOrder.indexOf(key)==-1){
                    ccvariable.chatBoxOrder.push(key);
                }
            }
            chatboxstates[key] = key;
            chatboxstates[key] += '|';
            if(params.hasOwnProperty('s')&& params.s>0){
                chatboxstates[key] += params.s;
            }
            chatboxstates[key] += '|';
            if(unreadcount){
                chatboxstates[key] += unreadcount;
            }
            ccvariable.internalVars['chatboxstates'] = chatboxstates;
            chatboxstatesarray = [];
            var result = [];
            $.each(ccvariable.chatBoxOrder, function(i, e) {
                if ($.inArray(e, result) == -1) result.push(e);
            });
            ccvariable.chatBoxOrder = result;
            for(var i = 0, len = ccvariable.chatBoxOrder.length; i<len; i++){
                chatboxstatesarray.push(ccvariable.internalVars['chatboxstates'][ccvariable.chatBoxOrder[i]]);
            }
            jqcc.cometchat.setSessionVariable('chatboxstates',chatboxstatesarray.join());
        }
        /** Session Variable End **/

        /** Theme Variable Start **/
        arguments.callee.incrementThemeVariable = function(name){
            ccvariable[name]++;
        };
        arguments.callee.setThemeVariable = function(name, value){
            ccvariable[name] = value;
        };
        arguments.callee.setThemeArray = function(name, id, value){
            if(typeof(ccvariable[name])==="undefined"){
                ccvariable[name]={};
            }
            ccvariable[name][id] = value;
        };
        arguments.callee.unsetThemeArray = function(name, id){
            if(typeof(ccvariable[name])!=="undefined"){
                delete ccvariable[name][id];
            }else{
                return false;
            }
        };
        arguments.callee.getThemeArray = function(name, id){
            if(typeof(ccvariable[name])!=="undefined"){
                return ccvariable[name][id];
            }else{
                return false;
            }
        };
        arguments.callee.getThemeVariable = function(name){
            return ccvariable[name];
        };
        /** Theme Variable End **/

        arguments.callee.userClick = function(listing){
            if(typeof (jqcc[calleeAPI].userClick)!=='undefined'){
                jqcc[calleeAPI].userClick(listing);
            }
        };
        arguments.callee.orderChatboxes = function(){
            return;
            /*var activeids = '';
            var activeChatboxId = '';
            var selfNewMessages = 0;
            for(chatbox in ccvariable.chatBoxesOrder){
                if(ccvariable.chatBoxesOrder.hasOwnProperty(chatbox)){
                    if(ccvariable.chatBoxesOrder[chatbox]!=null){
                        if(!Number(ccvariable.chatBoxesOrder[chatbox])){
                            ccvariable.chatBoxesOrder[chatbox] = 0;
                        }
                        activeids += chatbox.replace('_','')+'|'+ccvariable.chatBoxesOrder[chatbox]+',';
                        activeChatboxId += chatbox.replace('_','')+',';
                        if(ccvariable.chatBoxesOrder[chatbox]>0){
                            selfNewMessages = 1;
                        }
                    }
                }
            }
            ccvariable.newMessages = selfNewMessages;
            activeids = activeids.slice(0, -1);
            activeChatboxId = activeChatboxId.slice(0, -1);
            ccvariable.externalVars["activeChatboxIds"] = activeChatboxId;
            jqcc.cometchat.setSessionVariable('activeChatboxes', activeids);*/
        };
        arguments.callee.c5 = function(){
            branded();
            preinitialize();
            return;
        };
        arguments.callee.c6 = function(){
            preinitialize();
            return;
        };
        arguments.callee.getBaseData = function(){
            return ccvariable.baseData;
        };
        arguments.callee.getActiveId = function(){
            return ccvariable.openChatboxId;
        };
        arguments.callee.getUserID = function(){
           return ccvariable.userid;
        };
        arguments.callee.getUser = function(id, callbackfn){
          if (typeof (jqcc.cometchat.getUserFromUID) == 'function') {
              jqcc.cometchat.getUserFromUID(id, callbackfn);
          } else {
              $.ajax({
                    url: baseUrl+"cometchat_getid.php",
                    data: {userid: id, basedata: ccvariable.baseData},
                    cache: false,
                    dataType: 'jsonp',
                    type: ccvariable.dataMethod,
                    timeout: ccvariable.dataTimeout,
                    success: function(data){
                        if(data){
                            window[callbackfn](data);
                        }else{
                            window[callbackfn](0);
                        }
                    }
                });
            }
        };
        arguments.callee.getUserAuth = function(platform) {
           var userAuthJson = {};
           userAuthJson['basedata'] = jqcc.cometchat.getBaseData();
           userAuthJson['baseurl'] = jqcc.cometchat.getBaseUrl();
           userAuth = JSON.stringify(userAuthJson);

           if(platform == "1") {
               androidCometchat.sendToMobile(userAuth);
           } else if (platform == "2") {
               return userAuth;
           }
        };
        arguments.callee.ping = function(){
            return 1;
        };
        arguments.callee.getLanguage = function(id){
            if(typeof(id) != 'undefined' && id != null && id != ''){
                if(typeof(language[id]) != 'undefined' ){
                    return language[id];
                }else{
                    return '';
                }
            }
            return language;
        };
        arguments.callee.chatWith = function(id, cccloud){
           if(typeof(jqcc.cometchat.chatWithUID) == 'function' && typeof(cccloud) != 'undefined'){
               jqcc.cometchat.chatWithUID(id);
           }else {
                if(typeof (jqcc[calleeAPI].chatWith)!=='undefined' && jqcc('#cometchat_synergy_iframe').length==0){
                    jqcc[calleeAPI].chatWith(id);
                } else {
                    var controlparameters = {"type":"modules", "name":"cometchat", "method":"chatWith", "params":{"uid":id, "synergy":"1"}};
                    controlparameters = JSON.stringify(controlparameters);
                    if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                        jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                    }
                }
            }
        };
        arguments.callee.getRecentData = function(id){
            $.ajax({
                cache: false,
                url: baseUrl+"cometchat_receive.php",
                data: {chatbox: id, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                success: function(data){
                    if(typeof (jqcc[calleeAPI].loadData)!=='undefined'){
                        jqcc[calleeAPI].loadData(id, data);
                    }
                }
            });
        };
        arguments.callee.getUserDetails = function(id){
            $.ajax({
                url: baseUrl+"cometchat_getid.php",
                data: {userid: id, basedata: ccvariable.baseData},
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                cache: false,
                async: false,
                dataType: 'jsonp',
                success: function(data){
                    ccvariable.buddylistStatus[id] = data.s;
                    ccvariable.buddylistMessage[id] = data.m;
                    ccvariable.buddylistName[id] = data.n;
                    ccvariable.buddylistAvatar[id] = data.a;
                    ccvariable.buddylistLink[id] = data.l;
                    ccvariable.buddylistChannelHash[id] = data.ch;
                    ccvariable.buddylistLastseen[id] = data.ls;
                    if(ccvariable.callbackfn=='mobilewebapp'){
                        jqcc[ccvariable.callbackfn].loadUserData(id, data);
                    }
                }
            });
        };
        arguments.callee.launchModule = function(id){
            if(typeof (jqcc[calleeAPI].launchModule)!=='undefined' && jqcc("#cometchat").length > 0){
                jqcc[calleeAPI].launchModule(id);
            } else {
                var controlparameters = {"type":"modules", "name":"cometchat", "method":"launchModule", "params":{"uid":id, "synergy":"1","embedded":"1"}};
                controlparameters = JSON.stringify(controlparameters);
                if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                    jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
            }
        };
        arguments.callee.toggleModule = function(id){
            if(typeof (jqcc[calleeAPI].toggleModule)!=='undefined'){
                jqcc[calleeAPI].toggleModule(id);
            }
        };
        arguments.callee.closeModule = function(id){
            if(typeof (jqcc[calleeAPI].closeModule)!=='undefined'){
                jqcc[calleeAPI].closeModule(id);
            }
        };
        arguments.callee.closeAllModule = function(){
            if(typeof (jqcc[calleeAPI].closeAllModule)!=='undefined'){
                jqcc[calleeAPI].closeAllModule();
            }
        };
        arguments.callee.closeChatbox = function(id){
            if(typeof (jqcc[calleeAPI].closeChatbox)!=='undefined'){
                jqcc[calleeAPI].closeChatbox(id);
            }
        };
        arguments.callee.joinChatroom = function(roomid, inviteid, roomname){
            if(typeof (jqcc[calleeAPI].joinChatroom)!=='undefined'){
                jqcc[calleeAPI].joinChatroom(roomid, inviteid, roomname);
            }
        };
        arguments.callee.createChatboxSet = function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
            $.ajax({
                url: baseUrl+"cometchat_getid.php",
                data: {userid: id, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                cache: false,
                success: function(data){
                    if(data){
                        ccvariable.buddylistName[id] = data.n;
                        ccvariable.buddylistStatus[id] = data.s;
                        ccvariable.buddylistMessage[id] = data.m;
                        ccvariable.buddylistAvatar[id] = data.a;
                        ccvariable.buddylistLink[id] = data.l;
                        ccvariable.buddylistIsDevice[id] = data.d;
                        ccvariable.buddylistChannelHash[id] = data.ch;
                        ccvariable.buddylistLastseen[id] = data.ls;
                        jqcc[settings.theme].createChatbox(id, data.n, data.s, data.m, data.a, data.l, data.d, silent, tryOldMessages);
                    }
                },
                error: function(data){
                    jqcc.cometchat.setThemeVariable('trying', id, 5);
                }
            });

        };
        arguments.callee.updateChatboxSet = function(id,prepend){
            var postVars={chatbox: id, basedata: ccvariable.baseData};
            if(typeof(prepend)!=="undefined"){
                postVars["prepend"]=prepend;
            }
            $.ajax({
                cache: false,
                url: baseUrl+"cometchat_receive.php",
                data: postVars,
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                dataType: 'jsonp',
                success: function(data){
                    if(data){
                        if(typeof(prepend)!=="undefined"){
                            jqcc[settings.theme].prependMessages(id, data);
                        }else{
                            jqcc[settings.theme].updateChatboxSuccess(id, data);
                        }
                    }
                }
            });
        };
        arguments.callee.chatboxKeydownSet = function(id, message, callbackfn){
            if(typeof(callbackfn) === "undefined" || callbackfn !="") {
                callbackfn = ccvariable.callbackfn;
            }
            ccvariable.sendVars["callbackfn"] = callbackfn;
            if(message.length>1000){
                if(message.indexOf(" ") == -1 || message.indexOf(" ") >= 1000) {
                    message = message.substr(0,999)+" "+message.substr(999,message.length);
                }
                if(message.charAt(999)==' '){
                    messagecurrent = message.substring(0, 1000);
                }else{
                    messagecurrent = message.substring(0, 1000);
                    var spacePos = messagecurrent.length;
                    while(messagecurrent.charAt(spacePos)!=' '){
                        spacePos--;
                    }
                    messagecurrent = message.substring(0, spacePos);
                }
                messagenext = message.substring(messagecurrent.length);
                if(messagenext.length>0){
                    messagecurrent = messagecurrent+"...";
                }
            }else{
                messagecurrent = message;
                messagenext = '';
            }
            message = messagecurrent;

            sendAjax = function (broadcastflag) {
                sendajax = false;
                $.ajax({
                    url: baseUrl+"cometchat_send.php",
                    data: ccvariable.sendVars,
                    dataType: 'jsonp',
                    type: ccvariable.dataMethod,
                    timeout: ccvariable.dataTimeout,
                    success: function(data){
                        ccvariable.sendVars = {};
                        if(data != null && typeof(data) != 'undefined'){

                            if(typeof (jqcc[calleeAPI].addMessages)!=='undefined'){
                                if(broadcastflag){
                                    jqcc[calleeAPI].addMessages(data);
                                }else{
                                    jqcc[calleeAPI].addMessages([{"from": id, "message": data.m, "id": data.id, "broadcast": 0, "direction": data.direction, "calledfromsend": 1}]);
                                    var alreadyreceivedunreadmessages = jqcc.cometchat.getFromStorage('receivedunreadmessages');
                                    var arr = Object.keys(alreadyreceivedunreadmessages).map(function(k) { return alreadyreceivedunreadmessages[k] });
                                    var maxmsgid = Math.max.apply(null,arr);
                                    if( data.id < maxmsgid){
                                        jqcc.cometchat.updateToStorage('receivedunreadmessages',{});
                                    }
                                }
                            }
                        }
                        jqcc.cometchat.stimulateHeartbeat({heartbeatTime:settings.minHeartbeat});
                        ccvariable.sendVars = {};
                        sendajax = true;
                   },
                   error: function(data){
                        sendajax = true;
                        if(broadcastData.length==0){
                            sendbroadcastinterval = 0;
                            clearInterval(sendbroadcastinterval);
                        }
                   }
               });
            }
            $( document ).ajaxStop(function() {
                sendajax = true;
                if(broadcastData.length==0){
                    sendbroadcastinterval = 0;
                    clearInterval(sendbroadcastinterval);
                }
            });
            if(sendajax == true){
                ccvariable.sendVars["basedata"] = ccvariable.baseData;
                if(broadcastData.length == 0){
                    ccvariable.sendVars["to"] = id;
                    ccvariable.sendVars["message"] = message;
                    var broadcastflag = 0;
                }else{
                    broadcastData.push(id,message);
                    ccvariable.sendVars["broadcast"] = broadcastData;
                    var broadcastflag = 1;
                }
                sendAjax(broadcastflag);
            }else{
                broadcastData.push(id,message);
                if(sendbroadcastinterval == 0){
                    sendbroadcastinterval = setInterval(function(){
                        sendbroadcastinterval = 0;
                        clearInterval(sendbroadcastinterval);
                        if(broadcastData.length == 0){
                            clearInterval(sendbroadcastinterval);
                        }
                        if(sendajax == true && broadcastData.length > 0){
                            sendbroadcastinterval = 0;
                            clearInterval(sendbroadcastinterval);
                            ccvariable.sendVars["basedata"] = ccvariable.baseData;
                            ccvariable.sendVars["broadcast"] = broadcastData;
                            sendAjax(1);
                            broadcastData = [];
                        }
                    }, 50);
                }
            }
            if(messagenext.length>0){
                jqcc.cometchat.chatboxKeydownSet(id, '...'+messagenext);
            }
        };

        arguments.callee.sendMessage = function(id, message){
            if(jqcc("#cometchat").length > 0) {
                jqcc.cometchat.chatboxKeydownSet(id,message);
            } else {
                var controlparameters = {"type":"modules", "name":"cometchat", "method":"sendMessage", "params":{"uid":id, "message":message, "synergy":"1","embedded":"1"}};
                controlparameters = JSON.stringify(controlparameters);
                if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                    jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
            }
        };

        arguments.callee.addMessage = function(boxid,message,msgid,nopopup){
            if(typeof(nopopup) === "undefined" || nopopup =="") {
                nopopup = 0;
            }
            if(typeof (jqcc[calleeAPI].addMessages)!=='undefined'){
                jqcc[calleeAPI].addMessages([{"from": boxid, "message": message, "self": 1, "old": 1, "id": msgid, "sent": Math.floor(new Date().getTime()), "nopopup": nopopup}]);
            }
            if(typeof (jqcc[calleeAPI].scrollDown)!=='undefined'){
                jqcc[calleeAPI].scrollDown(boxid);
            }
        };

        arguments.callee.statusSendMessageSet = function(message, statustextarea){
            $.ajax({
                url: baseUrl+"cometchat_send.php",
                data: {statusmessage: message, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                success: function(data){
                    ccvariable.statusmessage = message;
                    if(typeof jqcc[settings.theme].statusSendMessageSuccess != "undefined") {
                        jqcc[settings.theme].statusSendMessageSuccess(statustextarea);
                    }
                },
                error: function(data){
                    if (typeof jqcc[settings.theme].statusSendMessageError != "undefined") {
                       jqcc[settings.theme].statusSendMessageError();
                   }
                }
            });
        };
        arguments.callee.updateSettings = function(guestname, statusmessage, status, lastseensetting, readreceiptsetting){
            $.ajax({
                url: baseUrl+"cometchat_send.php",
                data: {statusmessage: statusmessage, basedata: ccvariable.baseData, status: status, lastseenSettingsFlag: lastseensetting, readreceiptSettingsFlag: readreceiptsetting, guestname: guestname},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                success: function(data){
                    //jqcc[settings.theme].updateSettingsSuccess();
                },
                error: function(data){
                    //jqcc[settings.theme].updateSettingsError();
                }
            });
        };
        arguments.callee.setGuestNameSet = function(guestname){
            $.ajax({
                url: baseUrl+"cometchat_send.php",
                data: {guestname: guestname, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                success: function(data){
                    ccvariable.displayname = guestname;
                    if(typeof jqcc[settings.theme].setGuestNameSuccess != "undefined") {
                        jqcc[settings.theme].setGuestNameSuccess();
                    }
                },
                error: function(data){
                    if(typeof jqcc[settings.theme].setGuestNameError != "undefined") {
                        jqcc[settings.theme].setGuestNameError();
                    }
                }
            });
        };
        arguments.callee.hideBar = function(){
            if(typeof (jqcc[calleeAPI].hideBar)!=='undefined'){
                jqcc[calleeAPI].hideBar();
            }
        };
        arguments.callee.getBaseUrl = function(){
            return baseUrl;
        };
        arguments.callee.setAlert = function(id, number){
            if(typeof (jqcc[calleeAPI].setModuleAlert)!=='undefined'){
                jqcc[calleeAPI].setModuleAlert(id, number);
            }
        };
        arguments.callee.closeTooltip = function(){
            if(typeof (jqcc[calleeAPI].closeTooltip)!=='undefined'){
                jqcc[calleeAPI].closeTooltip();
            }
        };
        arguments.callee.scrollToTop = function(){
            if(typeof (jqcc[calleeAPI].scrollToTop)!=='undefined'){
                jqcc[calleeAPI].scrollToTop();
            }
        };
        arguments.callee.reinitialize = function(){
            ccvariable.baseData = $.cookie(settings.cookiePrefix+'data');
            if(typeof (jqcc[calleeAPI].reinitialize)!=='undefined'){
                jqcc[calleeAPI].reinitialize();
            }
        };
        arguments.callee.updateHtml = function(id, temp){
            if(typeof (jqcc[calleeAPI].updateHtml)!=='undefined'){
                jqcc[calleeAPI].updateHtml(id, temp);
            }
        };
        arguments.callee.processMessage = function(id, value){
            if(typeof (jqcc[calleeAPI].processMessage)!=='undefined'){
                return jqcc[calleeAPI].processMessage(id, value);
            }
        };
        arguments.callee.replaceHtml = function(id, value){
            replaceHtml(id, value);
        };
        arguments.callee.getSettings = function(e){
            return settings;
        };
        arguments.callee.getMobileappdetails = function(e){
            return mobileappdetails;
        };

        arguments.callee.getTrayicon = function(e){
            return trayicon;
        };
        arguments.callee.getCcvariable = function(e){
            return ccvariable;
        };
        arguments.callee.echo = function(e){
            return "ECHO";
        };
        arguments.callee.getWebrtcPlugins = function(e){
            return webrtcplugins;
        };
        arguments.callee.userAdd = function(id, s, m, n, a, l, ch, ls){
            ccvariable.isJabber[id] = 1;
            ccvariable.buddylistStatus[id] = s;
            ccvariable.buddylistMessage[id] = m;
            ccvariable.buddylistName[id] = n;
            ccvariable.buddylistAvatar[id] = a;
            ccvariable.buddylistLink[id] = l;
            ccvariable.buddylistChannelHash[id] = ch || '';
            ccvariable.buddylistLastseen[id] = ls || '';
        };
        arguments.callee.updateJabberOnlineNumber = function(number){
            if(typeof (jqcc[calleeAPI].updateJabberOnlineNumber)!=='undefined'){
                jqcc[calleeAPI].updateJabberOnlineNumber(number);
            }
        };
        arguments.callee.getName = function(id){
            if(typeof (ccvariable.buddylistName[id])!=='undefined'){
                return ccvariable.buddylistName[id];
            }
        };
        arguments.callee.lightbox = function(name,caller,windowMode){
            var allowpopout = 0;
            var callbackfn='';
            var cc_theme = jqcc.cometchat.getChatroomVars('calleeAPI');
            if(ccvariable.callbackfn=='desktop'){
                callbackfn='desktop';
            }
            if(ccvariable.mobileDevice){
                callbackfn='mobilewebapp';
            }
            if(typeof(windowMode)=="undefined"){
                windowMode = 0;
            }
            var callerUrl = "";
            if(typeof(caller) != "undefined"){
                callerUrl = "caller="+caller;
            }
            if(trayicon[name]){
                if(cc_theme == 'docked' && ccvariable.mobileDevice){
                    windowMode = 1;
                }

                if(name=='chatrooms'||name=='games'||name=='broadcastmessage'){
                    allowpopout = 1;
                    if(settings.theme == 'lite' && name=='chatrooms'){
                        jqcc[calleeAPI].minimizeOpenChatbox();
                    }
                }
                loadCCPopup(trayicon[name][2]+'?'+callerUrl+'&callbackfn='+callbackfn, trayicon[name][0], "status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width="+(Number(trayicon[name][4])+2)+",height="+trayicon[name][5]+"", Number(trayicon[name][4])+2, trayicon[name][5], trayicon[name][1], 0, 0, 0, allowpopout,windowMode);
            }
            /*if(typeof(caller) == 'undefined') {
                ccvariable.openChatboxId = '';
                jqcc.cometchat.setSessionVariable('openChatboxId', ccvariable.openChatboxId);
            }*/
        };
        arguments.callee.sendStatus = function(message){
            $.ajax({
                url: baseUrl+"cometchat_send.php",
                data: {status: message, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                success: function(data){
                    ccvariable.currentStatus = message;
                    if(typeof(jqcc[calleeAPI].updateStatus)=='function'){
                        jqcc[settings.theme].removeUnderline();
                        jqcc[calleeAPI].updateStatus(message);
                    }
                }
            });
        };
        arguments.callee.tryClickSync = function(id){
            if(ccvariable.buddylistName[id]==null||ccvariable.buddylistName[id]==''){
                if(ccvariable.trying[id]<5){
                    setTimeout(function(){
                        jqcc.cometchat.tryClickSync(id);
                    }, 500);
                }
            }else{
                jqcc.cometchat.chatWith(id);
            }
        };
        arguments.callee.userDoubleClick = function(listing){
            return;
            var id = listing;
            if(typeof (jqcc[calleeAPI].createChatbox)!=='undefined'){
                jqcc[calleeAPI].createChatbox(id, ccvariable.buddylistName[id], ccvariable.buddylistStatus[id], ccvariable.buddylistMessage[id], ccvariable.buddylistAvatar[id], ccvariable.buddylistLink[id], ccvariable.buddylistIsDevice[id], 1);
            }
        };
        arguments.callee.tryClick = function(id){
            if(ccvariable.buddylistName[id]==null||ccvariable.buddylistName[id]==''){
                if(ccvariable.trying[id]<5){
                    setTimeout(function(){
                        jqcc.cometchat.tryClick(id);
                    }, 500);
                }
            }else{
                if(ccvariable.openChatboxId!=id){
                    jqcc.cometchat.chatWith(id);
                }
            }
        };
        arguments.callee.notify = function(title, image, message, clickEvent, id, msgid){

            if(typeof jqcc.cometchat.getFromStorage('loggedin_'+jqcc.cometchat.getUserID(),{'lastnotifiedmessageid':0}).lastnotifiedmessageid == "undefined"){
                jqcc.cometchat.updateToStorage('loggedin_'+jqcc.cometchat.getUserID(),{'lastnotifiedmessageid':0});
            }

            if(jqcc.cometchat.getFromStorage('loggedin_'+jqcc.cometchat.getUserID(),{'lastnotifiedmessageid':0}).lastnotifiedmessageid != msgid && jqcc.cometchat.getFromStorage('loggedin_'+jqcc.cometchat.getUserID(),{'lastnotifiedmessageid':0}).lastnotifiedmessageid < msgid){
                jqcc.cometchat.updateToStorage('loggedin_'+jqcc.cometchat.getUserID(),{'lastnotifiedmessageid':msgid});
                if(navigator.userAgent.match(/chrome|firefox/i)&&settings.desktopNotifications==1){
                    if(ccvariable.callbackfn=="desktop"&&typeof title!='undefined'&&typeof image!='undefined'&&typeof message!='undefined'){
                        jqcc.ccdesktop.desktopNotify(image, message, ccvariable.buddylistName[id], msgid);
                    }else if(ccvariable.idleFlag){
                        if (Notification.permission !== 'denied') {
                            Notification.requestPermission(function (permission) {
                                if(!('permission' in Notification)) {
                                    Notification.permission = permission;
                                }
                            });
                        }
                        if(Notification.permission === "granted"&&typeof title!='undefined'&&typeof image!='undefined'&&typeof message!='undefined'){
                            tempMsg = jqcc('<div>'+message+'</div>');
                            jqcc.each(tempMsg.find('img.cometchat_smiley'),function(){
                                jqcc(this).replaceWith('*'+jqcc(this).attr('title')+'*');
                            });
                            message = tempMsg.text();
                            if(typeof id!='undefined'){
                                if(typeof ccvariable.desktopNotification[id]=="undefined"){
                                    ccvariable.desktopNotification[id] = {};
                                }
                                ccvariable.desktopNotification[id][msgid] = new Notification(title, {icon: image, body: message});
                                ccvariable.desktopNotification[id][msgid].onclick = function(){
                                    if(typeof clickEvent=='function'){
                                        clickEvent();
                                    }
                                };
                            }else{
                                ccvariable.desktopNotification[id][msgid] = new Notification(title, {icon: image, body: message});
                                ccvariable.desktopNotification[id][msgid].onclick = function(){
                                    if(typeof clickEvent=='function'){
                                        clickEvent();
                                    }
                                };
                            }
                        }
                    }
                }
            }
        };
        arguments.callee.statusKeydown = function(event, statustextarea){
            if(event.keyCode==13&&event.shiftKey==0){
                if(typeof (jqcc[calleeAPI].statusSendMessage)!=='undefined'){
                    jqcc[calleeAPI].statusSendMessage();
                }
                return false;
            }
        };
        arguments.callee.guestnameKeydown = function(event, statustextarea){
            if(event.keyCode==13&&event.shiftKey==0){
                if(typeof (jqcc[calleeAPI].setGuestName)!=='undefined'){
                    jqcc[calleeAPI].setGuestName(statustextarea);
                }
                return false;
            }
        };
        arguments.callee.minimizeAll = function(){
            if(typeof (jqcc[calleeAPI].setGuestName)!=='undefined' && jqcc("#cometchat").length > 0){
                jqcc[settings.theme].minimizeAll();
            } else {
                var controlparameters = {"type":"modules", "name":"cometchat", "method":"minimizeAll", "params":{"uid":"", "synergy":"1" ,"embedded":"1"}};
                controlparameters = JSON.stringify(controlparameters);
                if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                    jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
            }
        };
        arguments.callee.processcontrolmessage = function(incoming){
            if(typeof incoming.message != "undefined" && (incoming.message).indexOf('CC^CONTROL_')!=-1){
                var message = (incoming.message).replace('CC^CONTROL_','');
                var data = incoming.message.split('_');
                var chatroommode = 0;
                if(typeof(data[5])!='undefined' && data[5]==1){
                    chatroommode = 1;
                }
                if(cp = IsJsonString(message)){
                    var type = cp["type"] || ""
                    ,   name = cp["name"] || ""
                    ,   method = cp["method"] || ""
                    ,   params = cp["params"] || {}
                    ;
                    switch(type){
                        case 'core':
                            switch(name){
                                case 'bots':
                                    var botid = parseInt(params.botid);
                                    incoming.botid = botid;
                                    return params.message;
                                    break;
                                default:
                                    if(typeof jqcc[calleeAPI][method] == "function" && ccvariable.callbackfn != "mobilewebapp")
                                        jqcc[calleeAPI][method](params);
                                    break;
                            }
                            break;
                        case 'plugins':
                            message = JSON.parse(message);
                            return jqcc['cc'+name.toLowerCase()].processControlMessage(params);
                            break;
                        default:
                            break;
                    }
                } else if(data[1]=='PLUGIN'){
                    switch(data[2]){
                        case 'AVCHAT':
                        switch(data[3]){
                            case 'ENDCALL':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"endcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'REJECTCALL':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"rejectcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'NOANSWER':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"noanswer", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'CANCELCALL':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"canceloutgoingcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'BUSYCALL':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"busycall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            default :
                            message = '';
                            break;
                        }
                        break;
                        case 'AUDIOCHAT':
                        switch(data[3]){
                            case 'ENDCALL':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"endcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'REJECTCALL':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"rejectcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'NOANSWER':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"noanswer", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'CANCELCALL':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"canceloutgoingcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'BUSYCALL':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"busycall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            default :
                            message = '';
                            break;
                        }
                        break;
                        case 'BROADCAST':
                        switch(data[3]){
                            case 'ENDCALL':
                            var controlparameters = {"type":"plugins", "name":"broadcast", "method":"endcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            default :
                            message = '';
                            break;
                        }
                        break;
                        default :
                        break;
                    }
                    if(typeof(data[2]) == 'undefined'){return;}
                    return jqcc['cc'+data[2].toLowerCase()].processControlMessage(controlparameters);
                } else {
                    <?php
                    foreach ($trayicon as $ti) {
                        if (in_array('chatrooms', $ti)) {
                    ?>
                        switch(data[1]){
                            case 'kicked':
                                if (jqcc.cometchat.getChatroomVars('myid') == data[2]) {
                                    alert ('<?php echo $chatrooms_language[36];?>');
                                    jqcc.cometchat.leaveChatroom(incoming.chatroomid);
                                }
                                return '';
                                break;
                            case 'banned':
                                if (jqcc.cometchat.getChatroomVars('myid') == data[2]) {
                                    alert ('<?php echo $chatrooms_language[37];?>');
                                    jqcc.cometchat.leaveChatroom(incoming.chatroomid, 1);
                                }
                                return '';
                                break;
                            case 'deletemessage':
                                if(jqcc.cometchat.getChatroomVars('calleeAPI')=='docked') {
                                   $("#cometchat_groupmessage_"+data[2]).remove();
                                }else{
                                   $("#cometchat_groupmessage_"+data[2]).parent().remove();
                                }
                                return '';
                                break;
                            case 'deletedchatroom':
                                jqcc.cometchat.leaveChatroom(incoming.chatroomid);
                                alert ('<?php echo $chatrooms_language[55];?>');
                                return '';
                                break;
                            default :
                                break;
                        }
                    <?php }
                    } ?>
                }
            }else if(typeof incoming.message != "undefined" && ((incoming.message).indexOf('has successfully sent a file')!=-1 || (incoming.message).indexOf('has sent you a file')!=-1)){
                if(ccvariable.callbackfn=="desktop"){
                    if(incoming.message.indexOf('target')>=-1){
                        incoming.message=incoming.message.replace(/target="_blank"/g,'');
                    }
                }
                 return incoming.message;
            }else if(typeof incoming.message != "undefined" && ((incoming.message).indexOf('has successfully sent a handwritten message')!=-1 || (incoming.message).indexOf('has sent you a handwritten message')!=-1)){
                /*if(jqcc.cometchat.getCcvariable().callbackfn=="desktop"){
                    if(incoming.message.indexOf('href')>=-1){
                        var start = (incoming.message).indexOf('href');
                        var end = (incoming.message).indexOf('target');
                        var HtmlString=(incoming.message).slice(start,end);
                        incoming.message=(incoming.message).replace(HtmlString,'');
                    }
                }*/
                 return incoming.message;
            } else if(typeof incoming.message != "undefined") {
                if(ccvariable.callbackfn=="desktop"){
                    if((incoming.message).indexOf('has shared a file')!=-1){
                        if(incoming.message.indexOf('target')>=-1){
                            incoming.message=incoming.message.replace(/target="_blank"/g,'');
                        }
                        return incoming.message;
                    }else if((incoming.message).indexOf('has shared a handwritten message')!=-1){
                        /*if(incoming.message.indexOf('href')>=-1){
                            var start = (incoming.message).indexOf('href');
                            var end = (incoming.message).indexOf('target');
                            var HtmlString=(incoming.message).slice(start,end);
                            incoming.message=(incoming.message).replace(HtmlString,'');
                        }*/
                        return incoming.message;
                    }else{
                        return incoming.message;
                    }
                }else{
                    return incoming.message;
                }
            }
        }
        arguments.callee.closeCRPopout = function(params){

        }
        arguments.callee.typingTo = function(params){
            if(settings.cometserviceEnabled == 1 && settings.istypingEnabled == 1){
                var fid = jqcc.cometchat.getUserID();
                var senttime = parseInt(new Date().getTime())+jqcc.cometchat.getThemeVariable('timedifference');
                var channel = jqcc.cometchat.getThemeArray('buddylistChannelHash', params.id);
                if(channel.charAt(0)!="/"){
                    channel = "/"+channel;
                }
                var jsondata = {channel:channel,data:{"from":fid,"message":"CC^CONTROL_{\"type\":\"core\",\"name\":\"textchat\",\"method\":\""+params.method+"\",\"params\":{\"fromid\":"+fid+",\"typingtime\":"+senttime+"}}","sent":senttime,"self":0},callback:""}
                COMET.publish(jsondata);
            }
        }
        arguments.callee.sendReceipt = function(incoming, receipt){
            var fromid = incoming.from;
            var messageid = incoming.id;

            if(typeof incoming.old != "undefined" && incoming.old == 1){
                ccvariable.lastmessagereadstatus[fromid] = 1;
                return;
            }
            if(typeof receipt == "undefined" && incoming.self == 0 && ccvariable.currentStatus != 'invisible'){
                receipt = 'deliveredMessageNotify';
                ccvariable.lastmessagereadstatus[fromid] = 0;
                if(ccvariable['openChatboxId'] == fromid && ccvariable.windowFocus == true){
                    receipt = 'deliveredReadMessageNotify';
                    ccvariable.lastmessagereadstatus[fromid] = 1;
                }
            } else if(ccvariable.lastmessagereadstatus[fromid]==1){
                ccvariable.lastmessagereadstatus[fromid] = 1;
                return;
            }
            if(settings.cometserviceEnabled == 1 && settings.messagereceiptEnabled == 1 && typeof receipt != "undefined" && incoming.id != undefined){
                var channel = '';
                if(ccvariable.callbackfn != 'mobilewebapp'){
                    channel = ccvariable.buddylistChannelHash[fromid];
                } else{
                    channel = jqcc.cometchat.getThemeArray("buddyListChannelHash",fromid);
                }
                var controlmessage = 'CC^CONTROL_{"type":"core","name":"textchat","method":"'+receipt+'","params":{"fromid":'+ccvariable.userid+',"message":'+messageid+'}}';
                if(transport == 'cometservice-selfhosted'){
                	var jsondata = {"channel":"/"+channel,"data":{"from":ccvariable.userid,"message":controlmessage,"sent":parseInt(new Date().getTime()),"self":0},"callback":""}
                } else if(transport == 'cometservice'){
                    var jsondata = {"channel":channel,"message":{"from":ccvariable.userid,"message":controlmessage,"sent":parseInt(new Date().getTime()),"self":0},"callback":""}
                }

                COMET.publish(jsondata);

                }
            }

    };
    function replaceHtml(el, html){
        var oldEl = typeof el==="string" ? document.getElementById(el) : el;
        /*@cc_on // Pure innerHTML is slightly faster in IE
         oldEl.innerHTML = html;
         return oldEl;
         @*/
        var newEl = oldEl.cloneNode(false);
        newEl.innerHTML = html;
        oldEl.parentNode.replaceChild(newEl, oldEl);
        /* Since we just removed the old element from the DOM, return a reference
         to the new element, which can be used to restore variable references. */
        return newEl;
    };
})(jqcc);
jqcc(document).bind('keyup', function(e){
    if(e.keyCode==27){
        jqcc('.cometchat_closebox').click();
        $('.cometchat_container').remove();
        jqcc.cometchat.minimizeAll();
    }
});

function cometready(){
    if(typeof CometChathasBeenRun==='undefined'){
        CometChathasBeenRun = true;
    }else{
        return;
    }
    jqcc.cometchat();
    jqcc.cometchat.<?php echo $jsfn; ?>();
};

<?php if(!defined('USE_COMET') || USE_COMET == 0) { ?>
    jqcc(document).ready(function() {
        if(!((window.top!=window.self)&&('<?php echo $theme; ?>'=='docked'))){
            cometready();
        }
    });
<?php } ?>
