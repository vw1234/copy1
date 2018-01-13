<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ADVANCED */
$cms = "standalone";
define('SET_SESSION_NAME','');			// Session name
define('SWITCH_ENABLED','1');
define('INCLUDE_JQUERY','1');
define('FORCE_MAGIC_QUOTES','0');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* DATABASE */
// DO NOT EDIT DATABASE VALUES BELOW
// DO NOT EDIT DATABASE VALUES BELOW
// DO NOT EDIT DATABASE VALUES BELOW

define('DB_SERVER',			"localhost"				); // Database host
define('DB_PORT',			"3306"					); // Database port
define('DB_USERNAME',		"symfony"					); // Database username
define('DB_PASSWORD',		"nwp2010"				); // Database password
define('DB_NAME',			"assessment"			); // Database name

$table_prefix = '';									// Table prefix(if any)
$db_usertable = 'fos_user_user';							// Users or members information table name
$db_usertable_userid = 'id';					// UserID field in the users or members table
$db_usertable_name = 'concat('.$table_prefix.$db_usertable.'.firstname, " ",'.$table_prefix.$db_usertable.'.lastname) ';					// Name containing field in the users or members table
$db_avatartable = ' ';
$db_avatarfield = ' '.$table_prefix.$db_usertable.'.'.$db_usertable_userid.' ';
$db_linkfield = ' '.$table_prefix.$db_usertable.'.'.$db_usertable_userid.' ';

/*COMETCHAT'S INTEGRATION CLASS USED FOR SITE AUTHENTICATION */

class Integration{

	function __construct(){
		if(!defined('TABLE_PREFIX')){
			$this->defineFromGlobal('table_prefix');
			$this->defineFromGlobal('db_usertable');
			$this->defineFromGlobal('db_usertable_userid');
			$this->defineFromGlobal('db_usertable_name');
			$this->defineFromGlobal('db_avatartable');
			$this->defineFromGlobal('db_avatarfield');
			$this->defineFromGlobal('db_linkfield');
		}
	}

	function defineFromGlobal($key){
		if(isset($GLOBALS[$key])){
			define(strtoupper($key), $GLOBALS[$key]);
			unset($GLOBALS[$key]);
		}
	}

	function getUserID() {
		$userid = 0;
		if (!empty($_SESSION['basedata']) && $_SESSION['basedata'] != 'null') {
			$_REQUEST['basedata'] = $_SESSION['basedata'];
		}

		if (!empty($_REQUEST['basedata'])) {

			if (function_exists('mcrypt_encrypt') && defined('ENCRYPT_USERID') && ENCRYPT_USERID == '1') {
				$key = "";
				if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
					$key = KEY_A.KEY_B.KEY_C;
				}
				$uid = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(rawurldecode($_REQUEST['basedata'])), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
				if (intval($uid) > 0) {
					$userid = $uid;
				}
			} else {
				$userid = $_REQUEST['basedata'];
			}
		}
		//if (!empty($_SESSION['userid'])) {
		//	$userid = $_SESSION['userid'];
		//}
                if (!empty($_SESSION['_sf2_attributes']['_security_main'])) {
                    $k = $_SESSION['_sf2_attributes']['_security_main'];
                    $m = explode(';s:',$k);
                    $t = explode(':"',$m[5]);
                    $j = explode('";',$t[1]);
                    $sql = "select id FROM `fos_user_user` where username = '".$j[0]."'";
                    //$sql = "select id FROM `fos_user_user` where username = 'admin'";
                    $result = mysqli_query($GLOBALS['dbh'],$sql);
		    $row = mysqli_fetch_array($result);
                    $userid = $row['id'];
                }
		$userid = intval($userid);
		return $userid;
	}

	function chatLogin($userName,$userPass) {

		$userid = 0;
		global $guestsMode;

		if (filter_var($userName, FILTER_VALIDATE_EMAIL)) {
			$sql = ("SELECT * FROM `".TABLE_PREFIX.DB_USERTABLE."` WHERE email = '".mysqli_real_escape_string($GLOBALS['dbh'],$userName)."'");
		} else {
			$sql = ("SELECT * FROM `".TABLE_PREFIX.DB_USERTABLE."` WHERE ".DB_USERTABLE_NAME." = '".mysqli_real_escape_string($GLOBALS['dbh'],$userName)."'");
		}
		$result = mysqli_query($GLOBALS['dbh'],$sql);
		$row = mysqli_fetch_assoc($result);
		/* Add your password validation mechanism here */
		//$salted_password = md5($row['value'].$userPass.$row['salt']);

		if($row['password'] == $userPass) { //replace $userPass with $salted_password if you have edited the password validation mechanism
			$userid = $row[DB_USERTABLE_USERID];
		}
		if(!empty($userName) && !empty($_REQUEST['social_details'])) {
			$social_details = json_decode($_REQUEST['social_details']);
			$userid = socialLogin($social_details);
		}
		if(!empty($_REQUEST['guest_login']) && $userPass == "CC^CONTROL_GUEST" && $guestsMode == 1){
			$userid = getGuestID($userName);
		}
		if(!empty($userid) && isset($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp'){
			$sql = ("insert into cometchat_status (userid,isdevice) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','1') on duplicate key update isdevice = '1'");
                mysqli_query($GLOBALS['dbh'], $sql);
		}
		if($userid && function_exists('mcrypt_encrypt') && defined('ENCRYPT_USERID') && ENCRYPT_USERID == '1') {
			$key = "";
			if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
				$key = KEY_A.KEY_B.KEY_C;
			}
			$userid = rawurlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $userid, MCRYPT_MODE_CBC, md5(md5($key)))));
		}

		return $userid;
	}

	function getFriendsList($userid,$time) {
		global $hideOffline;
		$offlinecondition = '';
                //next line is commented out because of firstname, lastname change instead of username
		//$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX."friends join ".TABLE_PREFIX.DB_USERTABLE." on  ".TABLE_PREFIX."friends.toid = ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX."friends.fromid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' order by username asc");
		$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".DB_USERTABLE_NAME." username, ".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX."friends join ".TABLE_PREFIX.DB_USERTABLE." on  ".TABLE_PREFIX."friends.toid = ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX."friends.fromid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' order by username asc");
                if ((defined('MEMCACHE') && MEMCACHE <> 0) || DISPLAY_ALL_USERS == 1) {
			if ($hideOffline) {
				$offlinecondition = "where ((cometchat_status.lastactivity > (".mysqli_real_escape_string($GLOBALS['dbh'],$time)."-".((ONLINE_TIMEOUT)*2).")) OR cometchat_status.isdevice = 1) and (cometchat_status.status IS NULL OR cometchat_status.status <> 'invisible' OR cometchat_status.status <> 'offline')";
			}
                        //next line is commented out because of firstname, lastname change instead of username
			//$sql = ("select ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from  ".TABLE_PREFIX.DB_USERTABLE."   left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." ".$offlinecondition ." order by username asc");
                        $sql = ("select ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".DB_USERTABLE_NAME." username, ".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from  ".TABLE_PREFIX.DB_USERTABLE."   left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." ".$offlinecondition ." order by username asc");
		}

		return $sql;
	}

	function getFriendsIds($userid) {

		$sql = ("SELECT toid as friendid FROM `friends` WHERE status =1 and fromid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' union SELECT fromid as myfrndids FROM `friends` WHERE status = 1 and toid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");

		return $sql;
	}

	function getUserDetails($userid) {
                //next line is commented out because of firstname, lastname change instead of username
		//$sql = ("select ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");
                $sql = ("select ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".DB_USERTABLE_NAME." username, ".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");
		return $sql;
	}

	function getActivechatboxdetails($userids) {
                 //next line is commented out because of firstname, lastname change instead of username
		//$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." IN (".$userids.")");
                $sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".DB_USERTABLE_NAME." username, ".DB_LINKFIELD." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." IN (".$userids.")");
		return $sql;
	}

	function getUserStatus($userid) {
		$sql = ("select cometchat_status.message, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status from cometchat_status where userid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");

		return $sql;
	}

	function fetchLink($link) {
		return '';
	}

	function getAvatar($image) {
		return BASE_URL.'images/noavatar.png';
	}

	function getTimeStamp() {
		return time();
	}

	function processTime($time) {
		return $time;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/* HOOKS */

	function hooks_message($userid,$to,$unsanitizedmessage,$dir) {

	}

	function hooks_forcefriends() {

	}

	function hooks_updateLastActivity($userid) {

	}

	function hooks_statusupdate($userid,$statusmessage) {

	}

	function hooks_activityupdate($userid,$status) {

	}

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* LICENSE */

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'license.php');
$x = "\x62a\x73\x656\x34\x5fd\x65c\157\144\x65";
eval($x('JHI9ZXhwbG9kZSgnLScsJGxpY2Vuc2VrZXkpOyRwXz0wO2lmKCFlbXB0eSgkclsyXSkpJHBfPWludHZhbChwcmVnX3JlcGxhY2UoIi9bXjAtOV0vIiwnJywkclsyXSkpOw'));

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
