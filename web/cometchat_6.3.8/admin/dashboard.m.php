<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$navigation = <<<EOD
	<div id="leftnav">
	</div>
EOD;

function index() {
	global $body, $currentversion, $client, $ts, $livesoftware;
	$available_version = setConfigValue('LATEST_VERSION','');
	$stats = '';

	if (!empty($_GET['d'])) {
		header("Location: ".ADMIN_URL."\r\n");
		exit;
	}

	$onlineusers = onlineusers();

	$sql = ("select (select count(id) from cometchat) + (select count(id) from cometchat_chatroommessages) as totalmessages");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$r = mysqli_fetch_assoc($query);
	$totalmessages = $r['totalmessages'];
	if(empty($totalmessages)){$totalmessages='0';}
	$hrs24 = getTimeStamp()-60*60*24;
	$sql = ("select (select count(id) from cometchat where sent >= '".mysqli_real_escape_string($GLOBALS['dbh'],$hrs24)."') + (select count(id) from cometchat_chatroommessages where sent >= '".mysqli_real_escape_string($GLOBALS['dbh'],$hrs24)."') as totalmessages");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$r = mysqli_fetch_assoc($query);
	$totalmessagest = $r['totalmessages'];
	$stats = <<<EOD

	<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;">
		<h1 style="font-size: 50px; font-weight: bold;">$onlineusers</h1>
		<span style="font-size: 10px;">USERS CHATTING</span>
	</div>

	<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;">
		<h1 style="font-size: 50px; font-weight: bold;">$totalmessages</h1>
		<span style="font-size: 10px;">TOTAL MESSAGES</span>
	</div>

	<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;width:100px;">
		<h1 style="font-size: 50px; font-weight: bold;">$totalmessagest</h1>
		<span style="font-size: 10px;">MESSAGES SENT IN THE LAST 24 HOURS</span>
	</div>

EOD;

	$detectchangepass = 'Below are quick statistics of your site.';
	if(empty($client)) {
		$detectchangepass .= ' Be sure to frequently change your administrator password.';
	}
	if ( ADMIN_USER == 'cometchat' && ADMIN_PASS == 'cometchat' && empty($client)) {
		$detectchangepass = '<span style="color:#ff0000">Warning: Default administrator username/password detected. Please go to settings and change the username and password.</span>';
	}

	if (empty($totalmessages)) {
		$totalmessages = 0;
	}

	$cc_version_color = 'color:green;';
	$acc_version_color = '';
	if ($available_version != '') {
		$cc_version_color  = 'color:#ff0000;';
		$acc_version_color = 'color:green;';
	}

		$body = <<<EOD
<h2>Welcome</h2>
<h3>$detectchangepass</h3>


	<div style="float:left">

		{$stats}
		<div style="clear:both;padding:10px;"></div>

		<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;">
			<h1 style="font-size: 70px; font-weight: bold;{$cc_version_color}">$currentversion</h1>
			<span style="font-size: 10px;">CURRENT VERSION</span>
		</div>
EOD;
global $marketplace;
if($marketplace == 0){
	if ($available_version == '') {
		$body .= <<<EOD
		<div class="uptodate">
		<div style="clear:both;padding:20px;"></div>
		<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;width:155px;">
		<h1 style="font-size: 20px; font-weight: bold;">COMETCHAT IS UP TO DATE</h1>
		</div>
		</div>
EOD;
	}else{
		$writablepath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR;
		$button = '<button id="ccbtn" onclick="window.location.href=\'?module=update&action=updateNow&ts='.$ts.'\'" style="margin-top:53px;" class="button" id="updatenow">UPDATE NOW</button>';
		if(!file_exists($writablepath.'updates'.DIRECTORY_SEPARATOR.$available_version.DIRECTORY_SEPARATOR.'cometchat.zip')){
			$button = '<button id="ccbtn" onclick="window.location.href=\'?module=update&force=1&ts='.$ts.'\'" style="margin-top:53px;" class="button">DOWNLOAD NOW</button>';
		}
		$body .= <<<EOD
		<div class="newVersion">
		<div style="clear:both;padding:20px;"></div>
		<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;">
		<h1 style="font-size: 70px; font-weight: bold;{$acc_version_color}">{$available_version}</h1>
		<span style="font-size: 10px;">AVAILABLE VERSION </span>
		</div>
		{$button}
		</div>
EOD;
	}
}
		$body .= <<<EOD
		<div style="clear:both;padding:20px;"></div>

		<div style="width:450px;font-family:helvetica;line-height:1.4em;font-size:14px;">
			<span style="font-weight:bold;">Love CometChat?</span><br/>Take a minute to <a href="https://www.cometchat.com/reviews/write/" target="_blank">write us a testimonial</a> :)
		</div>


	</div>
	<div style="float:right">
		<iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2FCometChat%2F&tabs=timeline&width=500&height=300&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=false&appId=143961562477205" width="500" height="300" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
	</div>


<div style="clear:both"></div>

EOD;

	if (empty($_SESSION['cometchat']['VERSION_CHECK']) && $marketplace == 0) {

	$body .= <<<EOD
		<script>
			jQuery(function() {
				var current_version = '{$currentversion}';
				$.ajax({
					url: 'https://my.cometchat.com/{$livesoftware}/getversion?callback=?',
					type: 'get',
					dataType: 'jsonp',
					crossDomain:true,
					xhrFields: {
						withCredentials: false
					},
					success: function(data) {
						if(data.version){
							var version = data.version;
							if(version > current_version){
								window.location.href = 'index.php?module=dashboard&action=updateNewVersion&version='+version;
							}else{
								window.location.href = 'index.php?module=dashboard&action=updateNewVersion&version=empty';
							}
	                	}
					},
					error:function(data){
						console.log("error data",data);
					}
				});
			});
		</script>
EOD;
$_SESSION['cometchat']['VERSION_CHECK'] = 1;
}
	template();
}

function updateNewVersion(){
	global $ts;
	if($_GET['version'] == 'empty'){
		$newVersion = array('LATEST_VERSION' => '');
	}else{
		$newVersion = array('LATEST_VERSION' => $_GET['version']);
	}
	configeditor($newVersion);
	header("Location:?module=dashboard&ts={$ts}");
	exit();
}

function loadexternal() {
	global $getstylesheet;
	if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php')) {
		include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php');
	} else {
echo <<<EOD
$getstylesheet
<form>
<div id="content">
		<h2>No configuration required</h2>
		<h3>Sorry there are no settings to modify</h3>
		<input type="button" value="Close Window" class="button" onclick="javascript:window.close();">
</div>
</form>
EOD;
	}
}

function loadthemetype() {
	global $getstylesheet;
	if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php')) {
		include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php');
	} else {
echo <<<EOD
$getstylesheet
<form>
<div id="content">
		<h2>No configuration required</h2>
		<h3>Sorry there are no settings to modify</h3>
		<input type="button" value="Close Window" class="button" onclick="javascript:window.close();">
</div>
</form>
EOD;
	}
}

function themeembedcodesettings() {
	global $getstylesheet;
	if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php')) {
		$generateembedcodesettings = 1;
		include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php');
	} else {
echo <<<EOD
$getstylesheet
<form>
<div id="content">
		<h2>No configuration required</h2>
		<h3>Sorry there are no settings to modify</h3>
		<input type="button" value="Close Window" class="button" onclick="javascript:window.close();">
</div>
</form>
EOD;
	}
}
