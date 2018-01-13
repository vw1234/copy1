<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

function themeslist() {
	$themes = array();

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'cometchat.css')) {
				$themes[] = $file;
			}
		}
		closedir($handle);
	}


	return $themes;
}

function configeditor ($config) {	
	global $dbh;
	global $client;
	global $writable;
	$insertvalues = '';
	$key_type;
	foreach ($config as $name => $value) {
		if($name == strtoupper($name)){
			$key_type = 0;
		}else if(!is_array($value)){
			$key_type = 1;
		}else{
			$key_type = 2;
			$value = serialize($value);
		}
		$insertvalues .= ("('".mysqli_real_escape_string($dbh,$name)."', '".mysqli_real_escape_string($dbh,$value)."', {$key_type}),");
	}
	$insertvalues = rtrim($insertvalues,',');
	if(!empty($insertvalues)){
		$sql = ("replace into `cometchat_settings` (`setting_key`,`value`, `key_type`) values ".$insertvalues);
		$query = mysqli_query($dbh,$sql);
	}
	removeCachedSettings($client.'settings');
	if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.$writable)){
		clearcache(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.$writable);
	}
	if(function_exists('purgecache')) {
		purgecache($client);
	}
}

function cc_mail( $to, $subject, $message, $headers, $attachments = array() ) {
    if ( ! is_array( $attachments ) ) {
        $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
    }
    global $phpmailer;

    if ( ! ( $phpmailer instanceof PHPMailer ) ) {        
        if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."functions".DIRECTORY_SEPARATOR."mail".DIRECTORY_SEPARATOR."class-phpmailer.php")){
            include_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."functions".DIRECTORY_SEPARATOR."mail".DIRECTORY_SEPARATOR."class-phpmailer.php");
            include_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."functions".DIRECTORY_SEPARATOR."mail".DIRECTORY_SEPARATOR."class-smtp.php");
        }
        $phpmailer = new PHPMailer( true );
    }
    $cc = $bcc = $reply_to = array();
    if ( empty( $headers ) ) {
        $headers = array();
    } else {
        if ( !is_array( $headers ) ) {
            $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
        } else {
            $tempheaders = $headers;
        }
        $headers = array();
        if ( !empty( $tempheaders ) ) {            
            foreach ( (array) $tempheaders as $header ) {
                if ( strpos($header, ':') === false ) {
                    if ( false !== stripos( $header, 'boundary=' ) ) {
                        $parts = preg_split('/boundary=/i', trim( $header ) );
                        $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                    }
                    continue;
                }                
                list( $name, $content ) = explode( ':', trim( $header ), 2 );
                $name    = trim( $name    );
                $content = trim( $content );

                switch ( strtolower( $name ) ) {                   
                    case 'from':
                    $bracket_pos = strpos( $content, '<' );
                    if ( $bracket_pos !== false ) {                            
                        if ( $bracket_pos > 0 ) {
                            $from_name = substr( $content, 0, $bracket_pos - 1 );
                            $from_name = str_replace( '"', '', $from_name );
                            $from_name = trim( $from_name );
                        }

                        $from_email = substr( $content, $bracket_pos + 1 );
                        $from_email = str_replace( '>', '', $from_email );
                        $from_email = trim( $from_email );
                    } elseif ( '' !== trim( $content ) ) {
                        $from_email = trim( $content );
                    }
                    break;
                    case 'content-type':
                    if ( strpos( $content, ';' ) !== false ) {
                        list( $type, $charset_content ) = explode( ';', $content );
                        $content_type = trim( $type );
                        if ( false !== stripos( $charset_content, 'charset=' ) ) {
                            $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                        } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                            $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                            $charset = '';
                        }

                    } elseif ( '' !== trim( $content ) ) {
                        $content_type = trim( $content );
                    }
                    break;
                    case 'cc':
                    $cc = array_merge( (array) $cc, explode( ',', $content ) );
                    break;
                    case 'bcc':
                    $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                    break;
                    case 'reply-to':
                    $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                    break;
                    default:                      
                    $headers[trim( $name )] = trim( $content );
                    break;
                }
            }
        }
    }

    $phpmailer->ClearAllRecipients();
    $phpmailer->ClearAttachments();
    $phpmailer->ClearCustomHeaders();
    $phpmailer->ClearReplyTos();

    if ( !isset( $from_name ) )
        $from_name = 'bounce ';     

    $phpmailer->setFrom( $from_email, $from_name, false );
    if ( !is_array( $to ) )
        $to = explode( ',', $to );
    $phpmailer->Subject = $subject;
    $phpmailer->Body    = $message;
    $address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );
    foreach ( $address_headers as $address_header => $addresses ) {
        if ( empty( $addresses ) ) {
            continue;
        }

        foreach ( (array) $addresses as $address ) {
            try {                
                $recipient_name = '';
                if ( preg_match( '/(.*)<(.+)>/', $address, $matches ) ) {
                    if ( count( $matches ) == 3 ) {
                        $recipient_name = $matches[1];
                        $address        = $matches[2];
                    }
                }

                switch ( $address_header ) {
                    case 'to':
                    $phpmailer->addAddress( $address, $recipient_name );
                    break;
                    case 'cc':
                    $phpmailer->addCc( $address, $recipient_name );
                    break;
                    case 'bcc':
                    $phpmailer->addBcc( $address, $recipient_name );
                    break;
                    case 'reply_to':
                    $phpmailer->addReplyTo( $address, $recipient_name );
                    break;
                }
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }

    $phpmailer->IsMail();

    if ( !isset( $content_type ) )
        $content_type = 'text/plain';

    $phpmailer->ContentType = $content_type; 
    
    if ( 'text/html' == $content_type )
        $phpmailer->IsHTML( true );
    
    if ( !empty( $headers ) ) {
        foreach ( (array) $headers as $name => $content ) {
            $phpmailer->AddCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
        }

        if ( false !== stripos( $content_type, 'multipart' ) && ! empty($boundary) )
            $phpmailer->AddCustomHeader( sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ) );
    }

    if ( !empty( $attachments ) ) {
        foreach ( $attachments as $attachment ) {
            try {
                $phpmailer->AddAttachment($attachment);
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }

    try {
        return $phpmailer->Send();
    } catch ( phpmailerException $e ) {
        $mail_error_data = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
        $mail_error_data['phpmailer_exception_code'] = $e->getCode(); 
        return false;
    }
}
function languageeditor($lang){
	global $dbh;
	global $client;
	global $writable;
	if(empty($lang['lang_key']) || empty($lang['name']) || empty($lang['code']) || empty($lang['type'])){
		return 0;
	}
	$sql = ("insert into `cometchat_languages` set `lang_key` = '".mysqli_real_escape_string($dbh,$lang['lang_key'])."', `lang_text` = '".mysqli_real_escape_string($dbh,$lang['lang_text'])."', `code` = '".mysqli_real_escape_string($dbh,$lang['code'])."', `type` = '".mysqli_real_escape_string($dbh,$lang['type'])."', `name` = '".mysqli_real_escape_string($dbh,$lang['name'])."' on duplicate key update `lang_key` = '".mysqli_real_escape_string($dbh,$lang['lang_key'])."', `lang_text` = '".mysqli_real_escape_string($dbh,$lang['lang_text'])."', `code` = '".mysqli_real_escape_string($dbh,$lang['code'])."', `type` = '".mysqli_real_escape_string($dbh,$lang['type'])."', `name` = '".mysqli_real_escape_string($dbh,$lang['name'])."'");
	$query = mysqli_query($dbh,$sql);
	removeCachedSettings($client.'cometchat_language');
	if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.$writable)){
		clearcache(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.$writable);
	}
}

function coloreditor($data,$color_name){
	global $dbh;
	global $client;
	global $writable;
	$insertvalues = '';
	foreach ($data as $name => $value) {
		$insertvalues .= ("('".mysqli_real_escape_string($dbh,$name)."', '".mysqli_real_escape_string($dbh,$value)."', '".mysqli_real_escape_string($dbh,$color_name)."'),");
	}
	$insertvalues = rtrim($insertvalues,',');
	if(!empty($insertvalues)){
		$sql = ("replace into `cometchat_colors` (`color_key`,`color_value`, `color`) values ".$insertvalues);
		$query = mysqli_query($dbh,$sql);
	}
	removeCachedSettings($client.'cometchat_color');
	if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.$writable)){
		clearcache(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.$writable);
	}
}

function createslug($title,$rand = false) {
	$slug = preg_replace("/[^a-zA-Z0-9]/", "", $title);
	if ($rand) { $slug .= rand(0,9999); }
	return strtolower($slug);
}

function extension($filename) {
	return pathinfo($filename, PATHINFO_EXTENSION);
}

function deletedirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!deleteDirectory($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!deleteDirectory($dir . "/" . $item)) return false;
            };
        }
    return rmdir($dir);
}

function pushMobileAnnouncement($zero,$sent,$message,$isAnnouncement = '0',$insertedid){
	global $userid;
	global $lang;

	if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."FireBasePushNotification.php")){
		include_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."FireBasePushNotification.php");
		include_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."config.php");

		$announcementpushchannel = '';

		if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."announcements".DIRECTORY_SEPARATOR."config.php")){
			include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."announcements".DIRECTORY_SEPARATOR."config.php");
			include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."announcements".DIRECTORY_SEPARATOR."lang.php");
		}

		if(!empty($isAnnouncement)){
			$rawMessage = array("m" => $announcements_language['announces'].": ".$message, "sent" => $sent, "id" => $insertedid);
		}

		$pushnotifier = new FireBasePushNotification($firebaseauthserverkey,array('app_title' => $app_title));
		$pushnotifier->sendNotification($announcementpushchannel, $rawMessage, 0, 1);
	}
}

$getstylesheet = <<<EOD
<body><title>CometChat</title></body>
<style>
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, font, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td {
	margin: 0;
	padding: 0;
	border: 0;
	outline: 0;
	font-weight: inherit;
	font-style: inherit;
	font-size: 100%;
	font-family: inherit;
	vertical-align: baseline;
}
body {font-size: 10px; font-family: arial, san-serif;}
html {
	 overflow-y: scroll;
	 overflow-x: hidden;
}
#content {
	-moz-border-radius-bottomleft:5px;
	-moz-border-radius-bottomright:5px;
	-moz-border-radius-topleft:5px;
	-moz-border-radius-topright:5px;
	background-color:#EEEEEE;
	width:350px;
	padding:10px;
	margin:0;
}
form{
	padding: 20px !important;
}
h1{
	color:#333333;
	font-size:110%;
	padding-left:10px;
	padding-bottom:10px;
	padding-top:5px;
	font-weight:bold;
	border-bottom:1px solid #ccc;
	margin-bottom:10px;
	margin-left:10px;
	margin-right:10px;
	text-transform: uppercase;
}
h2 {
	color:#333333;
	font-size:160%;
	font-weight:bold;
}

h3 {
	color:#333333;
	font-size:110%;
	border-bottom:1px solid #ccc;
	padding-bottom:10px;
	margin-bottom:17px;
	padding-top:4px;
}
.button {
	border:1px solid #76b6d2;
	padding:4px;
	background:#76b6d2;
	color:#fff;
	font-weight:bold;
	font-size:10px;
	font-family:arial;
	text-transform:uppercase;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	padding-left:10px;
	padding-right:10px;
}
.inputbox {
	border:1px solid #ccc;
	padding:4px;
	background:#fff;
	color:#333;
	font-weight:bold;
	font-size:10px;
	font-family:arial;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	padding-left:10px;
	padding-right:10px;
	width: 200px;
}
.title {
	padding-top: 4px;
	text-align: right;
	padding-right:10px;
	font-size: 12px;
	width: 100px;
	float:left;
	color: #333;
}

.long {
	width: 200px;
}
.short {
	width: 100px;
}
.toppad {
	margin-top:7px;
}
.element {
	float:left;
}
a {
	color: #0f5d7e;
}

form #centernav {
	width: 475px !important;
}

form #content {
	margin: 0 !important;
}
form #centernav .titlelong {
	width: 210px !important;
}
form #centernav .title {
	width: 210px !important;
}
</style>
<link href="../css.php?admin=1" media="all" rel="stylesheet" type="text/css" />
EOD;
?>
