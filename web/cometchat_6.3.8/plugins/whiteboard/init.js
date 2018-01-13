<?php
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

	if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php")) {
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php");
	}

	foreach ($whiteboard_language as $i => $l) {
		$whiteboard_language[$i] = str_replace("'", "\'", $l);
	}
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccwhiteboard = (function () {

		var title = '<?php echo $whiteboard_language[0];?>';
		var lastcall = 0;
		var height = <?php echo $whitebHeight;?>;
		var width = <?php echo $whitebWidth;?>;
		var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);
        return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				var windowMode = 0;
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				if(chatroommode == 1){
					var currenttime = new Date();
					currenttime = parseInt(currenttime.getTime()/1000);
					if (currenttime-lastcall > 10) {
						baseUrl = $.cometchat.getBaseUrl();
						basedata = $.cometchat.getBaseData();

						var random = currenttime;
						if(!mobileDevice){
							loadCCPopup(baseUrl+'plugins/whiteboard/index.php?action=whiteboard&chatroommode=1&subaction=request&id='+id+'&basedata='+basedata, 'whiteboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0,width=<?php echo $whitebWidth;?>,height=<?php echo $whitebHeight;?>",width,height-50,'<?php echo $whiteboard_language[9];?>',1,1,1,1,windowMode);
						} else{
							loadCCPopup(baseUrl+'plugins/whiteboard/index.php?action=whiteboard&chatroommode=1&subaction=request&id='+id+'&basedata='+basedata, 'whiteboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0,width=<?php echo $whitebWidth;?>,height=<?php echo $whitebHeight;?>",width,height-50,'<?php echo $whiteboard_language[9];?>',1,1,1,1,1);
						}
					} else {
						alert('<?php echo $whiteboard_language[1];?>');
					}

				} else {
					var currenttime = new Date();
					currenttime = parseInt(currenttime.getTime()/1000);
					if (currenttime-lastcall > 10) {
						baseUrl = $.cometchat.getBaseUrl();
						baseData = $.cometchat.getBaseData();

						var random = currenttime;
						lastcall = currenttime;
						if(!mobileDevice){
							loadCCPopup(baseUrl+'plugins/whiteboard/index.php?action=whiteboard&id='+id+'&random='+random+'&basedata='+baseData, 'whiteboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width="+width+",height="+height,width,height-50,'<?php echo $whiteboard_language[9];?>',0,1,1,1,windowMode);
						} else {
							loadCCPopup(baseUrl+'plugins/whiteboard/index.php?action=whiteboard&id='+id+'&random='+random+'&basedata='+baseData, 'whiteboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width="+width+",height="+height,width,height-50,'<?php echo $whiteboard_language[9];?>',0,1,1,1,1);
						}

					} else {
						alert('<?php echo $whiteboard_language[1];?>');
					}
				}
			},

			accept: function (params) {
				var id = params.to;
				var random = params.random;
				var room = params.room;
				var chatroommode = params.chatroommode;
				var windowMode = 0;
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				baseUrl = $.cometchat.getBaseUrl();
				baseData = $.cometchat.getBaseData();
				$.getJSON(baseUrl+'plugins/whiteboard/index.php?action=accept&callback=?', {to: id, basedata: baseData});
				if(!mobileDevice){
					loadCCPopup(baseUrl+'plugins/whiteboard/index.php?action=whiteboard&id='+id+'&room='+room+'&basedata='+baseData, 'whiteboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width="+width+",height="+height,width,height-50,'<?php echo $whiteboard_language[9];?>',0,1,1,1,windowMode);
				} else{
					loadCCPopup(baseUrl+'plugins/whiteboard/index.php?action=whiteboard&id='+id+'&room='+room+'&basedata='+baseData, 'whiteboard',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width="+width+",height="+height,width,height-50,'<?php echo $whiteboard_language[9];?>',0,1,1,1,1);
				}
			}
		};
    })();

})(jqcc);

jqcc(document).ready(function(){
	jqcc('.accept_White').live('click',function(){
		var to = jqcc(this).attr('to');
		var random = jqcc(this).attr('random');
		var room = jqcc(this).attr('room');
		var chatroommode = jqcc(this).attr('chatroommode');
		var controlparameters = {"to":to, "random":random, "room":room, "chatroommode":chatroommode};
		jqcc.ccwhiteboard.accept(controlparameters);
	});
});
