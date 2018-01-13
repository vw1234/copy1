<?php

include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
    include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

foreach ($jabber_language as $i => $l) {
$jabber_language[$i] = str_replace("'", "\'", $l);
}

$connectPhrase = $jabber_language[0].' '.$jabber_language[16];
?>
if(typeof(jqcc) === 'undefined'){jqcc = jQuery;};
(function($) {
    var ccjabber = [];
    jqcc.extend(
        jqcc.facebook, {
            jabberInit: function() {
                ccjabber = jqcc.ccjabber.getCcjabberVariable();
                $('<div class="cometchat_tabsubtitle2" id="jabber_login">' + ccjabber.login + '</div>').insertAfter('#cometchat_userstab_popup .cometchat_userstabtitle');
                $('#cometchat_sidebar').css('height',($('#cometchat_sidebar').height()+34)+'px');
                $('#jabber_login').unbind('click');
                $('#jabber_login').bind('click', function() {
                    jqcc.ccjabber.login();
                });
                var list = '<div id="cometchat_userslist_jabber"></div>';
                $(list).insertAfter('#cometchat_userslist');
                if (jqcc.cookie('cc_jabber') && jqcc.cookie('cc_jabber') == 'true') {
                   jqcc.ccjabber.process();
                }
            },
            jabberLogout: function() {
                $.cometchat.updateJabberOnlineNumber(0);
                $('.cometchat_subsubtitle_siteusers').remove();
                $('.cometchat_subsubtitle_jabber').remove();
                var hash = '';
                $('#jabber_login').html(ccjabber.login);
                $('#cometchat_userslist_jabber').html('');
                ccjabber.heartbeatCount = 1;
                clearTimeout(ccjabber.messageTimer);
                ccjabber.heartbeatTime = ccjabber.minHeartbeat;
                jqcc.ccjabber.jabberLogout();
                $('#jabber_login').unbind('click');
                $('#jabber_login').bind('click', function() {
                        jqcc.ccjabber.login();
                });
            },
            jabberProcess: function() {
                if ($('.cometchat_subsubtitle').first().length == 0) {
                        var head = '<div class="cometchat_subsubtitle cometchat_subsubtitle_top cometchat_subsubtitle_siteusers"><hr class="hrleft"><?php echo $jabber_language[10];?><hr class="hrright"></div>';
                        $(head).insertBefore('#cometchat_userslist');
                }

                var head = '<div class="cometchat_subsubtitle cometchat_subsubtitle_jabber"><hr class="hrleft"><?php echo $jabber_language[11];?><hr class="hrright"></div>';

                if (jqcc.cookie('cc_jabber_type') == 'gtalk') {
                        head = '<div class="cometchat_subsubtitle cometchat_subsubtitle_jabber"><hr class="hrleft"><?php echo $jabber_language[16];?><?php echo $jabber_language[12];?><hr class="hrright"></div>';
                }

                $(head).insertBefore('#cometchat_userslist_jabber');

                $('#cometchat_searchbar').css('display', 'block');

                var hash = '';
                $('#jabber_login').html(jqcc.ccjabber.getJabberVariableLogout(jqcc.cookie('cc_jabber_type')));

                $('#jabber_login').unbind('click');
                $('#jabber_login').bind('click', function() {
                        jqcc.ccjabber.logout();
                });

                jqcc.ccjabber.getFriendsList(1);
            },
            getRecentDataAjaxSuccess:  function(data , id , originalid) {
                var todaysdate = new Date();
                var tdmonth  = todaysdate.getMonth();
                var tddate  = todaysdate.getDate();
                var tdyear = todaysdate.getFullYear();
                var today_date_class = tdmonth+"_"+tddate+"_"+tdyear;
                var ydaysdate = new Date((new Date()).getTime() - 3600000 * 24);
                var ydmonth  = ydaysdate.getMonth();
                var yddate  = ydaysdate.getDate();
                var ydyear = ydaysdate.getFullYear();
                var yday_date_class = ydmonth+"_"+yddate+"_"+ydyear;
                var d = '';
                var month = '';
                var date  = '';
                var year = '';
                var msg_date_class = '';
                var msg_date = '';
                var date_class = '';
                $.each(data, function(id, message) {
                    var selfstyleAvatar = '';
                    var msg_time = message.time;
                    msg_time = msg_time+'';
                    if (msg_time.length == 10){
                        msg_time = parseInt(msg_time * 1000);
                    }
                    var months_set = new Array();
                    <?php

                    $months_array = array($jabber_language[17],$jabber_language[18],$jabber_language[19],$jabber_language[20],$jabber_language[21],$jabber_language[22],$jabber_language[23],$jabber_language[24],$jabber_language[25],$jabber_language[26],$jabber_language[27],$jabber_language[28]);

                    foreach($months_array as $key => $val){
                        ?>
                        months_set.push('<?php echo $val; ?>');
                        <?php
                    }
                    ?>

                    d = new Date(parseInt(msg_time));
                    month  = d.getMonth();
                    date  = d.getDate();
                    year = d.getFullYear();
                    msg_date_class = month+"_"+date+"_"+year;

                    msg_date = months_set[month]+" "+date+", "+year;
                    date_class = "";

                    if(msg_date_class == today_date_class){
                        date_class = "today";
                        msg_date = '<?php echo $jabber_language[29];?>';
                    }else  if(msg_date_class == yday_date_class){
                        date_class = "yesterday";
                        msg_date = '<?php echo $jabber_language[30];?>';
                    }
                    var fromname = $.cometchat.getName(jqcc.ccjabber.encodeName(message.from));
					if (fromname.indexOf(" ") != -1) {
                        fromname = fromname.slice(0, fromname.indexOf(" "));
                    }

                    fromname = fromname.split("@")[0];
                    if(message.type == 'sent'){
						fromname = '<?php echo $language[10];?>';
					}else{
						selfstyleAvatar = '<a class="floatL" href=""><img class="ccmsg_avatar" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar',originalid)+'" title="'+fromname+'"></a>';
					}
                    var sentdata = '';
					if(message.time!=null){
						var ts = new Date(parseInt(message.time));
						sentdata = jqcc[ccjabber.theme].getTimeDisplay(ts);
					}
                    message.msg = message.msg.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    if(message.type == 'sent') {
                        $('#cometchat_tabcontenttext_'+originalid).append('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'">'+msg_date+'</div><div class="cometchat_chatboxmessage" id="cometchat_message_'+message.time+'"><div class="cometchat_chatboxmessagecontent cometchat_self floatR" title="'+sentdata+'"><span id="message_'+message.time+'">'+message.msg+'</span><span id="cometchat_chatboxseen_'+message.time+'"></span></div><div class="selfMsgArrow"><div class="after"></div></div></div>');
                    } else {
                        $('#cometchat_tabcontenttext_'+originalid).append('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'">'+msg_date+'</div><div class="cometchat_chatboxmessage" id="cometchat_message_'+message.time+'">'+selfstyleAvatar+'<div class="cometchat_chatboxmessagecontent floatL" title="'+sentdata+'" style="display: inline-block;"><span id="message_'+message.time+'" class="cometchat_msg">'+message.msg+'</span></div><div class="msgArrow"><div class="after"></div></div></div>');
                    }
                    jqcc[ccjabber.theme].scrollDown(originalid);
                });
                 $('#cometchat_tabcontenttext_'+originalid+' .cometchat_time').hide();
                   $.each($('#cometchat_tabcontenttext_'+originalid+' .cometchat_time'),function (i,divele){
                    var classes = $(divele).attr('class').split(/\s+/);
                    for(var i in classes){
                        if(classes[i].indexOf('cometchat_time_') === 0){
                            $('#cometchat_tabcontenttext_'+originalid+' .'+classes[i]+':first').show();
                        }
                    }
                });
            },
            jabberGetFriendsList: function(first) {
                if ($('#cometchat_userslist_jabber').html() == '') {
                    $('#cometchat_userslist_jabber').html('<div class="cometchat_subsubtitle" style="margin-left:10px;" >Loading...</div>');
                }
                jqcc.ccjabber.getFriendsListAjax(first);
            },
            getFriendsListAjaxSuccess: function(data , first) {
                if (data[0] && data[0].error == '1') {
                    jqcc.ccjabber.logout();
                } else {
                    var buddylisttemp = '';
                    var buddylisttempavatar = '';
                    var md5updated = 0;
                    var onlineNumber = 0;
                    var type = 0;
                    $.each(data, function(id, user) {

                        if (user.id) {
                            var numericid = ((user.id).split('@')[0]).split('-')[1];
                            var found = user.id.indexOf('facebook');
                            ++onlineNumber;
                            user.id = jqcc.ccjabber.encodeName(user.id);
                            var shortname = $.cometchat.getName(user.id);
                            if(found > 0) {
                                type = 1;
                            }
                            if (typeof (user.n) === "undefined" && type == 1) {
                                $.ajax({
                                    url : "//graph.facebook.com/" + numericid,
                                    dataType : "json",
                                    type : "GET",
                                    async : false,
                                    success : function(output) {
                                        user.n = output.name;
                                    }
                                });
                            }
                            if (user.n != '') {
                                var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
                                var test = '';
                                test = pattern.test(user.n);
                                if(test) {
                                    user.n = user.n.split("@")[0];
                                }
                                if (typeof (shortname) === "undefined") {
                                    shortname = user.n;
                                }
                            }
                            user.a = (user.a).replace('http://',window.location.protocol+'//').replace('https://',window.location.protocol+'//');
                            buddylisttemp += '<div id="cometchat_userlist_' + user.id + '" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentname">' + shortname + '</span><span class="cometchat_userscontentdot cometchat_' + user.s + '"></span></div>';
                            buddylisttempavatar += '<div id="cometchat_userlist_' + user.id + '" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar cometchat_buddy_'+user.s+'"><img class="cometchat_userscontentavatarimage" original="' + user.a + '"></span><span class="cometchat_userscontentname">' + shortname + '</span></div>';
                            $.cometchat.userAdd(user.id, user.s, user.m, user.n, user.a, '');
                        }
                        if (user.md5) {
                            hash = user.md5;
                            md5updated = 1;
                        }
                    });
                    if (onlineNumber == 0) {
                        buddylisttempavatar = ("<div class='cometchat_nofriends' style='margin-bottom:10px'><?php echo $jabber_language[14];?></div>");
                    }
                    if (md5updated) {
                        if (jqcc.cookie('cc_jabber') && jqcc.cookie('cc_jabber') == 'true') {
                            $.cometchat.updateJabberOnlineNumber(onlineNumber);
                            $.cometchat.replaceHtml('cometchat_userslist_jabber', '<div>' + buddylisttempavatar + '</div>');
                            $('.cometchat_userlist').unbind('click');
                            $('.cometchat_userlist').bind('click', function(e) {
                                $.cometchat.userClick(e.target);
                            });
                            if ($.cometchat.getSessionVariable('buddylist') == 1) {
                                $(".cometchat_userscontentavatar img").each(function() {
                                    if ($(this).attr('original')) {
                                        $(this).attr("src", $(this).attr('original'));
                                        $(this).removeAttr('original');
                                    }
                                });
                            }
                            $('#cometchat_search').keyup();
                        }
                    }
                    clearTimeout(ccjabber.friendsTimer);
                    ccjabber.friendsTimer = setTimeout(function() {
                        jqcc.ccjabber.getFriendsList();
                    }, 60000);
                    if (first) {
                        jqcc.ccjabber.getMessages();
                    }
                }
            }
        });
})(jqcc);