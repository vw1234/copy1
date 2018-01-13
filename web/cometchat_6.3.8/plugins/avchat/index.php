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
$webrtcTheme = $theme;
if (!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."avchat".$rtl.".css")) {
	$theme = "docked";
}

if ($p_<4) exit;

if ($videoPluginType == '1') {
	$videoPluginType = '0';
}

$basedata = $to = $grp  = $action = $chatroommode = $embed = $id = null;

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
if(!empty($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
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

if ($action == 'request') {
	$caller = '';
	if(!empty($_REQUEST['caller'])){
		$caller = $_REQUEST['caller'];
	}
	$avchat_token = '';
	if(empty($grp)){
		$grp = $userid<$to? md5($userid).md5($to) : md5($to).md5($userid);
		$grp = md5('avchat'.$grp);
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
			sendMessage($to,$avchat_language[5]." ".$avchat_language[44]."<a href='javascript:void(0);' class='avchat_link_".$grp."' onclick=\"javascript:jqcc.ccavchat.cancel_call('".$to."','".$grp."');\">".$avchat_language[43].".</a>",2);
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
	if($videoPluginType=='0'){
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
    						window.location = "https://{$webRTCPHPServer}/?v1={$v1}&v0={$v0}&m1={$m1}&m0={$m0}&b1={$b1}&room={$grp}&to={$to}&basedata={$bd}&pluginname=avchat&hostpath={$hostpath}&cssurl={$cssurl}";
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
		global $connectUrl,$videoPluginType,$avchat_language,$webRTCPHPServer,$webRTCServer;
		$error = $avchat_language[47];
		switch($videoPluginType){
			case '0':
			if($webRTCPHPServer === ''){
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
