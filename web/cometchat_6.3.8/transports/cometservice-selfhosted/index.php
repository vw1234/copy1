<?php
/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');

$temp = parse_url(CS_TEXTCHAT_SERVER);
$csrelayServer = $temp['host'].":3478";

$cssurl = '';
if(!empty($_REQUEST['cssurl'])){
	$cssurl = $_REQUEST['cssurl'];
}
$room = $r = null;
if(!empty($_REQUEST['room'])){
	$room = $_REQUEST['room'];
}
$audioOnly = 0;
if(!empty($_REQUEST['audioOnly'])){
	$audioOnly = $_REQUEST['audioOnly'];
}

if(isset($_REQUEST['broadcast'])){
	$broadcast = $_REQUEST['broadcast'];
}

$screenshare = null;
if(isset($_REQUEST['screenshare'])){
	$screenshare = $_REQUEST['screenshare'];
}

$cc_mute = 0;
if(isset($_REQUEST['cc_mute'])){
	$cc_mute = 1;
}

$m1 = 'Turn On Mic';
$m0 = 'Turn Off Mic';
$v1 = 'Turn On Video';
$v0 = 'Turn Off Video';
$b1 = 'End Call';
$b2 = 'Invite Users';
$b3 = 'Mute';
$b4 = 'Unmute';
$pluginname = '';
$to = 0;
$baseData = null;
$chatroommode = 0;
$caller = '';
$hostpath = '';
$host = '';
if(!empty($_REQUEST['m0'])){
	$m0 = $_REQUEST['m0'];
}
if(!empty($_REQUEST['m1'])){
	$m1 = $_REQUEST['m1'];
}
if(!empty($_REQUEST['v0'])){
	$v0 = $_REQUEST['v0'];
}
if(!empty($_REQUEST['v1'])){
	$v1 = $_REQUEST['v1'];
}
if(!empty($_REQUEST['b1'])){
	$b1 = $_REQUEST['b1'];
}
if(!empty($_REQUEST['b2'])){
	$b2 = $_REQUEST['b2'];
}
if(!empty($_REQUEST['b3'])){
	$b3 = $_REQUEST['b3'];
}
if(!empty($_REQUEST['b4'])){
	$b4 = $_REQUEST['b4'];
}
if(!empty($_REQUEST['pluginname'])){
	$pluginname = $_REQUEST['pluginname'];
}
if(!empty($_REQUEST['to'])){
	$to = $_REQUEST['to'];
}
if(!empty($_REQUEST['basedata'])){
	$baseData = $_REQUEST['basedata'];
}
if(!empty($_REQUEST['crmode'])){
	$chatroommode = $_REQUEST['crmode'];
}
if(!empty($_REQUEST['caller'])){
	$caller = $_REQUEST['caller'];
}
if(!empty($_REQUEST['hostpath'])){
	$hostpath = $_REQUEST['hostpath'];
}

if(!empty($_SERVER['HTTP_REFERER'])){
	$r = $_SERVER['HTTP_REFERER'];
}

$chromeExtension = "https://chrome.google.com/webstore/detail/ijohainfmdpboehcinajachbeelgkcdf";
$firefoxExtension = "browser-plugins/screenshare-1.7.6-fx.xpi";
if(!empty($_REQUEST['chromeExtension'])){
	$chromeExtension = $_REQUEST['chromeExtension'];
}
if(!empty($_REQUEST['firefoxExtension'])){
	$firefoxExtension = $_REQUEST['firefoxExtension'];
}

if(empty($r) || empty($room)){
	?>
	<!DOCTYPE html>
		<html>
			<head>
			</head>
			<body class="J436grhmflEVKGw">
			</body>
		</html>
	<?php
	exit();
}

if(!empty($r)){
	$r = str_replace("init.php","index.php",$r);
    $r = str_replace("AND","&",$r);
    $r = str_replace("QUESTION","?",$r);
	$host = parse_url($r,PHP_URL_HOST);
}

if(!empty($_REQUEST['cssurl'])){
	$cssurl = $_REQUEST['cssurl'];
	/*$data=parse_url($cssurl);
	$host=$data['host'];*/
}
	$devmode = 1;
?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="user-scalable=1,width=device-width, initial-scale=1.0" />
		<script type="text/javascript">
		   var client_host = '<?php echo $host; ?>';
           var firefoxExtension = "<?php echo $firefoxExtension; ?>";
           var chromeExtension = "<?php echo $chromeExtension; ?>";
           var csrelayServer = "<?php echo $csrelayServer; ?>";
           var webrtcServer = "<?php echo CS_TEXTCHAT_SERVER; ?>";
        </script>
        <script src="../../js.php?type=core&name=jquery" type="text/javascript"></script>
		<!-- <script type="text/javascript" src="js/adapter.js"></script> -->
			<script type="text/javascript" src="comet.js"></script>
			<script type="text/javascript" src="js/webrtc.js"></script>
		<script type="text/javascript">
			if(typeof($)=="undefined"){
				$ = jqcc;
			}
			var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
			var eventer = window[eventMethod];
			var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
			// Listen to message from child window
			$(document).ready(function() {
				$("head").append('<link href="<?php echo $cssurl; ?>" rel="stylesheet" />');
				eventer(messageEvent,function(e) {
					if(typeof e.data != 'undefined' && typeof e.data == "string"){
						if(e.data.indexOf('CC^WEBRTC_')!== -1){
							if(e.data.indexOf('UNMUTE_VIDEO')!== -1){
								var VID = $(document).find('.VID');
								if(VID.attr('ison')=="0"){
									VID.click();
								}
							}else if(e.data.indexOf('UNMUTE_AUDIO')!== -1){
								var AUD = $(document).find('.AUD');
								if(AUD.attr('ison')=="0"){
									AUD.click();
								}
							}else if(e.data.indexOf('MUTE_VIDEO')!== -1){
								var VID = $(document).find('.VID');
								if(VID.attr('ison')=="1"){
									VID.click();
								}
							}else if(e.data.indexOf('MUTE_AUDIO')!== -1){
								var AUD = $(document).find('.AUD');
								if(AUD.attr('ison')=="1"){
									AUD.click();
								}
							}
						}
					}
				},false);

				var pluginname = "<?php echo $pluginname; ?>";
				var broadcast = "<?php if(isset($_REQUEST['broadcast'])){ echo $broadcast; } ?>";

				$('#broadcastInvite').click(function(){
					inviteuser_broadcast();
				});

                $(".install_extn").on('click', function() {
            		setTimeout(function(){ window.close(); }, 3000);
        		});

        		$("#cc_closewindow").on('click', function() {
            		window.close();
        		});

        		/* Fix for IE */
        		var isIE = /*@cc_on!@*/false || !!document.documentMode;
        		if(isIE) {
        			$('.ie_fix').show();
					$('#cc_webrtcbuttons').addClass('ie_btnfix');
				}

			});
			function inviteuser_broadcast(){
				var grp = "'<?php echo $room; ?>'";
				var baseData = "'<?php echo $baseData; ?>'";
				var hostpath = "<?php echo $hostpath; ?>";
				var caller = "'<?php echo $caller; ?>'";

				window.open(hostpath + "plugins/broadcast/invite.php?action=invite&caller="+caller+"&grp="+grp+"&basedata="+ baseData, "invitebroadcast","status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=400,height=190");
			}
        </script>
		<link type="text/css" href="css/webrtc.css" rel="stylesheet" charset="utf-8">
	</head>
	<body style="padding: 0; margin: 0; overflow: auto; background: #FFFFFF;">
		<div id="videoChat">
			<div id="loading">This might take a while...<br /><img src="images/loaderbar.gif" height="10px" width="230px"></div>
			<?php if(!$audioOnly){ ?>
				<div id="video"></div>
			<?php
				}else{ ?>
				<div id="video" class="audonly"></div>
				<div id="CC_audio"></div>
			<?php }
			if(!empty($_REQUEST['hostpath']) && $hostpath != '') { ?>
				<div class="END cometchat_statusbutton" ison="1"><?php echo $b1; ?></div>
			<?php }
			if(!isset($broadcast)||(isset($broadcast)&&empty($broadcast))){ ?>
				<div class="but" id="cc_webrtcbuttons" style="position:fixed;right:10px;bottom:20px;z-index: 10000;">
				<?php
				if(isset($_REQUEST['broadcast']) && $broadcast == 0) {
					if(!empty($_REQUEST['hostpath']) && $hostpath != '') { ?>
						<div id="broadcastInvite" class="cometchat_statusbutton"><?php echo $b2; ?></div>
						<?php
					}
				}
				if ($pluginname != 'screenshare') {
				?>	
					<iframe class="ie_fix" src="about:blank"></iframe>
					<div class="AUD cometchat_statusbutton" ison="1"><?php echo $m0; ?></div>
					<?php if(!$audioOnly){ ?>
						<div class="VID cometchat_statusbutton" ison="1"><?php echo $v0; ?></div>
						<?php
					}
				}
				if (!isset($broadcast)){
		 		?>
			 		<div id="cc_mute" class="cometchat_statusbutton" ison="1">
			 			<?php echo $b3; ?>
			 		</div>
		 		<?php
		 		}
		 		?>
				</div>
				<?php
			}else{
			?>
				<div class="but" style="position:fixed;right:10px;bottom:20px;z-index: 10000;">
					<div id="cc_mute" class="cometchat_statusbutton" ison="1">
						<?php echo $b3; ?>
					</div>
				</div>

			<?php
			}
				if($pluginname == 'screenshare') {  ?>
					<div id="cometchat_screenshare_overlay"></div>
					<div id="cometchat_screenshare_lightbox">
						<div class="ext_install_steps"></div>
					</div>

					<div id="cc_screensharebuttons">
						<div id="cc_changewindow" class="cometchat_screensharebutton">Change Window</div>
						<div id="cc_closewindow" class="cometchat_screensharebutton">Stop Sharing</div>
					</div>
			<?php	} ?>
		</div>
		<div id="log">
		</div>
	</body>
	</html>
