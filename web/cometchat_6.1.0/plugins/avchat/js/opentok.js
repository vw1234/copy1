<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.php");

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

foreach ($avchat_language as $i => $l) {
	$avchat_language[$i] = str_replace("'", "\'", $l);
}

?>
var vidWidth = <?php echo $vidWidth;?>; var vidHeight = <?php echo $vidHeight;?>; var baseUrl = "<?php echo BASE_URL;?>"; var session; var publisher; var subscribers = {}; var totalStreams = 0;var camWidth = <?php echo $camWidth;?>; var camHeight = <?php echo $camHeight;?>; var newheight = 0;



function inviteUser() {
	eval(invitefunction + '("' + baseUrl + 'plugins/avchat/invite.php?action=invite&roomid='+ sessionId +'&basedata='+ basedata +'","invite","status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=400,height=190",400,190,"<?php echo $avchat_language[16];?>",0);');
}

function show(id) {
	document.getElementById(id).style.display = 'block';
}

function hide(id) {
	document.getElementById(id).style.display = 'none';
}

function disconnect() {
	session.disconnect();
	hide('navigation');
	show('endcall');
}