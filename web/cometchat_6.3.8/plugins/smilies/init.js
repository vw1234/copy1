<?php
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

	if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php")) {
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php");
	}

	foreach ($smilies_language as $i => $l) {
		$smilies_language[$i] = str_replace("'", "\'", $l);
	}
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccsmilies = (function () {

		var title = '<?php echo $smilies_language[0];?>';
		var height = <?php echo $smlHeight;?>;
		var width = <?php echo $smlWidth;?>;
		var theme = "<?php echo $theme;?>";
		var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|IEMobile|blackberry|palm|symbian/i);

        return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				var caller = '';
				var windowMode = 0;
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				if(typeof(params.caller) != "undefined") {
					caller = params.caller;
				}
				if(chatroommode == 1){
					baseUrl = $.cometchat.getBaseUrl();
					baseData = $.cometchat.getBaseData();
					loadPopupInChatbox(baseUrl+'plugins/smilies/index.php?chatroommode=1&id='+id+'&basedata='+baseData+'&caller='+caller, 'smilies', 0, id,1);
					/*$('#cometchat_group_'+id+'_popup').find('#cometchat_groupplugins_openup_icon_'+id).toggleClass('cometchat_pluginsopenup_arrowrotate');*/
				} else {
					baseUrl = $.cometchat.getBaseUrl();
					baseData = $.cometchat.getBaseData();
					loadPopupInChatbox(baseUrl+'plugins/smilies/index.php?id='+id+'&basedata='+baseData+'&caller='+caller, 'smilies', 0, id,0);
					/*$('#cometchat_user_'+id+'_popup').find('#cometchat_plugins_openup_icon_'+id).toggleClass('cometchat_pluginsopenup_arrowrotate');*/
				}
			},

			addtext: function (params) {
				var string = '';
				var id = params.to;
				var text = params.pattern+' ';
				var chatroommode = params.chatroommode;
				if(chatroommode == 1 && mobileDevice == null) {
					if(theme == 'embedded'){
						var currentroom_textarea = $('#currentroom').find('textarea.cometchat_textarea');
					}else{
						var currentroom_textarea = $('#cometchat_group_'+id+'_popup').find('textarea.cometchat_textarea');
					}

					if(mobileDevice == null){
	                    currentroom_textarea.focus();
	                }
					string = currentroom_textarea.val();
					if (string.charAt(string.length-1) == ' ') {
						currentroom_textarea.val(currentroom_textarea.val()+text);
					} else {
						if (string.length == 0) {
							currentroom_textarea.val(text);
						} else {
							currentroom_textarea.val(currentroom_textarea.val()+' '+text);
						}
					}
				} else if(chatroommode == 1 && mobileDevice) {
					if(theme == 'embedded'){
						var currentroom_textarea = $('#currentroom').find('textarea.cometchat_textarea');
					}else{
						var currentroom_textarea = $('#cometchat_group_'+id+'_popup').find('textarea.cometchat_textarea');
					}
					currentroom_textarea.focus();
					string = currentroom_textarea.val();
					currentroom_textarea.focus();
					if (string.charAt(string.length-1) == ' ') {
						currentroom_textarea.val(currentroom_textarea.val()+text);
					} else {
						if (string.length == 0) {
							currentroom_textarea.val(text);
						} else {
							currentroom_textarea.val(currentroom_textarea.val()+' '+text);
						}
					}
				} else if(chatroommode == 0 && mobileDevice) {
					if($('#cometchat_user_'+id+'_popup').length > 0) {
						var cometchat_user_textarea = $('#cometchat_user_'+id+'_popup').find('textarea.cometchat_textarea');
						jqcc.cometchat.chatWith(id);
						cometchat_user_textarea.focus();
						string = cometchat_user_textarea.val();
						cometchat_user_textarea.focus();
						if (string.charAt(string.length-1) == ' ') {
							cometchat_user_textarea.val(string+text);
						} else {
							if (string.length == 0) {
								cometchat_user_textarea.val(text);
							} else {
								cometchat_user_textarea.val(string+' '+text);
							}
						}

					} else {
						jqcc.cometchat.chatWith(id);
						var cometchat_user_textarea = $('#cometchat_user_'+id+'_popup').find('textarea.cometchat_textarea');
						cometchat_user_textarea.focus();
						string = cometchat_user_textarea.val();

						if (string.charAt(string.length-1) == ' ') {
							cometchat_user_textarea.val(string+text);
						} else {
							if (string.length == 0) {
								cometchat_user_textarea.val(text);
							} else {
								cometchat_user_textarea.val(string+' '+text);
							}
						}
						var tabcontenttext_height = ($(window).height()*40)/100;
                    	jqcc('#cometchat_tabcontenttext_'+id).css('height',tabcontenttext_height);
					}


				} else {
					if($('#cometchat_user_'+id+'_popup').length > 0) {
						var cometchat_user_textarea = $('#cometchat_user_'+id+'_popup').find('textarea.cometchat_textarea');
						cometchat_user_textarea.focus();
						jqcc.cometchat.chatWith(id);
						string = cometchat_user_textarea.val();

						if (string.charAt(string.length-1) == ' ') {
							cometchat_user_textarea.val(string+text);
						} else {
							if (string.length == 0) {
								cometchat_user_textarea.val(text);
							} else {
								cometchat_user_textarea.val(string+' '+text);
							}
						}
					} else {
						jqcc.cometchat.chatWith(id);
						var cometchat_user_textarea = $('#cometchat_user_'+id+'_popup').find('textarea.cometchat_textarea');
						cometchat_user_textarea.focus();
						string = cometchat_user_textarea.val();

						if (string.charAt(string.length-1) == ' ') {
							cometchat_user_textarea.val(string+text);
						} else {
							if (string.length == 0) {
								cometchat_user_textarea.val(text);
							} else {
								cometchat_user_textarea.val(string+' '+text);
							}
						}
					}
				}
			}
        };
    })();

})(jqcc);
