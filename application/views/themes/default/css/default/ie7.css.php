<?php if(extension_loaded('zlib')){ob_start('ob_gzhandler');} header('Content-type: text/css; charset: UTF-8'); ?>
#content{padding-left: 1%}
table{border-collapse:collapse}
#menu ul li.hr{border-bottom:1px solid #aaaaaa;height:1px;margin:0px; margin-top: -12px}
#login-table hr {margin: 0px;}
<?php if(extension_loaded('zlib')){ob_end_flush();}?>