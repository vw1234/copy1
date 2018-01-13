<?php

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }
global $getstylesheet;
global $theme;

if(!empty($_GET['process']) && $_GET['process'] == true) {
    include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');
    $embed_code = '<link type="text/css" href="'.BASE_URL.'cometchatcss.php" rel="stylesheet" charset="utf-8">'."\r\n".'<script type="text/javascript" src="'.BASE_URL.'cometchatjs.php" charset="utf-8"></script>';
    echo <<<EOD
        <!DOCTYPE html>
        <html>
            <head>
                <script type="text/javascript" src="../js.php?admin=1"></script>
                <script type="text/javascript" language="javascript">
                    $(function() {
                        setTimeout(function(){
                            resizeWindow();
                        },200);
                    });

                    function resizeWindow() {
                        window.resizeTo((520), (190+window.outerHeight-window.innerHeight));
                    }
                </script>
                <style>textarea { border:1px solid #ccc; color: #333; font-family:verdana; font-size:12px; }</style>
            </head>
        <body style='overflow:hidden'>
            <textarea readonly="" style="width:500px;height:170px">{$embed_code}</textarea>
        </body>
        </html>
EOD;

}
?>
