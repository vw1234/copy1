<?php
/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

/*use OpenTok\OpenTok;*/  //uncomment this line if you are using OpenTok.

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}
$webrtcTheme = $theme;
if (!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."avchat".$rtl.".css")) {
	$theme = "standard";
}

if ($p_<4) exit;

if ($videoPluginType == '1') {
	$videoPluginType = '0';
}

$basedata = $to = $grp  = $action = $chatroommode = $embed = $id = null;

if(!empty($_REQUEST['basedata'])) {
    $basedata = $_REQUEST['basedata'];
}
if(!empty($_REQUEST['to'])){
	$to = $_REQUEST['to'];
}
if(!empty($_REQUEST['grp'])){
	$grp = $_REQUEST['grp'];
}
if(!empty($_REQUEST['action'])){
	$action = $_REQUEST['action'];
}
if(!empty($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
}
if(!empty($_REQUEST['chatroommode'])){
	$chatroommode = $_REQUEST['chatroommode'];
}
if(!empty($_REQUEST['embed'])){
	$embed = $_REQUEST['embed'];
}
$cbfn = '';
if(!empty($_REQUEST['callbackfn'])){
	$cbfn = $_REQUEST['callbackfn'];
	$_SESSION['noguestmode'] = '1';
}

$cc_theme = '';
if(!empty($_REQUEST['cc_theme'])){
	$cc_theme = $_REQUEST['cc_theme'];
}
configCheck();

if($action == 'endcall') {
	if (!empty($chatroommode)) {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'endcall', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'endcall', 'params' => array('grp' => $grp, 'chatroommode' => 0));
		$controlparameters = json_encode($controlparameters);
		sendMessage($to,'CC^CONTROL_'.$controlparameters,2);
		incrementCallback();
		sendMessage($to, 'CC^CONTROL_'.$controlparameters,1);
		decrementCallback();
	}
	if (!empty($_GET['callback'])) {
		echo $_GET['callback'].'('.json_encode(1).')';
	} else {
		echo json_encode(1);
	}
}
if($action == 'rejectcall') {
	if (!empty($chatroommode)) {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'rejectcall', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'rejectcall', 'params' => array('grp' => $grp, 'chatroommode' => 0));
		$controlparameters = json_encode($controlparameters);
		sendMessage($to, 'CC^CONTROL_'.$controlparameters,1);
	}
	if (!empty($_GET['callback'])) {
		echo $_GET['callback'].'('.json_encode(1).')';
	} else {
		echo json_encode(1);
	}
}

if($action == 'noanswer') {
	if (!empty($chatroommode)) {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'noanswer', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'noanswer', 'params' => array('grp' => $grp, 'chatroommode' => 0));
		$controlparameters = json_encode($controlparameters);
		sendMessage($to, 'CC^CONTROL_'.$controlparameters,1);
	}
	if (!empty($_GET['callback'])) {
		echo $_GET['callback'].'('.json_encode(1).')';
	} else {
		echo json_encode(1);
	}
}

if($action == 'canceloutgoingcall') {
	if (!empty($chatroommode)) {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'canceloutgoingcall', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'canceloutgoingcall', 'params' => array('grp' => $grp, 'chatroommode' => 0));
		$controlparameters = json_encode($controlparameters);
		sendMessage($to, 'CC^CONTROL_'.$controlparameters,2);
		incrementCallback();
		sendMessage($to, 'CC^CONTROL_'.$controlparameters,1);
		decrementCallback();
	}
	if (!empty($_GET['callback'])) {
		echo $_GET['callback'].'('.json_encode(1).')';
	} else {
		echo json_encode(1);
	}
}

if($action == 'busycall') {
	if (!empty($chatroommode)) {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'busycall', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'avchat', 'method' => 'busycall', 'params' => array('grp' => $grp, 'chatroommode' => 0));
		$controlparameters = json_encode($controlparameters);
		sendMessage($to, 'CC^CONTROL_'.$controlparameters,1);
	}
	if (!empty($_GET['callback'])) {
		echo $_GET['callback'].'('.json_encode(1).')';
	} else {
		echo json_encode(1);
	}
}


if($videoPluginType == '3') {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'OpenTok'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
	$apiKey = $opentokApiKey;
	$apiSecret = $opentokApiSecret;
	$apiObj = new OpenTok($apiKey, $apiSecret);
}
if ($action == 'request') {
	$caller = '';
	if(!empty($_REQUEST['caller'])){
		$caller = $_REQUEST['caller'];
	}
	$avchat_token = '';
	if(empty($grp)){
		$grp = $userid<$to? md5($userid).md5($to) : md5($to).md5($userid);
		$grp = md5('avchat'.$grp);
		if($videoPluginType == '3' ){
			$location = time();
			if(!empty($_SERVER['REMOTE_ADDR'])){
				$location = $_SERVER['REMOTE_ADDR'];
			}
			try {
				$session = $apiObj->createSession(array( 'location' => $location ));
				$grp = $session->getSessionId();
				$avchat_token = $apiObj->generateToken($grp);
			}catch (Exception $e) {
				sendMessage($to,"Please ask your administrator to configure this plugin using administration panel.",2);
				exit;
			}
		}
	}

	if(!empty($chatroommode)){
		sendChatroomMessage($to, $avchat_language[19]." <a token ='".$avchat_token."' href='javascript:void(0);' class='join_Avchat' to='".$to."' grp ='".$grp."' caller='".$caller."' mobileAction=\"javascript:jqcc.ccavchat.join('".$grp."');\" >".$avchat_language[20]."</a> ",0);
	}else{
		if($videoPluginType == '0'){
			$response = sendMessage($to,$avchat_language[2]." <a class='avchat_link_".$grp." acceptAVChat' token ='".$avchat_token."' href='javascript:void(0);' to='".$userid."' grp='".$grp."' join_url='' start_url='' chatroommode='0' caller='".$caller."'  mobileAction=\"javascript:jqcc.ccavchat.accept('".$userid."','".$grp."');\">".$avchat_language[3]."</a> ".$avchat_language[45]."<a href='javascript:void(0);' class='avchat_link_".$grp."' onclick=\"javascript:jqcc.ccavchat.reject_call('".$userid."','".$grp."');\">".$avchat_language[43].".</a>".$avchat_language[46],1);
			pushMobileNotification($to,$response['id'],$grp."_#wrtcgrp_".$_SESSION['cometchat']['user']['n'].": ".$avchat_language[2],'0','AVC',getTimeStamp());
		} else {
			sendMessage($to,$avchat_language[2]." <a class='avchat_link_".$grp." acceptAVChat' token ='".$avchat_token."' href='javascript:void(0);' to='".$userid."' grp='".$grp."' join_url='' start_url='' caller='".$caller."' chatroommode='0' mobileAction=\"javascript:jqcc.ccavchat.accept('".$userid."','".$grp."');\" >".$avchat_language[3]."</a> ".$avchat_language[46],1);
		}
		incrementCallback();
		$_REQUEST['callback'];
		if($videoPluginType == '0') {
			sendMessage($to,$avchat_language[5].$avchat_language[44]."<a href='javascript:void(0);' class='avchat_link_".$grp."' onclick=\"javascript:jqcc.ccavchat.cancel_call('".$to."','".$grp."');\">".$avchat_language[43].".</a>",2);
		} else {
			sendMessage($to,$avchat_language[5],2);
		}
		decrementCallback();
	}
	if (!empty($_REQUEST['callback'])) {
		header('content-type: application/json; charset=utf-8');
		echo $_REQUEST['callback'].'("'.$grp.'")';
	} else {
		echo json_encode($grp);
	}
	exit;
}
if ($action == 'accept') {
	$avchat_token = '';
	$caller = '';
	if(!empty($_REQUEST['caller'])){
		$caller = $_REQUEST['caller'];
	}
	if ($videoPluginType == '3') {
		$avchat_token = $apiObj->generateToken($grp);
	}
	sendMessage($to,$avchat_language[6]." <a token ='".$avchat_token."' href='javascript:void(0);' class='avchat_link_".$grp." accept_AVfid' to='".$userid."' grp='".$grp."' start_url='' caller='".$caller."' mobileAction=\"javascript:jqcc.ccavchat.accept_fid('".$userid."','".$grp."');\" >".$avchat_language[7]."</a>",1);
	if (!empty($_REQUEST['callback'])) {
		header('content-type: application/json; charset=utf-8');
		echo $_REQUEST['callback'].'()';
	}
	exit;
}
if ($action == 'call') {
	$baseUrl = BASE_URL;
	$embed = '';
	$embedcss = '';
	$resize = 'window.resizeTo(';
	$invitefunction = 'window.open';
	if (!empty($embed) && $embed == 'web') {
		$embed = 'web';
		$resize = "parent.resizeCCPopup('audiovideochat',";
		$embedcss = 'embed';
		$invitefunction = 'parent.loadCCPopup';
	}
	if (!empty($embed) && $embed == 'desktop') {
		$embed = 'desktop';
		$resize = "parentSandboxBridge.resizeCCPopupWindow('audiovideochat',";
		$embedcss = 'embed';
		$invitefunction = 'parentSandboxBridge.loadCCPopupWindow';
	}
	if($videoPluginType == '2') {
		ini_set('display_errors', 0);
		$mode = 3;
		$flashvariables = '{grp:"'.$grp.'",connectUrl: "'.$connectUrl.'",name:"",quality: "'. $quality. '",fps:"'.$fps.'",mode: "'.$mode.'",maxP: "'.$maxP.'",camWidth: "'.$camWidth.'",camHeight: "'.$camHeight.'",soundQuality: "'.$soundQuality.'"}';
		$file = '_fms';

		echo <<<EOD
		<!DOCTYPE html>
		<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
			<title>{$avchat_language[8]}</title>
			<script src="../../js.php?type=core&name=jquery"></script>
			<link href="../../css.php?type=plugin&name=avchat&subtype=fmsred5" type="text/css" rel="stylesheet" >
				<script type="text/javascript" src="../../js.php?type=plugin&name=avchat&subtype=fmsred5&embed={$embed}&callbackfn={$cbfn}"></script>
				<script type="text/javascript">
					var swfVersionStr = "10.1.0";
					var xiSwfUrlStr = "playerProductInstall.swf";
					var flashvars = {$flashvariables};
					var params = {};
					params.quality = "high";
					params.bgcolor = "#000000";
					params.allowscriptaccess = "sameDomain";
					params.allowfullscreen = "true";
					var attributes = {};
					attributes.id = "audiovideochat";
					attributes.name = "audiovideochat";
					attributes.align = "middle";
					swfobject.embedSWF(
						"audiovideochat{$file}.swf?v2.2", "flashContent",
						"100%", "100%",
						swfVersionStr, xiSwfUrlStr,
						flashvars, params, attributes);
					swfobject.createCSS("#flashContent", "display:block;text-align:left;");
					function getFocus() {
						setTimeout('self.focus();',10000);
					}
					window.onbeforeunload = function() {
						var AddCallbackExample = document.getElementById("audiovideochat_fms");
						AddCallbackExample.getUnsavedDataWarning();
					}
				</script>
			</head>
			<body onblur="getFocus()">
				<div id="flashContent">
					<p>
						To view this page ensure that Adobe Flash Player version
						10.1.0 or greater is installed.
					</p>
					<script type="text/javascript">
						var pageHost = ((document.location.protocol == "https:") ? "https://" :	"http://");
						document.write("<a href='http://www.adobe.com/go/getflashplayer'><img src='" + pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif' alt='Get Adobe Flash player' /></a>" );
					</script>
				</div>
			</body>
		</html>
EOD;
	}elseif($videoPluginType == '3'){
		if (!empty($chatroommode)) {
			$sql = ("select vidsession from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$grp)."'");
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			$chatroom = mysqli_fetch_assoc($query);
			if (empty($chatroom['vidsession'])) {
				$location = time();
				if(!empty($_SERVER['REMOTE_ADDR'])){
					$location = $_SERVER['REMOTE_ADDR'];
				}
				try {
					$session = $apiObj->createSession(array( 'location' => $location ));
					$newsessionid = $session->getSessionId();
				}catch (Exception $e) {
					echo "<div style='background:white;padding:15px;'>Please ask your administrator to configure this plugin using administration panel.</div>";
					exit;
				}
				$sql = ("update cometchat_chatrooms set  vidsession = '".mysqli_real_escape_string($GLOBALS['dbh'],$newsessionid)."' where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$grp)."'");
				$query = mysqli_query($GLOBALS['dbh'],$sql);
				$grp = $newsessionid;
			} else {
				$grp = $chatroom['vidsession'];
			}
		}
		$avchat_token = $apiObj->generateToken($grp);
		$name = "";
		$sql = getUserDetails($userid);
		if ($guestsMode && $userid >= 10000000) {
			$sql = getGuestDetails($userid);
		}
		$result = mysqli_query($GLOBALS['dbh'],$sql);
		if($row = mysqli_fetch_assoc($result)){
			if (function_exists('processName')){
				$row['username'] = processName($row['username']);
			}
			$name = $row['username'];
		}
		$name = urlencode($name);
		echo <<<EOD
		<!DOCTYPE html>
		<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
			<title>{$avchat_language[8]}</title>
			<link href="../../css.php?type=plugin&name=avchat&subtype=opentok" type="text/css" rel="stylesheet" >
			<script src='//static.opentok.com/webrtc/v2.2/js/opentok.min.js'></script>
			<script src="../../js.php?type=core&name=jquery"></script>
			<script src="../../js.php?type=plugin&name=avchat&subtype=opentok&embed={$embed}&callbackfn={$cbfn}" type="text/javascript"></script>
			<script type="text/javascript" charset="utf-8">
				$ = jqcc;
                var basedata = '{$basedata}';
                var apiKey = '{$apiKey}';
				var sessionId = '{$grp}';
				var session = OT.initSession(apiKey, sessionId);
				var token = '{$avchat_token}';
				var invitefunction = "{$invitefunction}";
				session.on({
				  streamCreated: function(event) {
				    session.subscribe(event.stream, 'canvasSub', {insertMode: 'append'});
				  }
				});

				var publisher;
				$(function(){
					publisher = OT.initPublisher('canvasPub', {width: 120, height: 90,insertMode: 'replace'});
					session.connect(token, function(error) {
						  if (error) {
						    console.log(error.message);
						  } else {
						    session.publish(publisher);
						  }
						});

					$('#publishVideo').click(function(e){
						e.preventDefault();
						pub = $('#publishVideo');
						innerHtml = pub.html();
						if(innerHtml == '<img src="res/turnoffvideo.png">'){
							pub.html('<img src="res/turnonvideo.png">');
							publisher.publishVideo(false);
						}else{
							pub.html('<img src="res/turnoffvideo.png">');
							publisher.publishVideo(true);
						}
					});
				});

			</script>
			</head>
			<body>
				<div id="loading"><img src="res/init.png"></div>
				<div id="endcall"><img src="res/ended.png"></div>
				<div id="canvasPub"></div>
				<div id="canvasSub"></div>
				<div id="navigation">
					<div id="navigation_elements">
						<a href="#" onclick="javascript:inviteUser()" id="inviteLink"><img src="res/invite.png"></a>
						<a href="#" onclick="javascript:disconnect()" id="disconnectLink"><img src="res/hangup.png"></a>
						<a href="#" id="publishVideo"><img src="res/turnoffvideo.png"></a>
						<div style="clear:both"></div>
					</div>
					<div style="clear:both"></div>
				</div>
				<script>
				var reSize = function(){
					var otSubscribers = $('#canvasSub > .OT_subscriber');
					if(otSubscribers.length >= 1){
						$('#loading').hide();
					}
					if(otSubscribers.length == 1){
						$('#canvasSub > .OT_subscriber').each(function () {
						    this.style.setProperty( 'width', '100%', 'important' );
						});
					} else if(otSubscribers.length == 2){
						$('#canvasSub > .OT_subscriber').each(function () {
						    this.style.setProperty( 'width', '50%', 'important' );
						});
					} else if(otSubscribers.length > 2 && otSubscribers.length < 5){
						$('#canvasSub > .OT_subscriber').each(function () {
						    this.style.setProperty( 'width', '50%', 'important' );
						    this.style.setProperty( 'height', '50%', 'important' );
						});
					}else if(otSubscribers.length > 4 && otSubscribers.length < 7){
						$('#canvasSub > .OT_subscriber').each(function () {
						    this.style.setProperty( 'width', '33%', 'important' );
						    this.style.setProperty( 'height', '50%', 'important' );
						});
					}else if(otSubscribers.length > 6){
						$('#canvasSub > .OT_subscriber').each(function () {
						    this.style.setProperty( 'width', '33%', 'important' );
						    this.style.setProperty( 'height', '33%', 'important' );
						});
					}
				}
				setInterval(function(){reSize();}, 5000);
				</script>
			</body>
		</html>
EOD;
	}elseif($videoPluginType=='0'){
		$server_name = '';
		$onload = 'endCall(1)';
		if(strpos(BASE_URL,'//')=== false) {
			$server_name = '//'.$_SERVER['SERVER_NAME'];
		}
		$cssurl = $server_name.BASE_URL.'css.php?cc_theme='.$cc_theme;
		$endcall = '<a href="#" onclick="endCall(1)" id="endcall" class="cometchat_statusbutton" style="display: block;text-decoration: none;z-index: 10000;">'.$avchat_language[49].'</a>';
		if(!empty($chatroommode)||CROSS_DOMAIN == 1){
			$onload = 'closeWin()';
			$endcall = '<a href="#" onclick="closeWin()" id="endcall" class="cometchat_statusbutton" style="display: block;text-decoration: none;z-index: 10000;">'.$avchat_language[49].'</a>';
		}
		$v1 = rawurlencode($avchat_language[50]);
		$v0 = rawurlencode($avchat_language[51]);
		$m1 = rawurlencode($avchat_language[52]);
		$m0 = rawurlencode($avchat_language[53]);
		$b1 = rawurlencode($avchat_language[49]);
		$bd = encryptUserid($userid);
		$grp = md5($channelprefix.$grp);
		if( strpos($baseUrl, 'http') !== false ) {
			$hostpath = $baseUrl;
		} else {
			$hostpath = "http://".$_SERVER['SERVER_NAME'].$baseUrl;
		}
		echo <<<EOD
				<!DOCTYPE html>
				<html>
				<head>
					<title>{$avchat_language[8]}</title>
					<style>
						#ie_fix {
					        border: none;
					        position: absolute;
					        top: 0;
					        left: 0;
					        height: 100%;
					        width: 100%;
					        z-index: -1;
					        display: none;
					    }
					    .ie_buttonfix {
						    position: relative;
						    z-index: 2;
						    background-color: rgba(255, 255, 255, 1);
						}
						.ie_iframefix {
						    position: relative;
						    z-index: 1;
						}
					</style>
					<script src="../../js.php?type=core&name=jquery"></script>
					<link href="../../css.php?type=plugin&name=avchat&subtype=webrtc" type="text/css" rel="stylesheet" >
					<link href="../../css.php?cc_theme={$cc_theme}" type="text/css" rel="stylesheet" >
					<script src="../../js.php?type=plugin&name=avchat&subtype=webrtc&embed={$embed}" type="text/javascript"></script>
					<script>
						var isIE = /*@cc_on!@*/false || !!document.documentMode;
						jqcc(document).ready(function(){
							if(isIE) {
								jqcc('#ie_fix').show();
								jqcc("#webrtc").addClass("ie_iframefix");
								jqcc("#avchatButtons").addClass("ie_buttonfix");
							}
						});

						var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnce", "grp":"{$grp}", "value":"0", "chatroommode":"0"}};
                        controlparameters = JSON.stringify(controlparameters);
                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');

                        var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnceWindow", "grp":"{$grp}", "value":"0", "chatroommode":"0"}};
                        controlparameters = JSON.stringify(controlparameters);
                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');

                        function endCall(caller){
                        	if(typeof(parent) === 'undefined' || parent == self){
                            	var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnceWindow", "grp":"{$grp}", "value":"1", "chatroommode":"0"}};
                                    controlparameters = JSON.stringify(controlparameters);
                                    window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');


                                var controlparameters = {"type":"plugins", "name":"ccavchat", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}", "chatroommode":"0"}};
                                controlparameters = JSON.stringify(controlparameters);
                                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');

                                window.close();
                            } else {
                            	var controlparameters = {"type":"plugins", "name":"ccavchat", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}","chatroommode":"0"}};
                                controlparameters = JSON.stringify(controlparameters);
                                parent.postMessage('CC^CONTROL_'+controlparameters,'*');

                                var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnce", "grp":"{$grp}", "value":"1", "chatroommode":"0"}};
                                    controlparameters = JSON.stringify(controlparameters);
                                    parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                                if(caller)
                                var controlparameters = {'type':'plugins', 'name':'audiovideochat', 'method':'closeCCPopup', 'params':{'name':'audiovideochat'}};
                                controlparameters = JSON.stringify(controlparameters);
                                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                            }
                        }
                        function closeWin(){
                            if(typeof(parent) === 'undefined'  || parent == self){
                                var controlparameters = {"type":"plugins", "name":"ccavchat", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}", "chatroommode":"0"}};
                                controlparameters = JSON.stringify(controlparameters);
                                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');

                                window.close();
                            } else {
                                var controlparameters = {"type":"plugins", "name":"ccavchat", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}","chatroommode":"0"}};
                                controlparameters = JSON.stringify(controlparameters);
                                parent.postMessage('CC^CONTROL_'+controlparameters,'*');

                                var controlparameters = {'type':'plugins', 'name':'audiovideochat', 'method':'closeCCPopup', 'params':{'name':'audiovideochat'}};
                                controlparameters = JSON.stringify(controlparameters);
                                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                            }
                        }

                        if (location.protocol === 'http:') {
    						window.location = "https://{$webRTCPHPServer}/index.php?v1={$v1}&v0={$v0}&m1={$m1}&m0={$m0}&b1={$b1}&room={$grp}&to={$to}&basedata={$bd}&pluginname=avchat&hostpath={$hostpath}&cssurl={$cssurl}";
						}
					</script>
				</head>
				<body onunload="{$onload}">
					<iframe id ="webrtc" src="//{$webRTCPHPServer}/?v1={$v1}&v0={$v0}&m1={$m1}&m0={$m0}&room={$grp}&cssurl={$cssurl}" width=100% height=100% seamless allowfullscreen></iframe>
					<div id="avchatButtons">
						{$endcall}
						<iframe id="ie_fix" src="about:blank"></iframe>
					</div>
				</div>
				</body>
				</html>
EOD;

	}
}


function configCheck(){
	global $to;
	$errorFlag = 0;
	if(!empty($to)){
		global $connectUrl,$videoPluginType,$opentokApiSecret,$opentokApiKey,$avchat_language,$webRTCPHPServer,$webRTCServer;
		$error = $avchat_language[47];
		switch($videoPluginType){
			case '2':	if($connectUrl === ''){
							$errorFlag = 1;
						}
						break;
			case '3':	if($opentokApiSecret === '' || $opentokApiKey === '' || !file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'OpenTok'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')){
							$errorFlag = 1;
						}
						break;
			case '0':	if($webRTCPHPServer === ''){
							$errorFlag = 1;
						}
						break;
		}
		if($errorFlag){
			sendMessage($to,$error,2);
			exit;
		}
	}
}