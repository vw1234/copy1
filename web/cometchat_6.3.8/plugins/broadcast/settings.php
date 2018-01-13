<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

if (empty($_GET['process'])) {
	global $getstylesheet;
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

	$curl = 0;

	$schkd = '';
	$wchkd = '';

	$hideSelfhostedSettings = '';

	$commonSettings = '';
	$avchat_mobile_warning = 'This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.';

	$message = "<h3 id='data'></h3>";

	if ($videoPluginType == '1') {
		$schkd = "selected";
        $hideSelfhostedSettings = '';
	}else{
		$wchkd = "selected";
		$commonSettings = 'display:none;';
		$hideSelfhostedSettings = 'style="display:none;"';
        $avchat_mobile_warning = 'This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC (Chrome, Firefox &amp; Opera)';
	}

echo <<<EOD
<!DOCTYPE html>

<html>
<head>
	<script type="text/javascript" src="../js.php?admin=1"></script>
	<script type="text/javascript" language="javascript">

		$(function() {
			$('#errormsg').hide();
			var selected = $("#pluginTypeSelector :selected").val();
			if(selected=="0") {
				$('h3').show();
				$('#data').html('This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.');
				$('#avchat_mobile_warning').html('').hide();
			} else if(selected=="1") {
				$('h3').show();
				$('#data').html('This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.');
				$('#avchat_mobile_warning').html('').hide();
			} else {
				$("#centernav").hide();
				$("#SelfhostedSettings").hide();
				$('h3').show();
				$('#data').html('This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.');
			}

			$("#pluginTypeSelector").change(function() {
				var selected = $("#pluginTypeSelector :selected").val();
				var errorMsg = 0;
				$('#avchat_mobile_warning').html('This option does not support audio/video chat on mobile.');
				if(selected=="1") {
					$("#centernav").show();
                    $(".SelfhostedSettings").show();
					$('h3').show();
					$('#errormsg').hide();
					$('#data').html('This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.');
				} else if(selected=="0") {
					$("#centernav").hide();
					$("#SelfhostedSettings").hide();
					$('h3').show();
					$('#data').html('This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.');
				}
				resizeWindow();
			});

			setTimeout(function(){
				resizeWindow();
			},200);
		});
		function resizeWindow() {
				window.resizeTo(650, ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
		}
	</script>

	$getstylesheet

</head>

<body>
	<form style="height:100%" action="?module=dashboard&action=loadexternal&type=plugin&name=broadcast&process=true" method="post">
	<div id="content" style="width:auto">
		<h2>Audio/Video Broadcast Settings</h2><br />
				{$message}
		<div style="margin-bottom:10px;">
				<div class="title">Use :</div>
				<div class="element" id="">
					<select name="videoPluginType" id="pluginTypeSelector">
						<option value="0" {$wchkd}>CometChat Servers (WebRTC)</option>
						<option value="1" {$schkd}>SelfHosted WebRTC</option>
					</select>
				</div>
					<div style="clear:both;padding:10px;"></div>
				<div id="avchat_mobile_warning" style="padding:8px; border-radius: 7px;border: 1px solid #cccccc;width: 90%;">{$avchat_mobile_warning}</div>
					<div style="clear:both;padding:10px;"></div>

				<div style="clear:both;padding:10px;"></div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
	</div>
	</form>
</body>
</html>
EOD;
} else {
	configeditor($_POST);
	header("Location:?module=dashboard&action=loadexternal&type=plugin&name=broadcast");
}
