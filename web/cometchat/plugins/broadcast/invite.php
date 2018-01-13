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

$embed = '';
$close = "setTimeout('window.close()',2000);";
$basedata=0;
if(!empty($_REQUEST['basedata'])){
	$basedata = $_REQUEST['basedata'];
}
$cc_theme = '';
if(!empty($_REQUEST['cc_theme'])){
	$cc_theme = $_REQUEST['cc_theme'];
}
function invite() {
	global $userid, $broadcast_language, $language, $embed, $embedcss, $lightboxWindows, $guestsMode, $cookiePrefix, $chromeReorderFix, $hideOffline, $basedata, $plugins, $cc_theme;

	if($lightboxWindows == '1') {
		$embed = 'web';
		$embedcss = 'embed';
	}

	$status['available'] = $language[30];
	$status['busy'] = $language[31];
	$status['offline'] = $language[32];
	$status['invisible'] = $language[33];
	$status['away'] = $language[34];

	$id = $_REQUEST['grp'];

	if (empty($id)) { exit; }

	$time = getTimeStamp();

	$onlineCacheKey = 'all_online';
	if($userid > 10000000){
		$onlineCacheKey .= 'guest';
	}

	if (!is_array($buddyList = getCache($onlineCacheKey))) {
		$buddyList = array();
		$sql = getFriendsList($userid,$time);
		if($guestsMode){
	    	$sql = getGuestsList($userid,$time,$sql);
		}
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		while ($chat = mysqli_fetch_assoc($query)) {
			if (((($time-processTime($chat['lastactivity'])) < ONLINE_TIMEOUT) && $chat['status'] != 'invisible' && $chat['status'] != 'offline') || $chat['isdevice'] == 1) {
				if ($chat['status'] != 'busy' && $chat['status'] != 'away') {
					$chat['status'] = 'available';
				}
			} else {
				$chat['status'] = 'offline';
			}

			$avatar = getAvatar($chat['avatar']);

			if (!empty($chat['username'])) {
				if (function_exists('processName')) {
					$chat['username'] = processName($chat['username']);
				}
				if ($chat['userid'] != $userid && ($hideOffline == 0||($hideOffline == 1 && $chat['status']!='offline'))) {
					$buddyList[$chromeReorderFix.$chat['userid']] = array('id' => $chat['userid'], 'n' => $chat['username'], 'a' => $avatar, 's' => $chat['status']);
				}
			}
		}
	}

	if (DISPLAY_ALL_USERS == 0 && MEMCACHE <> 0 && USE_CCAUTH == 0) {
		$tempBuddyList = array();
		if (!is_array($friendIds = getCache('friend_ids_of_'.$userid))) {
			$friendIds=array();
			$sql = getFriendsIds($userid);
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if(mysqli_num_rows($query) == 1 ){
				$buddy = mysqli_fetch_assoc($query);
				$friendIds = explode(',',$buddy['friendid']);
			}else{
				while($buddy = mysqli_fetch_assoc($query)){
					$friendIds[]=$buddy['friendid'];
				}
			}
			setCache('friend_ids_of_'.$userid,$friendIds, 30);
		}
		foreach($friendIds as $friendId) {
			$friendId = $chromeReorderFix.$friendId;
			if (!empty($buddyList[$friendId])) {
				$tempBuddyList[$friendId] = $buddyList[$friendId];
			}
		}
		$buddyList = $tempBuddyList;
	}

	if (function_exists('hooks_forcefriends') && is_array(hooks_forcefriends())) {
		$buddyList = array_merge(hooks_forcefriends(),$buddyList);
	}

	$blockList = array();
	if (in_array('block',$plugins)) {
		$blockedIds = getBlockedUserIDs();
		foreach ($blockedIds as $bid) {
			array_push($blockList,$bid);
			if (isset($buddyList[$chromeReorderFix.$bid])) {
				unset($buddyList[$chromeReorderFix.$bid]);
			}
		}
	}

	if (isset($buddyList[$chromeReorderFix.$userid])) {
		unset($buddyList[$chromeReorderFix.$userid]);
	}

	$s['available'] = '';
	$s['away'] = '';
	$s['busy'] = '';
	$s['offline'] = '';

	foreach ($buddyList as $buddy) {
		$s[$buddy['s']] .= '<div class="invite_1"><div class="invite_2" onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;"><img height=30 width=30 src="'.$buddy['a'].'"></div><div class="invite_3" onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;">'.$buddy['n'].'<br/><span class="invite_5">'.$status[$buddy['s']].'</span></div><input type="checkbox" name="invite[]" value="'.$buddy['id'].'" id="check_'.$buddy['id'].'" class="invite_4"></div>';
	}

	$inviteContent = '';
	$invitehide = '';
	$inviteContent = $s['available']."".$s['away']."".$s['offline'];
	if(empty($inviteContent)) {
		$inviteContent = $broadcast_language[18];
		$invitehide = 'style="display:none;"';
	}

	echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$broadcast_language[13]}</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=broadcast&cc_theme={$cc_theme}" />
		<script src="../../js.php?type=core&name=jquery" type="text/javascript"></script>
		<script type="text/javascript">
			$ = jQuery = jqcc;
			$(function(){
				$('.invite_1').click(function() {
					var checked = $("input:checked").length;
					if(checked > 0){
						$('.invitebutton').attr("disabled", false);
					}else{
						$('.invitebutton').attr("disabled", true);
					}
				});
			});
		</script>
	</head>
	<body>
		<form method="post" action="invite.php?action=inviteusers&basedata={$basedata}&embed={$embed}">
			<div class="cometchat_wrapper">
				<div class="container_title {$embedcss}">{$broadcast_language[11]}</div>
				<div class="container_body {$embedcss}">
					{$inviteContent}
					<div style="clear:both"></div>
				</div>
				<div class="container_sub" {$invitehide}>
					<input type=submit value="{$broadcast_language[12]}" class="invitebutton" disabled />
				</div>
			</div>
			<input type="hidden" name="grp" value="$id" />
		</form>
	</body>
</html>
EOD;

}

function inviteusers() {
	global $broadcast_language;
	global $userid;
	global $close;
	global $embedcss;
	global $lightboxWindows;
	global $cc_theme;

	if($lightboxWindows == '1') {
		$embedcss = 'embed';
	}

	foreach ($_REQUEST['invite'] as $user) {
		$response = sendMessage($user,"{$broadcast_language[14]} <a class=\"broadcastInvite\" to=\"{$userid}\" grp=\"{$_REQUEST['grp']}\" href='javascript:void(0);' >{$broadcast_language[15]}</a>",1);
	}

	echo <<<EOD
	<!DOCTYPE html>
	<html>
	<head>
	<title>{$broadcast_language[13]}</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=broadcast&cc_theme={$cc_theme}" />
	</head>
	<body onload="{$close}">
	<div class="cometchat_wrapper">
	<div class="container_title {$embedcss}">{$broadcast_language[11]}</div>
	<div class="container_body {$embedcss}">
	{$broadcast_language[13]}</span>
	<div style="clear:both"></div>
	</div>
	</div>
	</body>
	</html>
EOD;

}

$action = 'invite';
$allowedActions = array('invite','inviteusers');

if (!empty($_GET['action']) && function_exists($_GET['action']) && in_array($_GET['action'],$allowedActions)) {
	$action = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['action']);
}
call_user_func($action);
