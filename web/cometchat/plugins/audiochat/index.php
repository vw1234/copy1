<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

if ($p_<4) exit;

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang.php");
}
$webrtcTheme = $theme;
if (!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."layouts".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."audiochat".$rtl.".css")) {
	$theme = "docked";
}

$basedata = $to = $grp  = $action = $chatroommode = $embed = null;

if(!empty($_REQUEST['basedata'])) {
	$basedata = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['basedata']);
}
if(!empty($_REQUEST['to'])){
	$to = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['to']);
}

if(!empty($_REQUEST['grp'])){
	$grp = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['grp']);
}
if(!empty($_REQUEST['action'])){
	$action = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['action']);
}
if(!empty($_REQUEST['chatroommode'])){
	$chatroommode = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['chatroommode']);
}
if(!empty($_REQUEST['embed'])){
	$embed = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['embed']);
}
$cbfn = '';
if(!empty($_REQUEST['callbackfn'])){
	$cbfn = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['callbackfn']);
	$_SESSION['noguestmode'] = '1';
}
$cc_theme = '';
if(!empty($_REQUEST['cc_theme'])){
	$cc_theme = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['cc_theme']);
}

if($action == 'endcall') {
	if (!empty($chatroommode)) {
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'endcall', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'endcall', 'params' => array('grp' => $grp, 'chatroommode' => 0));
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
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'rejectcall', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'rejectcall', 'params' => array('grp' => $grp, 'chatroommode' => 0));
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
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'noanswer', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'noanswer', 'params' => array('grp' => $grp, 'chatroommode' => 0));
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
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'canceloutgoingcall', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'canceloutgoingcall', 'params' => array('grp' => $grp, 'chatroommode' => 0));
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
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'busycall', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'audiochat', 'method' => 'busycall', 'params' => array('grp' => $grp, 'chatroommode' => 0));
		$controlparameters = json_encode($controlparameters);
		sendMessage($to, 'CC^CONTROL_'.$controlparameters,1);
	}
	if (!empty($_GET['callback'])) {
		echo $_GET['callback'].'('.json_encode(1).')';
	} else {
		echo json_encode(1);
	}
}

if ($action == 'request') {
	$audiochat_token = '';
	$caller = '';
	if(!empty($_REQUEST['caller'])){
		$caller = $_REQUEST['caller'];
	}
	if(empty($grp)){
		$grp = $userid<$to? md5($userid).md5($to) : md5($to).md5($userid);
		$grp = md5($_SERVER['HTTP_HOST'].'audiochat'.$grp);
	}
	if(isset($chatroommode)){
		sendChatroomMessage($to, $audiochat_language[19]." <a token ='".$audiochat_token."' href='javascript:void(0);' onclick=\"javascript:jqcc.ccaudiochat.join('".$grp."');\">".$audiochat_language[20]."</a> ",0);
		if (!empty($_REQUEST['callback'])) {
			header('content-type: application/json; charset=utf-8');
			echo $_REQUEST['callback'].'("'.$grp.'")';
		} else {
			echo json_encode($grp);
		}
		exit;
	}else{
		$optionalmessage = 0;

		if(function_exists('hooks_sendOptionalMessage')){
			$optionalmessage = hooks_sendOptionalMessage(array('to' => $to, 'plugin' => 'audio chat'));
		}
		
		if($optionalmessage == 0){
			$response = sendMessage($to,$audiochat_language[2]." <a class='audiochat_link_".$grp." acceptAudioChat' token ='".$audiochat_token."' mobileAction=\"javascript:jqcc.ccaudiochat.accept('".$userid."','".$grp."');\" href='javascript:void(0);' to='".$userid."' caller='".$caller."' grp='".$grp."' >".$audiochat_language[3]."</a> ".$audiochat_language[45]."<a href='javascript:void(0);' class='audiochat_link_".$grp."' onclick=\"javascript:jqcc.ccaudiochat.reject_call('".$userid."','".$grp."');\">".$audiochat_language[43].".</a>".$audiochat_language[46],1);
			pushMobileNotification($to,$response['id'],$grp."_#wrtcgrp_".$_SESSION['cometchat']['user']['n'].": ".$audiochat_language[2],'0','AC',getTimeStamp());
			incrementCallback();
			$_REQUEST['callback'];
			sendMessage($to,$audiochat_language[5]." ".$audiochat_language[44]."<a href='javascript:void(0);' class='audiochat_link_".$grp."' onclick=\"javascript:jqcc.ccaudiochat.cancel_call('".$to."','".$grp."');\">".$audiochat_language[43].".</a>",2);
			decrementCallback();
		}
	}
	if (!empty($_REQUEST['callback'])) {
		header('content-type: application/json; charset=utf-8');
		echo json_encode(1);
	} else {
		echo json_encode(1);
	}
	exit;
}
if ($action == 'accept') {
	incrementCallback();
	$_REQUEST['callback'];
	$caller = '';
	if(!empty($_REQUEST['caller'])){
		$caller = $_REQUEST['caller'];
	}
	sendMessage($to,$audiochat_language[6]." <a href='javascript:void(0);' class='audiochat_link_".$grp." accept_fid' mobileAction=\"javascript:jqcc.ccaudiochat.accept_fid('".$userid."','".$grp."');\" to='".$userid."' caller='".$caller."' grp='".$grp."' >".$audiochat_language[7]."</a>",1);
	decrementCallback();
	exit;
}
if ($action == 'call') {
	$baseUrl = BASE_URL;
	$embed = '';
	$embedcss = '';
	$onload = 'endCall(1)';
	$resize = 'window.resizeTo(';
	$invitefunction = 'window.open';
	if (!empty($embed) && $embed == 'web') {
		$embed = 'web';
		$resize = "parent.resizeCCPopup('audiochat',";
		$embedcss = 'embed';
		$invitefunction = 'parent.loadCCPopup';
	}
	if (!empty($embed) && $embed == 'desktop') {
		$embed = 'desktop';
		$resize = "parentSandboxBridge.resizeCCPopupWindow('audiochat',";
		$embedcss = 'embed';
		$invitefunction = 'parentSandboxBridge.loadCCPopupWindow';
	}
	if(CROSS_DOMAIN == 1){
		$cssurl=BASE_URL.'css.php?cc_theme='.$cc_theme;
	}else{
		$cssurl='//'.$_SERVER['SERVER_NAME'].BASE_URL.'css.php?cc_theme='.$cc_theme;
	}
	$endcall = '<a href="#" onclick="endCall(1)" id="endcall" class="cometchat_statusbutton" style="display: block;text-decoration: none;z-index: 10000;">'.$audiochat_language[49].'</a>';
	if(!empty($chatroommode) || CROSS_DOMAIN == 1){
      	$onload = 'closeWin()';
		$endcall = '<a href="#" onclick="closeWin()" id="endcall" class="cometchat_statusbutton" style="display: block;text-decoration: none;z-index: 10000;">'.$audiochat_language[49].'</a>';
	}
	$m1=rawurlencode($audiochat_language[50]);
	$m0=rawurlencode($audiochat_language[51]);
	$b1=rawurlencode($audiochat_language[49]);
	$bd = encryptUserid($userid);
	if( strpos($baseUrl, 'http') !== false ) {
		$hostpath = $baseUrl;
	} else {
		$hostpath = "http://".$_SERVER['SERVER_NAME'].$baseUrl;
	}
	if(!isset($chatroommode)){
		$grp = md5($channelprefix.$grp);
	}

	echo <<<EOD
	<!DOCTYPE html>
	<html>
		<head>
			<title>{$audiochat_language[8]}</title>
			<link href="../../css.php?cc_theme={$cc_theme}" type="text/css" rel="stylesheet" >
			<link href="../../css.php?type=plugin&name=audiochat&subtype=webrtc" type="text/css" rel="stylesheet" >
			<script>
				var isIE = /*@cc_on!@*/false || !!document.documentMode;
				/*jqcc(document).ready(function(){
					if(isIE) {
						jqcc('#ie_fix').show();
						jqcc("#webrtc").addClass("ie_iframefix");
						jqcc("#audiochatButtons").addClass("ie_buttonfix");
					}
				});*/
			var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnce", "grp":"{$grp}", "value":"0"}};
            controlparameters = JSON.stringify(controlparameters);
            parent.postMessage('CC^CONTROL_'+controlparameters,'*');

            var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnceWindow", "grp":"{$grp}", "value":"0"}};
            controlparameters = JSON.stringify(controlparameters);
            parent.postMessage('CC^CONTROL_'+controlparameters,'*');

			function endCall(caller){
				if(typeof(parent) === 'undefined' || parent == self){
					var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnceWindow", "grp":"{$grp}", "value":"1"}};
                    controlparameters = JSON.stringify(controlparameters);
                    window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');

                    var controlparameters = {"type":"plugins", "name":"ccaudiochat", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}", "chatroommode":"0"}};
                    controlparameters = JSON.stringify(controlparameters);
                    window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');

					window.close();
				} else {
					var controlparameters = {"type":"plugins", "name":"ccaudiochat", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}","chatroommode":"0"}};
                    controlparameters = JSON.stringify(controlparameters);
                    parent.postMessage('CC^CONTROL_'+controlparameters,'*');

                    var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnce", "grp":"{$grp}", "value":"1"}};
                        controlparameters = JSON.stringify(controlparameters);
                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');
					if(caller)
						var controlparameters = {'type':'plugins', 'name':'audiochat', 'method':'closeCCPopup', 'params':{'name':'audiochat'}};
                        controlparameters = JSON.stringify(controlparameters);
                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');
				}
			}
			function closeWin(){
				if(typeof(parent) === 'undefined' || parent == self){
					window.close();
				} else {
					var controlparameters = {'type':'plugins', 'name':'audiochat', 'method':'closeCCPopup', 'params':{'name':'audiochat'}};
                    controlparameters = JSON.stringify(controlparameters);
                    parent.postMessage('CC^CONTROL_'+controlparameters,'*');
				}
			}
			if (location.protocol === 'http:') {
    			window.location = "https://{$webRTCPHPServer}/index.php?audioOnly=1&m1={$m1}&m0={$m0}&b1={$b1}&room={$grp}&hostpath={$hostpath}&basedata={$bd}&to={$to}&pluginname=audiochat&cssurl={$cssurl}";
			}
			</script>
		</head>
		<body onunload="{$onload}">
			<iframe id ="webrtc" src="//{$webRTCPHPServer}/?audioOnly=1&m1={$m1}&m0={$m0}&room={$grp}&cssurl={$cssurl}" width=100% height=100% seamless allowfullscreen></iframe>
			<div id="audiochatButtons">
				{$endcall}
				<iframe id="ie_fix" src="about:blank"></iframe>
			</div>
		</div>
		</body>
	</html>
EOD;
}
