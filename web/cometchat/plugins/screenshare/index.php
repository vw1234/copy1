<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php");
}

if ($p_<4) exit;

$to = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['to']);
if (!empty($_REQUEST['chatroommode']) && !empty($_REQUEST['initiator'])) {
	$grp = md5($to).md5(mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['initiator']));
} else {
	$grp = $userid<$to? md5($userid).md5($to) : md5($to).md5($userid);
}


if ($_GET['action'] == 'request') {
    if (!empty($_REQUEST['chatroommode'])) {
		sendChatroomMessage($_REQUEST['to'],$screenshare_language[2]." <a href='javascript:void(0);' class='acceptSceenshare' to='".$_REQUEST['to']."' grp='".$grp."' initiator='".$userid."' join_url='' start_url='' chatroommode='1' mobileAction=\"javascript:jqcc.ccscreenshare.accept('".$_REQUEST['to']."','".$grp."','','', 'chatroommode');\">".$screenshare_language[3]."</a>",0);
    } else {
        $optionalmessage = 0;

		if(function_exists('hooks_sendOptionalMessage')) {
			$optionalmessage = hooks_sendOptionalMessage(array('to' => $_REQUEST['to'], 'plugin' => 'screenshare'));
		}

		if($optionalmessage == 0){
        	$response =  sendMessage($_REQUEST['to'],$screenshare_language[2]." <a href='javascript:void(0);' class='acceptSceenshare' to='".$userid."' grp='".$grp."' join_url='' start_url='' chatroommode='0' mobileAction=\"javascript:jqcc.ccscreenshare.accept('".$userid."','".$grp."');\">".$screenshare_language[3]."</a> ".$screenshare_language[4],1);

			$processedMessage = $_SESSION['cometchat']['user']['n'].": ".$screenshare_language[2];
			pushMobileNotification($_REQUEST['to'],$response['id'],$processedMessage);

	        $temp_callback = $_REQUEST['callback'];
	        $_REQUEST['callback'] = time();
	        sendMessage($_REQUEST['to'],$screenshare_language[5],2);
	        $_REQUEST['callback'] = $temp_callback;
        }
    }

	if (!empty($_GET['callback'])) {
		header('content-type: application/json; charset=utf-8');
		echo $_GET['callback'].'()';
	}
	exit;
}

if ($_GET['action'] == 'accept') {
	sendMessage($_REQUEST['to'],$screenshare_language[6],1);
	if (!empty($_GET['callback'])) {
		header('content-type: application/json; charset=utf-8');
		echo $_GET['callback'].'()';
	}
	exit;
}

if ($_GET['action'] == 'screenshare') {

	global $lightboxWindows;

	$id = $_GET['id'];
	$type = $_GET['type'];

	if (!empty($_GET['chatroommode'])) {
		if(!empty($_GET['roomid'])){
			sendChatroomMessage($_GET['roomid'],$screenshare_language[2]." <a href='javascript:void(0);' class='acceptSceenshare' to='".$userid."' grp='".$_GET['id']."' join_url='' start_url='' chatroommode='1' mobileAction=\"javascript:jqcc.ccscreenshare.accept('".$userid."','".$_GET['id']."');\">".$screenshare_language[3]."</a>",0);
		}
	}

	ini_set('display_errors', 0);

	$grp = md5($channelprefix.$grp);

	if($type == 1){
		echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>{$screenshare_language[0]}</title>
		<script src="../../js.php?type=core&name=jquery"></script>
		<script>
			window.location = "https://{$webRTCPHPServer}/?room={$grp}&pluginname=screenshare&screenshare=1&cssurl=";
		</script>
		<style type="text/css">
			html, body, div, span, applet, object, iframe,
			h1, h2, h3, h4, h5, h6, p, blockquote, pre,
			a, abbr, acronym, address, big, cite, code,
			del, dfn, em, font, img, ins, kbd, q, s, samp,
			small, strike, strong, sub, sup, tt, var,
			dl, dt, dd, ol, ul, li,
			fieldset, form, label, legend,
			table, caption, tbody, tfoot, thead, tr, th, td {
				margin: 0;
				padding: 0;
				border: 0;
				outline: 0;
				font-weight: inherit;
				font-style: inherit;
				font-size: 100%;
				font-family: inherit;
				vertical-align: baseline;
			}

			html {
			  height: 100%;
			  overflow: hidden; /* Hides scrollbar in IE */
			}

			body {
			  height: 100%;
			  margin: 0;
			  padding: 0;
			  background:#000000;
			}
		</style>
	</head>
	<body>
		<iframe id ="webrtc" src="//{$webRTCPHPServer}/?room={$grp}&pluginname=screenshare&screenshare=1&cssurl=" width=100% height=100% seamless allowfullscreen></iframe>
	</body>
</html>
EOD;
	}else{
		echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>ScreenViewer</title>
		<script src="../../js.php?type=core&name=jquery"></script>
		<script>
			window.location = "https://{$webRTCPHPServer}/?room={$grp}&pluginname=screenshare&screenshare=0&cssurl=";
		</script>
	</head>
	<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0">
		<div id="screenViewerDIV"></div>
		<iframe id ="webrtc" src="//{$webRTCPHPServer}/?room={$grp}&pluginname=screenshare&screenshare=0&cssurl=" width=100% height=100% seamless allowfullscreen></iframe>
	</body>
</html>
EOD;
	}
	exit;
}
