<?php

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SETTINGS START */

$screensharePluginType = setConfigValue('screensharePluginType','0');

/* SETTINGS END */

$hostAddress = '';
$port = '1935';
$application = 'screenshare';
$scrWidth = '640';
$scrHeight = '480';

/* videoPluginType Codes
0. CometChat Servers (WebRTC)
1. Self-hosted WebRTC
*/

$webRTCServer = 'r.chatforyoursite.com';
$webRTCPHPServer = 's.chatforyoursite.com';

if ($screensharePluginType == '0') {
	$scrWidth = '640';
	$scrHeight = '480';
}

if ($screensharePluginType == '1') {
	$temp = parse_url(CS_TEXTCHAT_SERVER);
	$webRTCServer = $temp['host'];
	$webRTCPHPServer = $_SERVER['HTTP_HOST'].BASE_URL."transports/cometservice-selfhosted";
}
