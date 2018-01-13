<?php

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php");
}

foreach ($clearconversation_language as $i => $l) {
	$clearconversation_language[$i] = str_replace("'", "\'", $l);
}
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
 */

 (function($){

 	$.ccclearconversation = (function () {

 		var title = '<?php echo $clearconversation_language[0];?>';
 		var type = '<?php echo $isDelete;?>';

 		return {

 			getTitle: function() {
 				return title;
 			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;

 				if(chatroommode == 1) {
 					if($("#currentroom_convotext").length){
 						if ($("#currentroom_convotext").html() != '') {
 							baseUrl = $.cometchat.getBaseUrl();
 							basedata = $.cometchat.getBaseData();
 							var lastid = parseInt($('#currentroom_convotext').find('.cometchat_chatboxmessage:last').attr('id').replace('cometchat_groupmessage_',''));
 							$.getJSON(baseUrl+'plugins/clearconversation/index.php?action=clear&basedata='+basedata+'&chatroommode=1&callback=?', {clearid: id, lastid: lastid});
 							$("#currentroom_convotext").html('');
 						}
 					}else{
 						if ($("#cometchat_grouptabcontenttext_"+id).html() != '') {
 							baseUrl = $.cometchat.getBaseUrl();
 							basedata = $.cometchat.getBaseData();
 							var lastid = parseInt($('#cometchat_grouptabcontenttext_'+id).find('.cometchat_chatboxmessage:last').attr('id').replace('cometchat_groupmessage_',''));
 							$.getJSON(baseUrl+'plugins/clearconversation/index.php?action=clear&basedata='+basedata+'&chatroommode=1&callback=?', {clearid: id, lastid: lastid});
                            $("#cometchat_grouptabcontenttext_"+id).find('.cometchat_ts').remove();
                            $("#cometchat_grouptabcontenttext_"+id).find('.cometchat_chatboxmessage').remove();
                            $("#cometchat_grouptabcontenttext_"+id).find('.cometchat_time').remove();
 						}
 					}
 				} else {
 					var settings = jqcc.cometchat.getSettings();
 					if ($("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").html() != '') {
 						baseUrl = $.cometchat.getBaseUrl();
 						baseData = $.cometchat.getBaseData();
                        
 						$.getJSON(baseUrl+'plugins/clearconversation/index.php?action=clear&callback=?', {clearid: id, basedata: baseData});
 						
                        if($("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_chatboxmessage").length == 0){
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_message_container > div.cometchat_messagebox").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_time").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_message_container > div.cometchat_time").remove();
                            $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > span.cometchat_sentnotification").remove();
 						}else{
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_chatboxmessage").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > table.cometchat_iphone").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_time").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_message_container > div.cometchat_time").remove();
                            $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > span.cometchat_sentnotification").remove();
 						}
 					}
 				}
 			}

 		};
 	})();

 })(jqcc);
