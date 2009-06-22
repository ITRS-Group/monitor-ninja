<?php if(extension_loaded('zlib')){ob_start('ob_gzhandler');} header('Content-type: text/css; charset: UTF-8'); ?>
* {
	text-decoration: none;
	font-size: 1em;
	outline: none;
	padding: 0;
	margin: 0;
}
code, kbd, samp, pre, tt, var, textarea,
input, select, isindex, listing, xmp, plaintext {
	white-space: normal;
	font-size: 1em;
	font: inherit;
}
dfn, i, cite, var, address, em {
	font-style: normal;
}
th, b, strong, h1, h2, h3, h4, h5, h6 {
	font-weight: normal;
}
a, img, a img, iframe, form, fieldset,
abbr, acronym, object, applet, table {
	border: none;
}
table {
	/*border-collapse: collapse;*/
	border-spacing: 0;
}
caption, th, td, center {
	vertical-align: top;
	text-align: left;
}
body {
	background: white;
	line-height: 1;
	color: black;
}
q {
	quotes: "" "";
}
ul, ol, dir, menu {
	list-style: none;
}
sub, sup {
	vertical-align: baseline;
}
a {
	color: inherit;
}
hr {
	display: none;
}
font {
	color: inherit !important;
	font: inherit !important;
}
marquee {
	overflow: inherit !important;
	-moz-binding: none;
}
blink {
	text-decoration: none;
}
nobr {
	white-space: normal;
}
/**************************************************************************************************/
html, body {
	height: 100%;
	color: #414141;
}
body {
	margin: 0px;
	font: 11px 'trebuchet ms',arial,sans-serif;
	background-color: #ffffff;
}
td { vertical-align: middle; }
strong { font-weight: bold; }
a { color: #414141; }
cite {font-style: normal;}
title {	text-transform: capitalize;}
ul.commands {
	margin: 0px;
	padding: 0px;
}
ul.commands li {
	line-height: 20px;
	list-style: none;
}
ul.commands li img {
	margin-right: 3px;
	margin-bottom: -2px;
	height: 14px;
}
/**************************************************************************************************/
cite em,
cite em:first-child {
	background: #ffffff url(images/input-bg-right.png) no-repeat;
	height: 29px;
	width: 8px;
	float: left;
	border: 0px;
	margin-bottom: -1px;
}
cite em:first-child {
	background: #ffffff url(images/input-bg-left.png) no-repeat;
	margin-left: -4px;
}
input[type=submit],
input[type=reset],
input[type=button] {
	font: 11px 'trebuchet ms',arial,sans-serif;
	border: 1px solid #ffffff;
	outline:  1px solid #cdcddc;
	background: transparent url(images/button-bg.png) repeat-x;
	padding: 1px 5px;
}
input[type=text] {
	font: 11px 'trebuchet ms',arial,sans-serif;
	border: 1px solid #ffffff;
	border:  1px solid #cdcddc;
	padding: 2px 5px;
	margin-left: 2px;
}
input.text {
	border: 0px;
	outline: 0px;
	background: #ffffff url(images/input-bg.png) repeat-x;
	font: 11px 'trebuchet ms',arial,sans-serif;
	padding: 7px 0px;
	float: left;
	margin-bottom: -1px;
	margin-left: 0px;
}
select {
	font: 11px 'trebuchet ms',arial,sans-serif;
	border: 1px solid #ffffff;
	outline:  1px solid #cdcddc;
	padding: 0px;
	margin-left: 3px;
}
/* login ****************************************************************************************/
#login-table {
	position: relative;
	margin: 150px auto;
	width: 240px;
	text-align: center;
	background: transparent url(images/login.png) no-repeat top center;
	padding-top: 66px;
}
#login-table table {
	width: 100%;
}
#login-table table tr td {
	background: #ffffff;
	padding: 5px;
}
#login-table hr {
	color: #dcdccd;
	background-color: #dcdccd;
	height: 1px;
	border: 0px;
}
/* content ****************************************************************************************/
#top-bar {
	position: relative;
	position: fixed;
	left: 0px;
	height: 32px;
	width: 100%;
	background: #ffffff url(images/menu-top.gif) repeat-x;
	z-index: 1;
	border-left: 1px solid #e9e9e0;
	border-right: 1px solid #e9e9e0;
}
#top-bar img {
	margin-top: 7px;
	margin-left: 7px;
}
#navigation {
	margin-top: -30px;
	margin-left: 27px;
	font-weight: bold;
}
#navigation ul {
	list-style: none;
	margin: 1px 0px 0px 0px;
	padding: 0px;
	width: auto;
}
#navigation ul li {
	background: url(images/menu-arrow.gif) no-repeat;
	margin-left: 5px;
	padding-left: 13px;
	float: left;
	height: 32px;
}
#navigation a {
	margin-left: 10px;
	margin-top: 9px;
	color: #000000;
	text-decoration: none;
	font-weight: bold;
	float: left;
	text-transform: capitalize;
}
#navigation p {
	margin: 0px;
	font-weight: normal;
	float: right;
	margin-top: 2px;
	color: #555555;
	margin-right: 10px;
	margin-top: 7px
}
#navigation p a {
	float: none;
	font-weight: normal;
	font-size: 11px;
	margin: 0px;
	color: #414141;
	border-bottom: 1px dotted #777777;
}
#navigation img {
	margin-top: 1px;
	float: left;
}
#navigation img:first-child {
	margin-left: 5px;
}
#navigation input {
	float: right;
	border: 0px;
	outline: 0px;
	margin-top: 5px;
	margin-right: 4px;
	font: 11px 'trebuchet ms';
	background: transparent url(images/input-170.png) no-repeat;
	width: 138px;
	padding: 3px 5px 3px 27px;
	color: #999999;
}
#quickbar {
	position: relative;
	position: fixed;
	margin: 0px auto;
	height: 34px;
	width: 100%;
	top: 32px;
	left: 0px;
	background: #ffffff url(images/menu-top-two.gif) repeat-x;
	z-index: 1;
	border-left: 1px solid #e9e9e0;
	border-right: 1px solid #e9e9e0;
}
#quicklinks ul {
	padding: 9px 10px;
	margin: 0px;
	float: left;
}
#quicklinks ul li {
	display: inline;
	padding: 0px 10px 0px 5px;
	background: url(images/menu-divider.gif) top right no-repeat;
	font-weight: bold;
}
#icons {
	position: absolute;
	right: 0px;
	top: 1	px;
	z-index: 9999;
}
#icons ul {
	height: 32px;
}
#icons ul li {
	display: inline;
	margin-left: 0px;
	float: right;
	background: url(images/menu-divider.gif) 0px 0px no-repeat;
	padding: 8px 12px;
	cursor: pointer;
}
#status {
	float: right;
	margin-top: 9px;
	margin-right: 10px;
	font-style: italic;
	color: #555555;
}
#menu {
	position: absolute;
	position: fixed;
	width: 165px;
	top: 0px;
	left: 0px;
	z-index: 0;
	height: 100%;
	background-color: #f5f5ef;
	border-left: 1px solid #e9e9e0;
}
#menu ul {
	margin: 0px;
	padding: 0px;
	margin-top: 66px;
	border-right: 1px solid #e9e9e0;
}
#menu ul li {
	list-style: none;
	padding: 3px 0px 2px 10px;
	margin: 0px;
	background: #ffffff url(images/menu-top-two.gif) 0px -1px repeat-x;
	border-bottom: 1px solid #e9e9e0;
	height: 14px;
}
#menu ul li.selected {
	font-weight: bold;
}
#menu ul li.hr {
	height: 0px;
	background: #e9e9e0;
	border-bottom: 1px solid #dcdccd;
	padding: 0px;
}
#menu ul li img {
	width: 14px;
	height: 14px;
	margin-top: 1px;
	float: left;
	margin-right: 10px;
}
#menu ul li a {
	text-decoration: none;
}
#menu ul li.header {
	font-weight: bold;
	padding-top: 15px;
	padding-left: 15px;
}
#close-menu {
	position: absolute;
	top: 75px;
	left: 145px;
	background: transparent url(images/menu-close.png) no-repeat;
	width: 14px;
	height: 14px;
	z-index: 2;
	cursor: pointer;
}
#show-menu {
	position: absolute;
	top: 75px;
	left: 10px;
	background: transparent url(images/menu-show.png) no-repeat;
	width: 14px;
	height: 14px;
	z-index: 2;
	cursor: pointer;
	display: none;
}
#content {
	position: relative;
	top: 64px;
	margin: 0px 0px 0px 166px;
	padding-top: 11px;
	padding-top: 0px;
}
#page_settings {
	position: absolute;
	top: 65px;
	right: 0px;
	padding: 0px;
	border: 1px solid #e9e9e0;
	background-color: #f5f5ef;
	display: none;
	z-index: 9999;
}
#page_settings ul {
	margin: 0px;
	padding: 0px;
}
#page_settings ul li {
	list-style: none;
	border-bottom: 1px solid #e9e9e0;
	padding: 3px 10px;
	background: #ffffff url(images/menu-top-two.gif) 0px -1px repeat-x;
	cursor: pointer;
}
#page_settings ul li.header {
	font-weight: bold;
	padding-top: 15px;
	cursor: default;
}
#page_settings ul li.selected {
	list-style-image: url(images/checkbox-selected.png);
	list-style-position: inside;
}
#page_settings ul li.unselected {
	list-style-image: url(images/checkbox.png);
	list-style-position: inside;
}
#page_settings ul li a {
	text-decoration: none;
	border-bottom: 1px dotted #777777;
}
#page_links ul {
	margin: 0px;
	padding: 0px;
}
#page_links ul li {
	list-style: none;
	line-height: 17px;
	background: url(images/arrow.gif) 0px 7px no-repeat;
	padding-left: 10px;
}
/* pagination *************************************************************************************/
#content p.pagination {
	float: right;
	margin-top: 3px;
	margin-right: 10px;
}
#status_count_summary {
	float: left;
}
#content form.pagination_form {
	float: right;
}
/**************************************************************************************************/
.error_message {
	margin-left: 1%;
	font-weight: bold;
	margin-bottom: 10px;
	font-size: 12px;
}
.error_description {
	margin-left: 1%;
	width: 550px;
}
/* generic classes ********************************************************************************/
table {
	border-spacing: 0px;
	margin: 0px;
	width: 100%;
}
caption {
	font-size: 11px;
	color: #333333;
	font-weight: bold;
	text-align: left;
	padding: 2px 0px 4px 4px;
}
th {
	text-align: left;
	padding: 5px 7px;
	border: 0px;
	font-weight: bold;
	border: 0px;
	padding: 3px 7px;
	background: #ffffff url(images/td-light.gif) repeat-x;
	text-transform: uppercase;
}
tr td {
	background: #ffffff url(images/td-light.gif) repeat-x;
	padding: 0px 5px;
	border: 0px;
	text-align:left;
	white-space: nowrap;
}
tr td.dark  {
	width: 40px;
	background: #f5f5ef url(images/td-dark.gif) repeat-x;
	text-align: center;
	padding: 5.50px 0px;
}
tr td.white {
	padding: 0px;
	background: #ffffff;
	vertical-align: top;
}
/* health css *************************************************************************************/
table.healt {
	border: 1px solid #dcdccd;
	border-spacing: 1px;
	background-color: #ffffff;
	margin-top: -1px;
}
table.healt tr td {
	background: none;
	border: 0px;
	text-align: left;
	padding: 0px;
	width: 50%;
}
table.healt tr td div.border {
	height: 187px;
	height: 100px;
}
table.healt tr td:first-child div.border {
	margin-right: 0px;
}
table.healt tr td div.border img {
	height: 187px;
	height: 100px;
}
table.healt tr td div.border {
	padding: 0px;
	background: url(images/td-light.gif) repeat-x;
}
table.healt tr td div.border img {
	display: block;
	margin-top: 0px;
	margin-bottom: 0px;
	height: 30px;
}
/* extinfo table **********************************************************************************/
table.extinfo {
	border-spacing: 1px;
	background-color: #dcdccd;
	margin-top: -1px;
}
table.extinfo tr td {
	padding: 2px 5px;
}
table.extinfo tr td.status.icon {
	width: 11px;
}
table.extinfo tr td img {
	margin-bottom: -2px;
	margin-right: 2px;
}
table.extinfo tr td.status.icon img {
	margin: 1px;
	height: 14px;
}
/* widgdet style **********************************************************************************/
.widget-header {
	font-size: 11px;
	color: #333333;
	font-weight: bold;
	text-align: left;
	padding: 3px 5px 3px 5px;
	border: 1px solid #e9e9e0;
	border: 1px solid #dcdccd;
	height: 14px;
}
.widget-header span {
	float: left;
}
.widget-header span img {
	float: left;
}
.widget-header span.widget-menu {
	float: right;
	margin-top: 1px;
}
.widget-header span.widget-menu img {
	margin-left: 3px;
}
.widget {
	margin-left: 1%;
}
.widget-place {
	clear: both;
}
.widget-place.col {
	clear: none;
}
.widget-placeholder {
  border: 1px dashed #cdcdbc;
}
/**************************************************************************************************/
div.left {
	float: left;
	margin-top: 11px;
}
div.right {
	float: right;
	margin-top: 11px;
}
.max { width: 100%; }
.w99 { width: 99%; }
.w98 { width: 98%; }
.w66 { width: 65%; }
.w49 { width: 49%; }
.w48 { width: 48%; }
.w33 { width: 32%; }
.w32 { width: 32%; }
.w18 { width: 18%; }
.w19 { width: 19%; }
/* status *****************************************************************************************/
.status  {
	background: #f5f5ef url(images/td-dark.gif) repeat-x;
	padding: 5px;
	height: 16px;
}
.icon {
	width: 16px;
	padding: 2px 7px;
	text-align: center;
	vertical-align: middle;
}
#widget-status_totals .icon,
#widget-host_totals .icon {
	width: 11px;
}
tr.odd td.bl,
tr.even td.bl { border-left: 1px solid #dcdccd; }
tr.odd td.bt,
tr.even td.bt { border-top: 1px solid #dcdccd; }
tr.odd td.bt img,
tr.even td.bt img { float: left }
td.icon a { border: 0px; }
th.white,
#service_table tr.even td.white,
#service_table tr.odd td.white {
	background: #ffffff;
	border: 1px solid #ffffff;
	padding: 0px;
}
tr.even td.w80,
tr.odd td.w80 { width: 80px; border-right: 0px;  }
#content a {
	text-decoration: none;
	border-bottom: 1px dotted #777777;
}
th a { border-bottom: 0px; }
img { border-bottom: 0px; }
a > img { border: 0px; }
#status_count_summary {
	margin-top: 7px;
	font-style: italic;
	margin-left: 5px;
}
tr.even td {
	border: 1px solid #dcdccd;
	border-left: 0px;
	border-top: 0px;
	background: #fdfdfd;
	padding-top: 2px;
	padding-bottom: 2px;
}
tr.odd td {
	border: 1px solid #dcdccd;
	border-left: 0px;
	background: #f8f8f3;
	border-top: 0px;
	padding-top: 2px;
	padding-bottom: 2px;
}
tr.odd td:first-child,
tr.even td:first-child {
	border-left: 1px solid #dcdccd;
}
tr.even td.status {
	background: #f5f5ef url(images/td-dark.gif) repeat-x;
}
td.white table tr td {
	border: 1px solid #e9e9e0;
	border-left: 0px;
	border-top: 0px;
}
td.white:first-child table tr td:first-child {
	border-left: 1px solid #e9e9e0;
}
/* widget thihngys  *******************************************************************************/
#widget-tac_hosts table tr th,
#widget-tac_services table tr th,
#widget-tac_monfeat table tr th {
	border: 1px solid #e9e9e0;
	border-left: 0px;
	border-top: 0px;
}
#widget-tac_hosts table tr th:first-child,
#widget-tac_services table tr th:first-child,
#widget-tac_monfeat table tr th:first-child {
	border-left: 1px solid #e9e9e0;
}
/* table sorter styles ****************************************************************************/
.group_grid_table th.header,
.group_overview_table th.header,
.comments_table th.header,
#group_summary_table th.header,
#service_table th.header,
#host_table th.header,
th.header {
	background: #ffffff url(images/sort.png) top right;
	cursor: pointer;
	border-bottom: 1px solid #dcdccd;
	border-right: 1px solid #dcdccd;
	border-top: 1px solid #dcdccd;
	padding-top: 14px;
}
.group_grid_table th.header:first-child,
.group_overview_table th.header:first-child,
.comments_table th.header:first-child,
#group_summary_table th.header:first-child,
#service_table th.header:first-child,
#host_table th.header:first-child,
th.header:first-child {
	background: #ffffff url(images/sort.png) top right;
	cursor: pointer;
	border-left: 1px solid #dcdccd;
}
.group_grid_table th,
.group_overview_table th,
.comments_table th,
#group_summary_table th,
#service_table th,
#service_table th.headerNone,
th.headerNone,
#host_table th,
#host_table th.no-sort {
	background:#f5f5ef;
	cursor: default;
	border-bottom: 1px solid #dcdccd;
	border-right: 1px solid #dcdccd;
	border-top: 1px solid #dcdccd;
	padding-top: 14px;
	background: #ffffff url(images/sort-none.png) top right;
}
.group_grid_table th:first-child,
.group_overview_table th:first-child,
.comments_table th:first-child,
th.headerNone:first-child,
#group_summary_table th:first-child,
#service_table th:first-child,
#host_table th:first-child,
#host_table th.no-sort:first-child {
	border-left: 1px solid #dcdccd;
}
.group_grid_table th.headerSortDown,
.group_overview_table th.headerSortDown,
.comments_table th.headerSortDown,
#group_summary_table th.headerSortDown,
#service_table th.headerSortDown,
#host_table th.headerSortDown,
th.headerSortDown {
	background: #ffffff url(images/sort-down.png) top right;
	cursor: pointer;
}
.group_grid_table th.headerSortDown:first-child,
.group_overview_table th.headerSortDown:first-child,
.comments_table th.headerSortDown:first-child,
#group_summary_table th.headerSortDown:first-child,
#service_table th.headerSortDown:first-child,
#host_table th.headerSortDown:first-child,
th.headerSortDown:first-child {
	background: #ffffff url(images/sort-down.png) top right;
	border-left: 1px solid #dcdccd;
	cursor: pointer;
}
.group_grid_table th.headerSortUp,
.group_overview_table th.headerSortUp,
.comments_table th.headerSortUp,
#group_summary_table th.headerSortUp,
#service_table th.headerSortUp,
#host_table th.headerSortUp,
th.headerSortUp {
	background: #ffffff url(images/sort-up.png) top right;
	cursor: pointer;
}
.group_grid_table th.headerSortUp:first-child,
.group_overview_table th.headerSortUp:first-child,
.comments_table th.headerSortUp:first-child,
#group_summary_table th.headerSortUp:first-child,
#service_table th.headerSortUp:first-child,
#host_table th.headerSortUp:first-child,
th.headerSortUp:first-child {
	background: #ffffff url(images/sort-up.png) top right;
	border-left: 1px solid #dcdccd;
	cursor: pointer;
}
/* autocomplete styles ****************************************************************************/
.autocomplete-w1 {
	/*background:url(/application/views/themes/default/images/shadow.png) no-repeat bottom right;*/
	position:absolute;
	top:0px;
	left:0px;
	margin:8px 0px 0px 6px;
}
.autocomplete {
	border:1px solid #999999;
	background:#ffffff;
	cursor:default;
	text-align:left;
	max-height:350px;
	overflow:auto;
	margin:-6px 6px 6px -6px;
}
.autocomplete .selected {
	background:#f0f0f0;
}
.autocomplete div {
	padding:2px 5px;
	white-space:nowrap;
}
.autocomplete strong {
	font-weight:normal;
	color:#3399ff;
}
<?php if(extension_loaded('zlib')){ob_end_flush();}?>