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

if (!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$color.DIRECTORY_SEPARATOR."broadcast".$rtl.".css")) {
	$color = "color1";
}

if ($videoPluginType == 1) {
	$videoPluginType = 0;
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

$broadcast = 0;
if(!empty($_REQUEST['broadcast'])){
	$broadcast = 1;
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

$caller = '';
if(!empty($_REQUEST['caller'])){
	$caller = $_REQUEST['caller'];
}

if($action == 'endcall') {
	if (!empty($chatroommode)) {
		$controlparameters = array('type' => 'plugins', 'name' => 'broadcast', 'method' => 'endcall', 'params' => array('grp' => $grp, 'chatroommode' => 1));
		$controlparameters = json_encode($controlparameters);
		sendChatroomMessage($to, 'CC^CONTROL_'.$controlparameters,0);
	} else {
		$controlparameters = array('type' => 'plugins', 'name' => 'broadcast', 'method' => 'endcall', 'params' => array('grp' => $grp, 'chatroommode' => 0));
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
	exit;
}

if ($p_<4) exit;

if ($action == 'request') {
	$grp = $userid<$to? md5($userid).md5($to) : md5($to).md5($userid);
	$grp = md5($_SERVER['HTTP_HOST'].'broadcast'.$grp);

	$response = sendMessage($to,$broadcast_language[2]." <a href='javascript:void(0);' class='broadcastAccept' to='".$userid."' grp='".$grp."' mobileAction=\"javascript:jqcc.ccbroadcast.accept('".$userid."','".$grp."');\">".$broadcast_language[3]."</a> ".$broadcast_language[4],1);

	$processedMessage = $_SESSION['cometchat']['user']['n'].": ".$broadcast_language[2];
   	pushMobileNotification($to,$response['id'],$processedMessage);


	incrementCallback();
	sendMessage($to,$broadcast_language[5],2);
	decrementCallback();

	if (!empty($_REQUEST['callback'])) {
		header('content-type: application/json; charset=utf-8');
		echo $_REQUEST['callback'].'('.$grp.')';
		exit;
	}
}

if ($action == 'call' ) {
	$grp = mysqli_real_escape_string($GLOBALS['dbh'],$grp);
	if ($chatroommode == 1 &&  $_REQUEST['broadcast'] == 0) {
		$grp = md5($_SERVER['HTTP_HOST'].'broadcast'.md5($to.'group'));
	}
}

if($videoPluginType == '0'){
	if (!empty($chatroommode)) {
		if (empty($_REQUEST['join'])) {
			$res = sendChatroomMessage($to,$broadcast_language[17]." <a href='javascript:void(0);' grp='".$grp."' class='join_Broadcast' mobileAction=\"javascript:jqcc.ccbroadcast.join('".$grp."');\">".$broadcast_language[16]."</a>",0);
		}
		if (!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn']=='mobileapp') {
			header('content-type: application/json; charset=utf-8');
			echo $_REQUEST['callback'].'('.$grp.')';
			exit;
		}
	}

	$baseUrl = BASE_URL;
	$embed = '';
	$embedcss = '';
	$resize = 'window.resizeTo(';
	$invitefunction = 'window.open';

	if (!empty($embed) && $embed == 'web') {
		$embed = 'web';
		$resize = "parent.resizeCCPopup('broadcast',";
		$embedcss = 'embed';
		$invitefunction = 'parent.loadCCPopup';
	}

	if (!empty($embed) && $embed == 'desktop') {
		$embed = 'desktop';
		$resize = "parentSandboxBridge.resizeCCPopupWindow('broadcast',";
		$embedcss = 'embed';
		$invitefunction = 'parentSandboxBridge.loadCCPopupWindow';
	}

	$server_name = '';
	$onload = 'endCall(1)';
	if(strpos(BASE_URL,'//')=== false) {
		$server_name = '//'.$_SERVER['SERVER_NAME'];
	}
	if(CROSS_DOMAIN==1){
		$cssurl = BASE_URL.'css.php?cc_theme='.$cc_theme;
	}else{
		$cssurl = $server_name.BASE_URL.'css.php?cc_theme='.$cc_theme;
	}
	$endcall = '<a href="#" onclick="endCall(1)" id="endcall" class="cometchat_statusbutton" style="display: block;text-decoration: none;z-index: 10000;">'.$broadcast_language[19].'</a>';
	if(!empty($chatroommode)||CROSS_DOMAIN == 1){
		if(empty($chatroommode)) {
			$chatroommode = 0;
		}
		$onload = 'closeWin('.$chatroommode.',1)';
		$endcall = '<a href="#" onclick="closeWin('.$chatroommode.',0)" id="endcall" class="cometchat_statusbutton" style="display: block;text-decoration: none;z-index: 10000;">'.$broadcast_language[19].'</a>';
	}
	$invitecall = '';
	if(!$broadcast){
		$invitecall = '<a href="#" id="broadcastInvite" class="cometchat_statusbutton" caller="'.$caller.'" style="display: block;text-decoration: none;z-index: 10000;">'.$broadcast_language[12].'</a>';
	}
	$v1 = rawurlencode($broadcast_language[20]);
	$v0 = rawurlencode($broadcast_language[21]);
	$m1 = rawurlencode($broadcast_language[22]);
	$m0 = rawurlencode($broadcast_language[23]);
	$b1 = rawurlencode($broadcast_language[19]);
	$b2 = rawurlencode($broadcast_language[12]);
	if( strpos($baseUrl, 'http') !== false ) {
		$hostpath = $baseUrl;
	} else {
		$hostpath = "http://".$_SERVER['SERVER_NAME'].$baseUrl;
	}
	$bd = encryptUserid($userid);
	$grpunencrypted = $grp;
	$grp = md5($channelprefix.$grp);
	echo <<<EOD
		<!DOCTYPE html>
		<html>
			<head>
				<title>{$broadcast_language[8]}</title>
				<script src="../../js.php?type=core&name=jquery"></script>
				<script>
					$ = jQuery = jqcc;
				</script>
				<link href="../../css.php?type=plugin&name=broadcast&subtype=webrtc" type="text/css" rel="stylesheet" >
				<link href="../../css.php?cc_theme={$cc_theme}" type="text/css" rel="stylesheet" >
				<script src="../../js.php?type=plugin&name=broadcast&subtype=webrtc&embed={$embed}" type="text/javascript"></script>
				<script>
					var basedata = '{$basedata}';
					var sessionId = '{$grpunencrypted}';
					var invitefunction = "{$invitefunction}";
					var baseUrl = '{$baseUrl}';
					var caller = '{$caller}';
					var isIE = /*@cc_on!@*/false || !!document.documentMode;
					jqcc(document).ready(function(){
						jqcc('#broadcastInvite').on('click',function(){
							var controlparameters = {"type":"plugins", "name":"ccbroadcast", "method":"inviteBroadcast", "params":{"id":sessionId, "caller":caller}};
				            controlparameters = JSON.stringify(controlparameters);
				            if(typeof(parent) != 'undefined' && parent != null && parent != self){
				                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
				            } else {
				                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
				            }
					    });

						if(isIE) {
							jqcc('#ie_fix').show();
							jqcc("#webrtc").addClass("ie_iframefix");
							jqcc("#broadButtons").addClass("ie_buttonfix");
						}
					});

					if(window.top == window.self){
						var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnce", "grp":"{$grp}", "value":"0"}};
                        controlparameters = JSON.stringify(controlparameters);
                        window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');

                        var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnceWindow", "grp":"{$grp}", "value":"0"}};
                        controlparameters = JSON.stringify(controlparameters);
                        window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
					}else{
						var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnce", "grp":"{$grp}", "value":"0"}};
                        controlparameters = JSON.stringify(controlparameters);
                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');

                        var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnceWindow", "grp":"{$grp}", "value":"0"}};
                        controlparameters = JSON.stringify(controlparameters);
                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');
					}
					function endCall(caller){
						if(window.top == window.self){
							var controlparameters = {"type":"plugins", "name":"ccbroadcast", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}","chatroommode":"0"}};
                            controlparameters = JSON.stringify(controlparameters);
                            window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');

							var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnceWindow", "grp":"{$grp}", "value":"1"}};
                            controlparameters = JSON.stringify(controlparameters);
                            window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');

							window.close();
						} else {
							var controlparameters = {"type":"plugins", "name":"ccbroadcast", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}","chatroommode":"0"}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');

							var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":"endcallOnce", "grp":"{$grp}", "value":"1"}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
							if(caller)
							var controlparameters = {'type':'plugins', 'name':'broadcast', 'method':'closeCCPopup', 'params':{'name':'broadcast'}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
						}
					}
					function closeWin(chatroommode,Endcall){
						if(window.top == window.self){
							if({$broadcast} == 0 && Endcall == 1){
								var controlparameters = {"type":"plugins", "name":"ccbroadcast", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}","chatroommode":chatroommode}};
                                controlparameters = JSON.stringify(controlparameters);
                                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
                        	}
							window.close();
						} else {
							if({$broadcast} == 0 && Endcall == 1){
								var controlparameters = {"type":"plugins", "name":"ccbroadcast", "method":"end_call", "params":{"to":"{$to}", "grp":"{$grp}","chatroommode":chatroommode}};
                                controlparameters = JSON.stringify(controlparameters);
                                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                            }
							var controlparameters = {'type':'plugins', 'name':'broadcast', 'method':'closeCCPopup', 'params':{'name':'broadcast'}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
						}
					}
                    if (location.protocol === 'http:') {
						window.location = "https://{$webRTCPHPServer}/index.php?v1={$v1}&v0={$v0}&m1={$m1}&m0={$m0}&b1={$b1}&b2={$b2}&broadcast={$broadcast}&room={$grp}&hostpath={$hostpath}&basedata={$bd}&to={$to}&crmode={$chatroommode}&pluginname=broadcast&caller={$caller}&cssurl={$cssurl}";
					}
				</script>
			</head>
			<body onunload="{$onload}">
				<iframe id ="webrtc" src="//{$webRTCPHPServer}/index.php?v1={$v1}&v0={$v0}&m1={$m1}&m0={$m0}&broadcast={$broadcast}&room={$grp}&cssurl={$cssurl}" width=100% height=100% seamless allowfullscreen></iframe>
				<div id="broadButtons">
					{$endcall}
					{$invitecall}
					<iframe id="ie_fix" src="about:blank"></iframe>
				</div>
			</div>
			</body>
		</html>
EOD;
	}else {

	if (!empty($chatroommode)) {
		$grporg = $grp ;
		$sql = ("select vidsession from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$grp)."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		$chatroom = mysqli_fetch_assoc($query);

		if (empty($chatroom['vidsession'])) {
			if(!empty($_SERVER['REMOTE_ADDR'])){
				$location = $_SERVER['REMOTE_ADDR'];
			}
			try {
				$session = $apiObj->createSession(array( 'location' => $location ));
				$newsessionid = $session->getSessionId();
			} catch (Exception $e) {
				echo "<div style='background:white;padding:15px;'>Please ask your administrator to configure this plugin using administration panel.</div>";
				exit;
			}
			$sql = ("update cometchat_chatrooms set  vidsession = '".mysqli_real_escape_string($GLOBALS['dbh'],$newsessionid)."' where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$grp)."'");
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			$grp = $newsessionid;
		} else {
			$grp = $chatroom['vidsession'];
		}

		if (empty($_REQUEST['join'])) {
			sendChatroomMessage($grporg,$broadcast_language[9]." <a href='javascript:void(0);' onclick=\"javascript:jqcc.ccbroadcast.join('".$grporg."');\">".$broadcast_language[10]."</a>",0);
		}

		$avchat_token = $apiObj->generateToken($grp);
	}

	$baseUrl = BASE_URL;
	$embed = '';
	$embedcss = '';
	$resize = 'window.resizeTo(';
	$invitefunction = 'window.open';

	if (!empty($embed) && $embed == 'web') {
		$embed = 'web';
		$resize = "parent.resizeCCPopup('broadcast',";
		$embedcss = 'embed';
		$invitefunction = 'parent.loadCCPopup';
	}
	if (!empty($embed) && $embed == 'desktop') {
		$embed = 'desktop';
		$resize = "parentSandboxBridge.resizeCCPopupWindow('broadcast',";
		$embedcss = 'embed';
		$invitefunction = 'parentSandboxBridge.loadCCPopupWindow';
	}

	$publish = "";
	$control = "";

	if ($_REQUEST['type'] == 1) {
		$publish = "publisher = OT.initPublisher('canvasPub', {width: $vidWidth, height: $vidHeight-20,insertMode: 'replace'});session.publish(publisher);";		//In $vidHeight-20, 20 is the height of video controls bar
		$control = '<div id="navigation" style="display:block">
			<div id="navigation_elements">
				<a href="#" onclick="javascript:inviteUser()" id="inviteLink"><img src="res/invite.png"></a>
				<a href="#" id="publishVideo"><img src="res/turnoffvideo.png"></a>
				<div style="clear:both"></div>
			</div>
			<div style="clear:both"></div>
		</div>';
	}

	echo <<<EOD
	<!DOCTYPE html>
	<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>{$broadcast_language[8]}</title>
                <style>
                	html,body{
                		width:100%;
                		height:100%;
    					background: #000 !important;
                	}
                </style>
				<script src="../../js.php?type=core&name=jquery"></script>
				<script>
					$ = jQuery = jqcc;
				</script>
				<script type="text/javascript" charset="utf-8">
                    var basedata = '{$basedata}';
                    var apiKey = '{$apiKey}';
					var sessionId = '{$grp}';
					var session = OT.initSession(apiKey, sessionId);
					var token = '{$avchat_token}';
					var invitefunction = "{$invitefunction}";
					session.on({
					  streamCreated: function(event) {
					    session.subscribe(event.stream, 'canvasSub', {insertMode: 'append',width: $vidWidth, height: $vidHeight});
					  }
					});

					var publisher;
					$(function(){
						session.connect(token, function(error) {
							  if (error) {
							    console.log(error.message);
							  } else {
							    {$publish}
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
			<div id="canvasPub"></div>
			<div id="canvasSub"></div>
			{$control}
		</body>
		<script>
		var reSize = function(){
			var otSubscribers = $('#canvasSub > .OT_subscriber');
			if(otSubscribers.length == 1){
				$('#canvasSub').css( 'width', '100%' );
				$('#canvasSub').css( 'height', '100%' );
				$('#canvasSub > .OT_subscriber').each(function () {
				    this.style.setProperty( 'width', '100%', 'important' );
			    	this.style.setProperty( 'height', '100%', 'important' );
				});

			} else{
			    $('#canvasPub').css( 'width', '100%' );
			    $('#canvasPub').css( 'height', '95%' );
			}
		}
		window.onresize = reSize;
		</script>
	</html>
EOD;
}
