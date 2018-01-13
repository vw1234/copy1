<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/


if (!defined('CCADMIN')) { echo "NO DICE"; exit; }
global $marketplace;
if($marketplace == 1) { echo "NO DICE"; exit; }

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'CometChatUpdate.php');

global $update;
$update = new CometChatUpdate();


function index() {
	global $body, $cms, $navigation, $ts, $currentversion, $licensekey, $update, $settings, $livesoftware;
	$force = '';
	if(!empty($_REQUEST['force']) && $_REQUEST['force'] == 1){
		$force = 'downloadPackage();';
	}
	$type = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
	$server = $type.'://'.$_SERVER['SERVER_NAME'].BASE_URL.'admin/update/index.php';

	$body = <<<EOD
		<script>
				jQuery(function() {
					{$force}
					jQuery("#downloadnow").click(function(){
						downloadPackage();
					});

					function downloadPackage(){
						$('#updatestatus').html('<img src="images/downloading.gif" height="200" width="270"/><br/><b style="font-size:20px;">Downloading...</b>');
						$.ajax({
							url: 'https://my.cometchat.com/{$livesoftware}/getupdate?license={$licensekey}&callback_url={$server}',
							type:'get',
							dataType:'jsonp',
							contentType:'application/json;charset=utf-8',
							xhrFields: {
								withCredentials: false
							},
							success: function(data) {
								if(data.status==1){
									downloadZip();
								}else{
									$('#updatestatus').html('');
									$("#alertmessage").html(data.message);
									$("#alertmessage").show();
								}
							}
						});
					}

					function downloadZip(){
						$('#updatestatus').html('<img src="images/downloading.gif" height="200" width="270"/><br/><b style="font-size:20px;">Downloading...</b>');
						$.ajax({
							url: 'index.php?module=update&action=processUpdate',
							type: 'get',
							dataType:'json',
							contentType:'application/jsonp;charset=utf-8',
							success: function(data){
								if(data == 1){
									window.location.href='index.php?module=update&action=updateNow';
								}else{
									$('#updatestatus').html('');
									$("#alertmessage").html(data);
								}
							},
							error: function(error){
								$('#updatestatus').html('');
								$("#alertmessage").html('An error occured: '+error.response);
								$("#alertmessage").show();
							}
						});
					}
				});
			</script>
EOD;
	if($update->checkAvailableZip()){
		header("Location:?module=update&action=updateNow&ts={$ts}");
	} else{
		$body .= <<<EOD
		<h2>Downloading the new version
		<br>Version number: {$settings['LATEST_VERSION']['value']}</h2>

		<div>

			<div id="centernav" style="width:890px !important;margin-top: 30px;">
			<div class="" id="updatestatus" style="text-align:center !important;"><img src="images/downloading.gif" height="200" width="270"/><br/><b style="font-size:20px;">Downloading...</b></div>
			<div class="success" ><div class='success' id = 'alertmessage' style = 'height:40px;padding: 20px;display:none;'></div></div>
				<div style="clear:both;padding:10px;"></div>
			</div>
			<div id="rightnav">
				<h1>Tips</h1>
				<ul id="modules_availablemodules">
					<li>If you face any issue with the update, please contact our <a target="_blank" href="https://support.cometchat.com">support team</a> to assist you.</li>
 				</ul>
 				<h1>Note:</h1>
				<ul id="modules_availablemodules">
					<li>This feature is currently in beta. We recommend proceeding only after you have taken a complete backup of your server/site.</li>
 				</ul>
			</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>
EOD;
	}
template();

}
function updateNow(){
	global $body, $cms, $navigation, $ts, $update, $currentversion, $licensekey, $settings;
	ini_set('max_execution_time', 300);
	$type = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
	$server = $type.'://'.$_SERVER['SERVER_NAME'].BASE_URL.'admin/update/index.php';
	$body = <<<EOD
		<script>
				jQuery(function() {
					jQuery("#updatenow").click(function(){
						$("#updatenow").hide();
						$(".success").show();
						$("#step_no_1").addClass('selected');
						generateHash();
					});
					jQuery('#yesupdate').live('click', function(e){
						backupFiles();
					});
					jQuery('#cancelupdate').live('click', function(e){
						$('#alertmessage').hide();
						$("#alertmessage").text('Update skipped.');
						alert('Please take the help of our support team (https://support.cometchat.com/) to update.');
						window.location.replace('index.php?module=update');
					});
					jQuery('#changestryagain').live('click', function(e){
						applyChanges();
					});

					function generateHash(){
						$("#updatenow").hide();
						$(".success").show();
						$("#step_no_1").addClass('selected');
						$('#updatestatus').html('<img src="images/loading.gif" height="30" width="30"/><br/>Generating Hashes');
						compareHash();
					}

					function compareHash(){
						$("#step_no_1").addClass('done');
						$("#step_no_2").addClass('selected');
						$('#updatestatus').html('<img src="images/loading.gif" height="30" width="30"/><br/>Comparing Hashes');
						$.ajax({
							url: 'index.php?module=update&action=compareHashes',
							type:'get',
							success: function(data){
								if(data.trim() == 1){
									backupFiles();
								}else{
									$('#updatestatus').html('');
									$("#alertmessage").html('It seems that some of the core files of CometChat  are modified on your server.<br /> Updating CometChat will overwrite these files.<br /> Do you still want to continue?<br><div id="updatewarning" style="margin-top: 10px;"><button style="background-color:#1ABB9C;border-color:#1ABB9C;" class="button" id="yesupdate" > Yes</button><button class="button" id="cancelupdate" style="background-color:#F44336;border-color:#F44336; margin-left:20px;" > No</button></div>');
								}
							}
						});
					}

					function backupFiles(){
						$("#step_no_2").addClass('done');
						$("#step_no_3").addClass('selected');
						$('#updatestatus').html('<img src="images/loading.gif" height="30" width="30"/><br/>Taking backup of files and tables');
						$('#alertmessage').hide();
						$.ajax({
							url: 'index.php?module=update&action=backupFiles',
							type:'get',
							success: function(data){
								if(data == 1){
									extractZip();
								}else{
									$('#updatestatus').html('');
									$("#alertmessage").html('An error occured: '+data.message);
									$("#alertmessage").show();
								}
							},
							error: function(error){
								$('#updatestatus').html('');
								$("#alertmessage").html('An error occured: '+error.response);
								$("#alertmessage").show();
							}
						});
					}

					function extractZip(){
						$("#step_no_3").addClass('done');
						$("#step_no_4").addClass('selected');
						$('#updatestatus').html('<img src="images/loading.gif" height="30" width="30"/><br/>Extracting zip file');
						$.ajax({
							url: 'index.php?module=update&action=extractZip',
							type:'get',
							success: function(data){
								if(data == 1){
									applyChanges();
								}else{
									$('#updatestatus').html('');
									$("#alertmessage").html('An error occured');
									$("#alertmessage").show();
								}
							}
						});
					}

					function applyChanges(){
						$("#step_no_4").addClass('done');
						$('#updatestatus').html('<img src="images/loading.gif" height="30" width="30"/><br/>Applying changes');
						$.ajax({
							url: 'index.php?module=update&action=applyChanges',
							type:'get',
							success: function(data){
								var data = JSON.parse(data);
								if(data.status == 1){
									var message = '';
									if(typeof(data.message) != 'undefined'){
										message = data.message;
										for(var i = 0; i < message.length; i++){
											$.ajax({
												url: '../updates/'+message[i],
												type: 'get',
												success: function(data){

												},
												error: function(error){
													$('#updatestatus').html('');
													$("#step_no_5").removeClass('selected done');
													$("#alertmessage").html('An error occured: '+error.response);
													$("#alertmessage").show();
												}
											});
										}
									}
									$("#step_no_5").addClass('done');
									$('#updatestatus').html('Successfully updated CometChat');
								}else{
									$('#updatestatus').html('');
									$("#alertmessage").html(data.message);
									$("#alertmessage").show();
								}
							},
							error: function(error){
								$('#updatestatus').html('');
								$("#step_no_5").removeClass('selected done');
								$("#alertmessage").html('An error occured: '+error.response);
								$("#alertmessage").show();
							}
						});
					}
				});
			</script>
EOD;
	if(!empty($settings['LATEST_VERSION']['value']) && $update->checkAvailableZip()){
		$body .= <<<EOD
		<h2>The new version is ready to be installed
		<br>Version number: {$settings['LATEST_VERSION']['value']}</h2>

		<div>
			<div id="centernav" style="width:890px !important;margin-top:30px;">
				<div id="wizard" class="form_wizard wizard_horizontal">
  <ul class="wizard_steps" style="width:100%;">
    <li>
      <a href="#step-1" id="step_no_1">
        <span class="step_no" >1</span>
        <span class="step_descr">
            <small>Generating hash</small>
        </span>
      </a>
    </li>
    <li>
      <a href="#step-2" id="step_no_2">
        <span class="step_no">2</span>
        <span class="step_descr">
            <small>Comparing hash</small>
        </span>
      </a>
    </li>
    <li>
      <a href="#step-3" id="step_no_3">
        <span class="step_no" >3</span>
        <span class="step_descr">
            <small>Taking backup of files <br> and tables</small>
        </span>
      </a>
    </li>
    <li>
      <a href="#step-4" id="step_no_4">
        <span class="step_no" >4</span>
        <span class="step_descr">
            <small>Extracting new files</small>
        </span>
      </a>
    </li>
    <li>
      <a href="#step-5" id="step_no_5">
        <span class="step_no" >5</span>
        <span class="step_descr">
            <small>Applying changes</small>
        </span>
      </a>
    </li>
  </ul>
  <div id="step-1">
      <h2 class="StepTitle" id="updatestatus"></h2>
    </div>
  </div>
					<button class='button' id='updatenow'>update now</button>
					<br/><div class="success" ><div class='success' id = 'alertmessage' style = 'height: 40px; padding: 20px;font-weight: bold;line-height: 17px;color: rgba(255, 87, 34, 0.9);display:none;'></div></div>
				<div style="clear:both;padding:10px;"></div>
			</div>
			<div id="rightnav">
				<h1>Tips</h1>
				<ul id="modules_availablemodules">
					<li>If you face any issue with the update, please contact our <a target="_blank" href="https://support.cometchat.com">support team</a> to assist you.</li>
 				</ul>
 				<h1>Note:</h1>
				<ul id="modules_availablemodules">
					<li>This feature is currently in beta. We recommend proceeding only after you have taken a complete backup of your server/site.</li>
 				</ul>
			</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>
EOD;
		}else{
			$body .= <<<EOD
					$navigation
					<div>
					<div id="centernav" style="width:700px">
					<button class='button' style='background-color:green;'>Please download the package and re try</button>
					</div>
					</div>
EOD;
		}
	template();
}

function generateHash(){
	$update = new CometChatUpdate();
	$update->generateHash(dirname(dirname(__FILE__)).DS);
	$update->insertHash('hashes');
}

function compareHashes(){
	global $update, $currentversion;
	if($update->compareHashes()){
		$response = 1;
	}else{
		$response = 0;
	}
	$_SESSION['cometchat']['old_version'] = $currentversion;
	echo $response;
}

function processUpdate(){
	global $update, $settings, $currentversion, $livesoftware;

	$downloadurl = 'https://my.cometchat.com/'.$livesoftware.'/getdownload/?token=';
	if(!empty($settings['latest_update_token'])){
		$path = $update->saveZip($downloadurl.$settings['latest_update_token']['value'], $settings['LATEST_VERSION']['value']);
		if($path == 1){
			$response = 1;
		}elseif($path == 'fail'){
			$response = '<p style="color:red;"><b>Unable to save the zip file please download the CometChat from <a href="http://my.cometchat.com" target="_blank">my.cometchat.com</a> and manually place it in /cometchat/writable/updates/'.$settings['LATEST_VERSION']['value'].'/cometchat.zip</b></p>';
		}else{
			$response = $path;
		}
	}else{
		$response = '<p style="color:red;"><b>Cannot find the token, please retry.</b></p>';
	}
	echo $response;
}

function backupFiles(){
	global $update;
	if($update->backupFiles()){
		$response = 1;
	}else{
		$response = 0;
	}
	echo $response;
}

function extractZip(){
	global $update;
	if($update->extractZip()){
		$response = 1;
	}else{
		$response = 0;
	}
	echo $response;
}

function applyChanges(){
	global $update;
	$testpermission = dirname(dirname(__FILE__));
	if(is_writable($testpermission)){
		if($frame = $update->applyChanges()){
			$response['status'] = 1;
			if(is_array($frame)){
				$response['message'] = $frame;
			}
		}else{
			$response['status'] = 0;
		}
	}else{
		$response['message'] = 'Please make '.dirname(dirname(__FILE__)).DS.' directory writable.<button class="button" id="changestryagain">Try again</button>';
		$response['status'] = 0;
 	}
 	configeditor(array('LATEST_VERSION'=>''));
 	configeditor(array('latest_update_token'=>''));
 	configeditor(array('OLD_VERSION'=> $_SESSION['cometchat']['old_version']));
	echo json_encode($response);
}

function applyDBChanges(){
	global $update;
	if($update->DBChanges()){
		echo 1;
	}
}
