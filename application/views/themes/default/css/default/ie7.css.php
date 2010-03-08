<?php if(extension_loaded('zlib')){ob_start('ob_gzhandler');} header('Content-type: text/css; charset: UTF-8'); ?>
#content{padding-left: 1%}
table{border-collapse:collapse}
#menu ul li.hr{border-bottom:1px solid #aaaaaa;height:1px;margin:0px; margin-top: -12px;display:none}
#login-table hr {margin: 0px;}
.w98 { width: 99%;}
div.left {float: left;margin-left: 0px;}
div.right {float: none; position: absolute; right: 0px;}
.w49 { padding-right: 10px }
.service-totals {margin-top:-75px;}

.icon {padding: 0px 7px;max-width: 16px;}
.icon img {margin: 0px;}
input[type=text],
select {
	border:  1px solid #cdcdcd;
}
#service_table tr.even td.white,
#service_table tr.odd td.white {
	border-right: 1px solid #d0d0d0;
	border: 0px;
}
#service_table tr.even td.white:first-child,
#service_table tr.odd td.white:first-child {
	border: 0px;
}
#service_table tr.even td.service_hostname.w80.bt,
#service_table tr.odd td.service_hostname.w80.bt {
	border: 1px solid #d0d0d0;
}
p.pagination { padding-top: 3px;}
#extinfo_info,#nagios_commands { padding-left: 1%;}
#filters{height: 130px;}

<?php if(extension_loaded('zlib')){ob_end_flush();}?>