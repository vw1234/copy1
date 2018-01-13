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

foreach ($screenshare_language as $i => $l) {
	$screenshare_language[$i] = str_replace("'", "\'", $l);
}

?>

function setupApp()
{
	screenViewer = document.getElementById("screenViewerID");

}

function stopApp()
{
	if (screenViewer != null)
	{
		if (typeof screenViewer.windowCloseEvent == 'function') {
			screenViewer.windowCloseEvent();
		} else {
			screenViewer.remove();
		}
	}
}

function getParameter(string, parm, delim) {

	 if (string.length == 0) {
		return '';
	 }

	 var sPos = string.indexOf(parm + "=");

	 if (sPos == -1) {return '';}

	 sPos = sPos + parm.length + 1;
	 var ePos = string.indexOf(delim, sPos);

	 if (ePos == -1) {
		ePos = string.length;
	 }

	 return unescape(string.substring(sPos, ePos));
}

function getPageParameter(parameterName, defaultValue) {

	var s = self.location.search;

	if ((s == null) || (s.length < 1)) {
		return defaultValue;
	}

	s = getParameter(s, parameterName, '&');

	if ((s == null) || (s.length < 1)) {
		s = defaultValue;
	}

	return s;
}