<?php
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){
	$.ccbots = (function () {
		var title = 'Bots Extension';

        return {
			getTitle: function() {
				return title;
			},
			init: function () {
				var baseUrl = $.cometchat.getBaseUrl();


			}
        };
    })();
})(jqcc);
