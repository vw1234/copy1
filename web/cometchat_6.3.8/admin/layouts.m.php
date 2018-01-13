<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$navigation = <<<EOD
	<div id="leftnav">
		<a href="?module=layouts&amp;ts={$ts}" class="active_setting">Layouts</a>
	</div>
EOD;

function index() {
	global $body;
	global $navigation;
	global $color_original;
    global $theme_original;
    global $ts;

    $athemes = array();

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "base" && $file !="mobile" && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'config.php')) {
				if($file == 'embedded' || $file == 'docked') {
						$athemes[] = $file;
				}
			}
		}
		closedir($handle);
	}
	asort($athemes);
	array_push($athemes, "mobile");

	$activethemes = '';
	$no = 0;

	foreach ($athemes as $ti) {
		$title = ucwords($ti);

		++$no;

		$default = '';
		$opacity = '0.5';
		$setdefault = '';
		
		if (strtolower($theme_original) == strtolower($ti)) {
			$opacity = '1;cursor:default';
			$setdefault = '';
        }

        if (strtolower($ti) == 'mobile' || strtolower($ti) == 'synergy' || strtolower($ti) == 'embedded') {
			$Default = ' (Default)';
			$opacity = '1;cursor:default';
			$setdefault = '';
		}

		if(strtolower($ti) == 'embedded'){
			$default = '';
		}

		if (strtolower($ti) == 'embedded'){
			$activethemes .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'"><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;"></span><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.stripslashes($title).$default.'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:0px;"><a href="../cometchat_embedded.php" target="_blank" style="margin-right:5px;"><img src="images/link.png" title="Direct link to Embedded"></a><a href="javascript:void(0)" onclick="javascript:themetype_embedcode(\''.$ti.'\')" style="margin-right:5px;"><img src="images/embed.png" title="Generate Embed Code" ></a></span><div style="clear:both"></div></li>';
		}else if(strtolower($ti) == 'docked'){
			$activethemes .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'"><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.stripslashes($title).$default.'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;"><a href="javascript:void(0)" onclick="javascript:themetype_embedcode(\''.$ti.'\')"><img src="images/embed.png" title="Generate Footer Code" ></a></span><div style="clear:both"></div></li>';
		} else {
			$activethemes .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'"><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.stripslashes($title).$default.'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;"><a href="javascript:void(0)" onclick="javascript:themetype_configmodule(\''.$ti.'\')" style="margin-right:5px;"><img src="images/config.png" title="Edit '.$title.'"></a></span><div style="clear:both"></div></li>';
		}
	}

	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
                 <div>
		<h2>Layouts</h2>

		<div>
			<ul id="modules_livethemes">
				$activethemes
			</ul>
		</div>
                 </div>


EOD;

global $color;
global $colors;

$colorbox = '';
foreach($colors as $colorname => $val){
	$colordetails = unserialize($val[$colorname]);
	$colorbox .= '<div id="'.$colorname.'_'.$colordetails['primary'].'" style="background:#'.$colordetails['primary'].'" class="colorbox"><div class="tick" id="tick_'.$colorname.'_'.$colordetails['primary'].'"><img src="images/check.svg"/></div> </div>';
}
$colorval = unserialize($colors[$color][$color]);

$newcolorform = '';
$js = '';

$newcolorform .= '<div class="titlesmall" style="padding-top:14px;" >Primary Color</div><div class="element">';
$newcolorform .= '<input type="text" class="inputbox themevariables" id="primary_field" name="primary" value="#'.$colorval['primary'].'" style="width: 100px;height:28px" required="true"/>';
$newcolorform .= '<div class="colorSelector themeSettings" field="primary" id="primary" oldcolor="#'.$colorval['primary'].'" newcolor="#'.$colorval['primary'].'" ><div style="background:#'.$colorval['primary'].'" style="float:right;margin-left:10px"></div></div>';
$newcolorform .= '</div><div style="clear:both;padding:7px;"></div>';
$newcolorform .= '<div class="titlesmall" style="padding-top:14px;" >Dark Color</div><div class="element">';
$newcolorform .= '<input type="text" class="inputbox themevariables" id="secondary_field" name="secondary" value="#'.$colorval['secondary'].'" style="width: 100px;height:28px" required="true"/>';
$newcolorform .= '<div class="colorSelector themeSettings" field="secondary" id="secondary" oldcolor="#'.$colorval['secondary'].'" newcolor="#'.$colorval['secondary'].'" ><div style="background:#'.$colorval['secondary'].'" style="float:right;margin-left:10px"></div></div>';
$newcolorform .= '</div><div style="clear:both;padding:7px;"></div>';
$newcolorform .= '<div class="titlesmall" style="padding-top:14px;" >Menu Hover Color</div><div class="element">';
$newcolorform .= '<input type="text" class="inputbox themevariables" id="hover_field" name="hover" value="#'.$colorval['hover'].'" style="width: 100px;height:28px" required="true"/>';
$newcolorform .= '<div class="colorSelector themeSettings" field="hover" id="hover" oldcolor="#'.$colorval['hover'].'" newcolor="#'.$colorval['hover'].'" ><div style="background:#'.$colorval['hover'].'" style="float:right;margin-left:10px"></div></div>';


$js .= <<<EOD
$('#primary').ColorPicker({
	color: '#{$colorval['primary']}',
	onShow: function (colpkr) {
		$(colpkr).fadeIn(500);
		return false;
	},
	onHide: function (colpkr) {
		$(colpkr).fadeOut(500);
		return false;
	},
	onChange: function (hsb, hex, rgb) {
		$('#primary div').css('backgroundColor', '#' + hex);
		$('#primary').attr('newcolor','#'+hex);
		document.getElementById('primary_field').setAttribute('value','#'+hex);
		$('#primary_field').trigger("change");
	}
});

$('#secondary').ColorPicker({
	color: '#{$colorval['secondary']}',
	onShow: function (colpkr) {
		$(colpkr).fadeIn(500);
		return false;
	},
	onHide: function (colpkr) {
		$(colpkr).fadeOut(500);
		return false;
	},
	onChange: function (hsb, hex, rgb) {
		$('#secondary div').css('backgroundColor', '#' + hex);
		$('#secondary').attr('newcolor','#'+hex);
		document.getElementById('secondary_field').setAttribute('value','#'+hex);
		$('#secondary_field').trigger("change");
	}
});

$('#hover').ColorPicker({
	color: '#{$colorval['hover']}',
	onShow: function (colpkr) {
		$(colpkr).fadeIn(500);
		return false;
	},
	onHide: function (colpkr) {
		$(colpkr).fadeOut(500);
		return false;
	},
	onChange: function (hsb, hex, rgb) {
		$('#hover div').css('backgroundColor', '#' + hex);
		$('#hover').attr('newcolor','#'+hex);
		document.getElementById('hover_field').setAttribute('value','#'+hex);
		$('#hover_field').trigger("change");
	}
});

EOD;


$body .= <<<EOD
			<script type="text/javascript">
				$(document).ready(function(){
					$('#{$color}_{$colorval['primary']}').find('.tick').css('display','table');
					$('#{$color}_{$colorval['primary']}').find('.tick').addClass('selected');
					$('.colorbox').click(function(){
						$('.tick').css('display','none');
						$('.tick').removeClass('selected');
						$('#'+this.id).find('.tick').css('display','table');
						$('#'+this.id).find('.tick').addClass('selected');
					});
					$('#submit_color').click(function(){
						var tickclass = $('.tick');
						$.each(tickclass,function(i,val){
							if($('#'+this.id).hasClass('selected')){
								var name = this.id.split('_');
								$("#color_text").val(name[1]);
							}
						});
					});
					$('#addcoloroption').click(function(){
						$('#addcolorcontent').slideDown();
					});
					$('#hideaddcolor').click(function(){
						$('#addcolorcontent').slideUp();
					});
				});

				$(function() { $js });
			</script>
			<div class="margin-top">
				<h2>Colors</h2>
				<div style="width:400px;"class="outerbox">
					{$colorbox}
				</div>
				<form action="?module=layouts&action=updatecolorval&ts={$ts}" method="post">
				<input id="color_text" type="hidden" name="color"/>
				<input type="submit" id="submit_color" class="button" value="Update Color">&nbsp;&nbsp;or <span id="addcoloroption" style="text-decoration:underline;cursor:pointer;">Add New Color</span>
				</form>
				<div style="clear:both;padding:15px;"></div>
				<div id="addcolorcontent" style="display:none;">
					<h2>Add New Color</h2>
					<div style="clear:both;padding:7px;"></div>
					<form action="?module=layouts&action=addnewcolor&ts={$ts}" method="post">
					<div>
						<div id="centernav" style="width:700px">
							{$newcolorform}
						</div>
					</div>
					<div style="clear:both;padding:7px;"></div>
					<input type="submit" id="add_color" class="button" value="Add Color">&nbsp;&nbsp;or <span id="hideaddcolor" style="text-decoration:underline;cursor:pointer;">cancel</span>
					</form>
				</div>
			</div>
EOD;

	template();
}

function updatecolorval(){
	$color = $_POST['color'];
	global $ts;
	configeditor(array('color'=>$color));
	$_SESSION['cometchat']['error'] = 'Color updated successfully.';
	header('Location:?module=layouts&ts='.$ts);
}

function addnewcolor(){
	global $ts;
	global $colors;
	global $client;
	$primary = $_POST['primary'];
	$secondary = $_POST['secondary'];
	$hover = $_POST['hover'];

	if(substr($primary,0,1) == '#' && substr($secondary,0,1) == '#' && substr($hover,0,1) == '#'){
		$primary = substr($primary,1);
		$secondary = substr($secondary,1);
		$hover = substr($hover,1);
		$colordetails = array('primary' => $primary, 'secondary' => $secondary, 'hover' => $hover);
		$colorvalue = serialize($colordetails);
		$colorname = 'color9'.mysqli_real_escape_string($GLOBALS['dbh'],$ts);

		foreach($colors as $name => $val){
			if($val[$name] == $colorvalue) {
				$_SESSION['cometchat']['error'] = 'Color already exists';
				header("Location:?module=layouts&ts={$ts}");
			}
		}
		$sql = ("insert into `cometchat_colors`(`color_key`,`color_value`,`color`) values ('".$colorname."','".mysqli_real_escape_string($GLOBALS['dbh'],$colorvalue)."','".$colorname."')"); 
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		$_SESSION['cometchat']['error'] = 'New color added successfully';
		removeCachedSettings($client.'cometchat_color');
		header("Location:?module=layouts&ts={$ts}");
	}
}

function removecolorprocess() {
    global $ts;
    global $client;
	$color = $_GET['data'];
	$color_array = array('docked','embedded');

	if (!in_array($color, $color_array) && !empty($color)) {
		$sql = ("delete from `cometchat_colors` where `color` = '".mysqli_real_escape_string($GLOBALS['dbh'],$color)."'");
		mysqli_query($GLOBALS['dbh'],$sql);
		removeCachedSettings($client.'cometchat_color');
		$_SESSION['cometchat']['error'] = 'Color scheme deleted successfully';
	} else {
		$_SESSION['cometchat']['error'] = 'Sorry, this color scheme cannot be deleted. Please manually remove the theme from the "themes/color" folder.';
	}
	header("Location:?module=layouts&ts={$ts}");
}
