<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/


if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$navigation = <<<EOD
	<div id="leftnav">
	<a href="?module=editor&amp;ts={$ts}" id="editor_integration" class="midmenu">Integration</a>
	<a href="?module=editor&amp;customjs=1&amp;ts={$ts}" id="editor_extrajs" class="midmenu">Custom JS</a>
	<a href="?module=editor&amp;customcss=1&amp;ts={$ts}" id="editor_extracss" class="midmenu">Custom CSS</a>
	</div>
EOD;
function index() {
	if (!empty($GLOBALS['client'])) { echo "Not Found"; exit; }
	global $body, $navigation, $ts, $currentversion, $settings, $writable;
	$restoreurl = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$restoreurl = substr($restoreurl,0, strpos($restoreurl, '?')).'update/restore.php';
	$restoreurl = str_replace('index.php', '', $restoreurl);
	$restorebtn = '';
	$warning = '';
	$text = '';
	$isenabled = 0;
	$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'integration.php';
	if (isset($_POST['text'])){
		$type = $_POST['hiddenfield'];
		if($type != 'integration'){
			configeditor(array('enable_'.$type => $_POST['enable']));
			$isenabled = $_POST['enable'];
			$settings['enable_'.$type]['value'] = $_POST['enable'];
			configeditor(array($type => $_POST['text']));
			$text = $_POST['text'];
			$_SESSION['cometchat']['error'] = 'File saved successfully.';
		}elseif(is_writable($file)){
			file_put_contents($file, $_POST['text']);
			$text = file_get_contents($file);
			$_SESSION['cometchat']['error'] = 'File saved successfully.';
		}else{
			$_SESSION['cometchat']['error'] = 'File not saved, permission denied.';
		}
		clearcache(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.$writable);
	}
	if(!empty($_REQUEST['customjs'])){
		if(!empty($settings['customjs']['value']) && empty($_POST['text'])){
			$text = $settings['customjs']['value'];
		}
		$url = '?module=editor&customjs=1&ts='.$ts;
		$filename = 'custom js';
		$active_id = 'editor_extrajs';
		if((!empty($settings['enable_customjs']) && $settings['enable_customjs']['value'] == 1) || $isenabled == 1){
			$hiddenfield = 'Enable custom JS<input type="radio" name="enable" value="1" checked/>Yes<input type="radio" name="enable" value="0"/>No<input type="hidden" name="hiddenfield" value="customjs"/> ';
		}else{
			$hiddenfield = 'Enable custom JS<input type="radio" name="enable" value="1" />Yes<input type="radio" name="enable" value="0" checked />No<input type="hidden" name="hiddenfield" value="customjs"/> ';
		}
	}elseif(!empty($_REQUEST['customcss'])){
		if(!empty($settings['customcss']['value']) && empty($_POST['text'])){
			$text = $settings['customcss']['value'];
		}
		$url = '?module=editor&customcss=1&ts='.$ts;
		$filename = 'custom css';
		$active_id = 'editor_extracss';
		if((!empty($settings['enable_customcss']) && $settings['enable_customcss']['value'] == 1) || $isenabled == 1 ){
			$hiddenfield = 'Enable custom CSS<input type="radio" name="enable" value="1" checked/>Yes<input type="radio" name="enable" value="0"/>No<input type="hidden" name="hiddenfield" value="customcss"/> ';
		}else{
			$hiddenfield = 'Enable custom CSS<input type="radio" name="enable" value="1" />Yes<input type="radio" name="enable" value="0" checked />No<input type="hidden" name="hiddenfield" value="customcss"/> ';
		}
	}else{
		$url = '?module=editor&ts='.$ts;
		$filename = 'integration.php';
		$active_id = 'editor_integration';
		$hiddenfield = '<input type="hidden" name="hiddenfield" value="integration"/> ';
		$restorebtn = '<button class="button" id="backupbtn" style="margin-top:-30px;float:right;">Backup this copy</button><button class="button" id="restorebtn" style="right: 3%;margin-top: -30px;float: right;">Restore to default</button>';
		$warning = <<<EOD
		<div id="rightnav">
				<h1 style="padding-bottom:0px !important;color:red;">Warning</h1>
					<p class="para"> Kindly copy the following URL <span style="color:blue;">{$restoreurl}</span> before you make changes to this "integration.php" file, which will help you go to previous version of this file. This is important, because if the site does not load post making changes then you can easily restore it from this URL.
					</p>
			</div>
EOD;
		$text = file_get_contents($file);
	}
	$body = <<<EOD
		{$navigation}
		<script src="js/jquery-linedtextarea.js"></script>
		<link href="css/jquery-linedtextarea.css" type="text/css" rel="stylesheet" />
		<style>
		.codeEditor{
			resize: none;
		    font-size: 11px;
		    width: 97% !important;
		    border: none;
		    position: absolute;
		}
		.linedtextarea{
			margin-left: 50px !important;
			position: relative !important;
		}
		.linedwrap{
			width: 100% !important;
			margin-top:20px;
		}
		#rightnav{
			float: none;
			width: 100% !important;
			padding : 0px !important;
		}
		.para{
			padding-left: 10px;
			padding: 10px;
		}
		</style>

		<script>
			jQuery(function() {
				$(".midmenu").removeClass('active_setting');
				$("#{$active_id}").addClass('active_setting');
				$(".codeEditor").linedtextarea({selectedLine: 1});
				$(".codeEditor").focus(function(){
					$(".linedwrap").css('box-shadow','inset 0 0 5px red');
				});
				$(".codeEditor").blur(function(){
					$(".linedwrap").css('box-shadow','inset 0 0 ');
				});
				var winHt = $(window).height();
				var warningHt = $('#rightnav').height();
				$('.codeEditor').height(winHt - 255 - warningHt);
				$('.linedwrap').height(winHt - 250 - warningHt);
				$('.lines').height(winHt - 250 - warningHt);

				$(window).resize(function(){
					var winHt = $(window).height();
					$('.codeEditor').height(winHt - 255 - warningHt);
					$('.linedwrap').height(winHt - 250 - warningHt);
					$('.lines').height(winHt - 250 - warningHt);
				});
				$("#restorebtn").click(function(){
					var confirmation = confirm('Are you sure you wish to restore this with backup copy?');
					if(confirmation == true){
						$.ajax({
							url: 'update/restore.php',
							type: 'get',
							success: function(data){
								window.location.href = '?module=editor';
							}
						});
					}
				});
				$("#backupbtn").click(function(){
					var confirmation = confirm('Are you sure you wish to backup this copy?');
					if (confirmation == true) {
						$.ajax({
							url: 'update/restore.php',
							data: {'backup':true},
							type: 'get',
							success: function(data){
								window.location.href = '?module=editor';
							}
						});
					}
				});
				$(".lineno").removeClass('lineselect');
			});
			function submit_form(){
				var confirmation = confirm('Are you sure you wish to save this file?');
				if (confirmation == true) {
					$('#editsubmit').submit();
				}
			}
		</script>
		<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
			<h2>{$filename}</h2>
			{$warning}
				<form action="" method="post" id="editsubmit" >
					{$hiddenfield}
					<textarea id="editortext" class="codeEditor" name="text">$text</textarea><br>
					<input type="button" value="save" id="savebtn" class="button" onclick="submit_form()"/> or <a href="?module=editor&amp;ts={$ts}">cancel</a>
					</form>
					{$restorebtn}

		</div>
EOD;

template();

}
