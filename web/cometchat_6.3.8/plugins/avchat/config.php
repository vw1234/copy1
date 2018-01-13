<?php

////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SETTINGS START */

$videoPluginType = setConfigValue('videoPluginType','0');

/* SETTINGS END */

$vidWidth = '220';
$vidHeight = '165';
$maxP = '10';
$quality = '90';
$winWidth = '650';
$winHeight = '365';
$connectUrl = '';
$camWidth = '440';
$camHeight = '330';
$fps = '30';
$soundQuality = '7';
$email = 'email';

/* videoPluginType Codes
0. CometChat Servers (WebRTC)
1. Self-hosted WebRTC
*/

$webRTCServer = 'r.chatforyoursite.com';
$webRTCPHPServer = 's.chatforyoursite.com';
if ($videoPluginType == '1') {
	$temp = parse_url(CS_TEXTCHAT_SERVER);
	$webRTCServer = $temp['host'];
	$webRTCPHPServer = $_SERVER['HTTP_HOST'].BASE_URL."transports/cometservice-selfhosted";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
