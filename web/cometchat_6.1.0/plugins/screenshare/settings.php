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
	$errorMsg = '';

	$schkd = '';
	$wchkd = '';

	$hideSelfhostedSettings = '';
	
	$commonSettings = '';
	$screenshare_mobile_warning = 'This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.';
	$message = "<h3 id='data'></h3>";

	if ($screensharePluginType == '1') {
		$schkd = "selected";
		$errorMsg = '';
        $hideSelfhostedSettings = '';
	}else if ($screensharePluginType == '0') {
		$wchkd = "selected";
		$commonSettings = 'display:none;';
		$hideSelfhostedSettings = 'style="display:none;"';
        $screenshare_mobile_warning = 'This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC (Chrome, Firefox &amp; Opera)';
	} else {
		$hideSelfhostedSettings = 'style="display:none;"';
		$errorMsg = '';
	}

echo <<<EOD
<!DOCTYPE html>
<head>
$getstylesheet
<script src="../js.php?type=core&name=jquery"></script>
<script>
	$ = jQuery = jqcc;

		$(function() {
			$('#errormsg').hide();
			var selected = $("#pluginTypeSelector :selected").val();
			if(selected=="0") {
				$('h3').show();
				$('#data').html('This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.');
				$('#screenshare_mobile_warning').html('').hide();
			} else if(selected=="1") {
				$('h3').show();
				$('#data').html('This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.');
				$('#screenshare_mobile_warning').html('').hide();
			} else {
				$("#centernav").hide();
				$("#SelfhostedSettings").hide();
				$('h3').show();
				$('#data').html('This option will work in mobile apps (iOS &amp; Android) and on modern desktop browsers which have support for webRTC.');
			}

			$("#pluginTypeSelector").change(function() {
				var selected = $("#pluginTypeSelector :selected").val();
				var errorMsg = 0;
				$('#screenshare_mobile_warning').html('This option does not support audio/video chat on mobile.');
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

		function resizeWindow()	{
				window.resizeTo(650, ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
		}
</script>
</head>
<body>
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=plugin&name=screenshare&process=true" method="post">
<div id="content" style="width:auto">
		<h2>ScreenShare Settings</h2>
				{$message}
		<div style="margin-bottom:10px;">
				<div class="title">Use :</div>
				<div class="element" id="">
					<select name="screensharePluginType" id="pluginTypeSelector">
						<option value="0" {$wchkd}>CometChat Servers (WebRTC)</option>
						<option value="1" {$schkd}>SelfHosted WebRTC</option>
					</select>
				</div>
				<div style="clear:both;padding:5px;"></div>
				<div id="screenshare_mobile_warning" style="padding:8px; border-radius: 7px;border: 1px solid #cccccc;width: 90%;">{$screenshare_mobile_warning}</div>
				<div style="clear:both;padding:5px;"></div>
				{$errorMsg}

				<div id="centernav" style="width:380px; $commonSettings ">
	                <div class="SelfhostedSettings" $hideSelfhostedSettings>
						<div class="title" >Server URL:</div><div class="element"><input type="text" class="inputbox" name="selfhostedwebrtc" value="$selfhostedwebrtc"></div>
					</div>
				</div>

			<div style="clear:both;padding:5px;"></div>
		</div>
		
		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
</div>
</form>
</body>
<script>resizeWindow();</script>
EOD;
} else {
	configeditor($_POST);
	header("Location:?module=dashboard&action=loadexternal&type=plugin&name=screenshare");
}