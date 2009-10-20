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
	font: 11px arial,sans-serif;
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

h1 {
	font-size: 16px;
	font-weight: bold;
	margin-bottom: 10px;
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
	font: 11px arial,sans-serif;
	border: 1px solid #ffffff;
	outline:  1px solid #cdcddc;
	background: #b7b7b7 url(images/bg.png) repeat-x;
	padding: 1px 5px;
}
input[type=text] {
	font: 11px arial,sans-serif;
	border: 1px solid #ffffff;
	border:  1px solid #cdcddc;
	padding: 2px 5px;
	margin-left: 2px;
}
input.text {
	border: 0px;
	outline: 0px;
	background: #ffffff url(images/input-bg.png) repeat-x;
	font: 11px arial,sans-serif;
	padding: 7px 0px;
	float: left;
	margin-bottom: -1px;
	margin-left: 0px;
}
input.i160 {
	border: 0px;
	outline: 0px;
	background: #ffffff url(images/input-160.png) no-repeat;
	font: 11px arial,sans-serif;
	padding: 8px 7px;
	width: 155px;
	float: left;
	margin-bottom: -1px;
	margin-left: 0px;
}
select {
	font: 11px arial,sans-serif;
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
	margin: 10px 0px;
}
/* content ****************************************************************************************/
#license-bar {
	position: relative;
	position: fixed;
	left: 0px;
	height: 32px;
	width: 100%;
	background: #000000 url(images/bg.png) repeat-x;
	z-index: 1;
	border-left: 1px solid #d0d0d0;
	border-right: 1px solid #d0d0d0;
}
#top-bar {
	position: relative;
	position: fixed;
	left: 0px;
	height: 32px;
	width: 100%;
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
	background: url(images/menu-arrow.png) no-repeat;
	margin-left: 5px;
	padding-left: 23px;
	float: left;
	height: 32px;
	padding-top: 9px;
}
#navigation a {
	color: #000000;
	text-decoration: none;
	font-weight: bold;
	float: left;
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
	font: 11px arial,sans-serif;
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
	z-index: 1;
	border: 1px solid #dcdcdc;
}
#quicklinks ul {
	padding: 10px 7px 11px 7px;
	margin: 0px;
	width: 100%;
	float: left;
	background: #ffdd00 url(images/bg.png) repeat-x;
}
#quicklinks ul li {
	display: inline;
	padding: 0px 10px 0px 5px;
	/*background: url(images/menu-divider.gif) top right no-repeat;*/
	font-weight: bold;
}
#icons {
	position: absolute;
	right: 0px;
	top: 0px;
	z-index: 9999;
	background: #e0e0e0 url(images/bg.png) repeat-x;
	border-bottom: 1px solid #d0d0d0;
}
#icons ul {
	height: 31px;
}
#icons ul li {
	display: inline;
	margin-left: 0px;
	float: right;
	background: url(images/menu-divider.gif) 0px 0px no-repeat;
	padding: 11px 12px 4px 12px;
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
	width: 180px;
	top: 0px;
	left: 0px;
	z-index: 0;
	height: 100%;
	background-color: #f5f5ef;
	border-left: 1px solid #e9e9e0;
}
#menu ul li em {
	margin-top: -6px;
	border: 1px solid #ffffff;
	background: #999999;
	color: #ffffff;
	width: 16px;
	text-align: center;
	margin-left: -6px;
	display: block;
	display: none;
}
#menu ul li cite {
	margin-left: -3px;
}
#menu ul {
	margin: 0px;
	padding: 0px;
	margin-top: 66px;
}
#menu ul li {
	list-style: none;
	padding: 3px 0px 2px 10px;
	margin: 0px;
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
	margin-top: -2px;
	float: left;
	margin-right: 10px;
	opacity: 0.8;
}
#menu ul li a {
	text-decoration: none;
}
#menu ul li.header {
	font-weight: bold;
	padding-top: 15px;
	padding-left: 15px;
	cursor: pointer;
}
#menu-slider {
	width: 8px !important;
	z-index: 999 !important;
	height: 533px;
	margin: 0px 0px;
	border: 1px solid #cccccc;
	position: absolute;
	top: 67px;
	left: 176px;
}
.ui-slider-handle {
	position: absolute;
	left: -2px;
	width: 10px !important;
	background: #b7b7b7 !important;
	border: solid 1px #cccccc;
}
#menu-scroll {
	width: 180px;
	padding: 0px;
	height: 600px;
	overflow: hidden;
	position: absolute;
}
#close-menu {
	position: fixed;
	top: 62px;
	left: 160px;
	background: transparent url(images/menu_hide.png) no-repeat;
	width: 14px;
	height: 14px;
	z-index: 2;
	cursor: pointer;
}
#show-menu {
	position: fixed;
	top: 62px;
	left: 18px;
	background: transparent url(images/menu_show.png) no-repeat;
	width: 14px;
	height: 14px;
	z-index: 2;
	cursor: pointer;
	display: none;
}
#content {
	position: relative;
	top: 64px;
	margin: 0px 0px 0px 180px;
	padding-top: 11px;
	padding-top: 0px;
}
#page_settings {
	position: absolute;
	position: fixed;
	top: 67px;
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
	padding: 5px 10px;
	background: #d0d0d0 url(images/bg.png) repeat-x;
	cursor: pointer;
}
#page_settings ul li.header {
	font-weight: bold;
	padding-top: 15px;
	cursor: default;
}
#page_settings ul li.selected {
	list-style-image:none;
	background: transparent url(images/checkbox-selected.png) 4px 3px no-repeat;
	padding-left: 25px;
}
#page_settings ul li.unselected {
	list-style-image: none;
	background: transparent url(images/checkbox.png) 4px 3px no-repeat;
	padding-left: 25px;
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
	text-transform: uppercase;
}
tr td {
	padding: 0px 5px;
	border: 0px;
	text-align:left;
	white-space: nowrap;
}
tr td.dark  {
	width: 40px;
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
}
table.healt tr td div.border img {
	display: block;
	margin-top: 0px;
	margin-bottom: 0px;
	height: 30px;
}
.service-totals {
	margin-left: 249px;
	margin-top:-72px;
	width: 251px;
}
.service-totals tr td.status:first-child,
.w-table.w50 tr td.status:first-child { border-left: 1px solid #d0d0d0;}
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
.wtop { margin-top: 11px; margin-bottom: 0px;}
.widget-header span.widget-menu img {
	margin-left: 3px;
}
.widget {
	width: 100%;
	margin-top: 11px;
}
.widget-place {
	margin-left: 1%;
	width: 32%;
	float: left;
	min-height: 20px;
}
.widget-place.w98{
	width: 98%;
}
.widget-place.col {
	clear: none;
}
.widget-placeholder {
	margin-top: 11px;
  border: 1px dashed #cdcdbc;
}
.widget-editbox{
	z-index: 9999;
	background-color: #ffffff;
	padding: 15px;
	float: right;
	margin-top: -1px;
	border: 1px solid #e9e9e0;
	right: 0px;
	width: 200px;
	position: relative;
}
/**************************************************************************************************/
div.left {
	float: left;
	margin-top: 11px;
	margin-left: 1%;
}
div.right {
	float: right;
	margin-top: 11px;
}
.max { width: 100%; }
.w99 { width: 99%; }
.w98 { width: 98%; }
.w66 { width: 65%; }
.w50 { width: 50%; }
.w49 { width: 49%; }
.w48 { width: 48%; }
.w33 { width: 32%; }
.w32 { width: 32%; }
/* status *****************************************************************************************/
.status  {
	padding: 5px;
	height: 16px;
}
.icon {
	width: 16px;
	padding: 2px 7px;
	text-align: center;
	vertical-align: middle;
}
.icon img {
	margin-top: 2px;
	margin-bottom: -1px;
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
}
td.white table tr td {
	border: 1px solid #e9e9e0;
	border-left: 0px;
	border-top: 0px;
}
td.white:first-child table tr td:first-child {
	border-left: 1px solid #e9e9e0;
}
.w-table {
	/*border-spacing: 1px;
	background-color: #e9e9e0;
	margin-top: -1px;*/
}
.w-table tr td {
	border-right: 1px solid #d0d0d0;
	border-bottom:1px solid #d0d0d0;
}
.w-table tr td.dark {
	border-left: 1px solid #d0d0d0;
}
th.enabled_monfeat,
th.disabled_monfeat {
	cursor: pointer
}
th.disabled_monfeat em,
th.enabled_monfeat em {
	font-weight: normal;
	float: right;
	margin-top: 2px;
	margin-bottom: -2px;
	background: transparent url(../../icons/arrow-down.gif) right no-repeat;
	width: 70px;
	height: 16px;
	padding-left: 20px

}
th.enabled_monfeat em {
	width: 65px;
}
th.disabled_monfeat cite,
th.enabled_monfeat cite {
	float: left;
	margin-top: 2px;
	margin-bottom: -2px;
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
/* jquery.jgrowl.css ******************************************************************************/
div.jGrowl {
	padding:10px;
	z-index:9999;
}
/** Normal Style Positions **/
body > div.jGrowl {
	position:fixed;
}
body > div.jGrowl.top-left {
	left:0px;
	top:0px;
}
body > div.jGrowl.top-right {
	right:0px;
	top:0px;
}
body > div.jGrowl.bottom-left {
	left:0px;
	bottom:0px;
}
body > div.jGrowl.bottom-right {
	right:0px;
	bottom:0px;
}
body > div.jGrowl.center {
	top:0px;
	width:50%;
	left:25%;
}
/** Cross Browser Styling **/
div.center div.jGrowl-notification, div.center div.jGrowl-closer {
	margin-left:auto;
	margin-right:auto;
}
div.jGrowl div.jGrowl-notification, div.jGrowl div.jGrowl-closer {
	background-color:#000;
	color:#fff;
	opacity:.85;
	filter:alpha(opacity = 85);
	zoom:1;
	width:235px;
	padding:10px;
	margin-top:5px;
	margin-bottom:5px;
	font-family:arial,sans-serif;
	font-size:12px;
	text-align:left;
	display:none;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
}
div.jGrowl div.jGrowl-notification {
	min-height:40px;
}
div.jGrowl div.jGrowl-notification div.header {
	font-weight:bold;
	font-size:10px;
}
div.jGrowl div.jGrowl-notification div.close {
	float:right;
	font-weight:bold;
	font-size:12px;
	cursor:pointer;
}
div.jGrowl div.jGrowl-closer {
	height:15px;
	padding-top:4px;
	padding-bottom:4px;
	cursor:pointer;
	font-size:11px;
	font-weight:bold;
	text-align:center;
}
.ui-slider { position: relative; text-align: left; }
.ui-slider .ui-slider-handle { position: absolute; z-index: 2; width: 1.2em; height: 1.2em; cursor: default; }
.ui-slider .ui-slider-range { position: absolute; z-index: 1; font-size: .7em; display: block; border: 0; }
.ui-slider-horizontal { height: .8em; }
.ui-slider-horizontal .ui-slider-handle { top: -.3em; margin-left: -.6em; }
.ui-slider-horizontal .ui-slider-range { top: 0; height: 100%; }
.ui-slider-horizontal .ui-slider-range-min { left: 0; }
.ui-slider-horizontal .ui-slider-range-max { right: 0; }
/* Component containers
----------------------------------*/
.ui-widget { font-family: arial,sans-serif/*{ffDefault}*/; font-size: 1.1em/*{fsDefault}*/; }
.ui-widget-content { border: 1px solid #aaaaaa/*{borderColorContent}*/; background: #ffffff/*{bgColorContent}*/ /*url(images/ui-bg_flat_75_ffffff_40x100.png)*//*{bgImgUrlContent}*/ 50%/*{bgContentXPos}*/ 50%/*{bgContentYPos}*/ repeat-x/*{bgContentRepeat}*/; color: #222222/*{fcContent}*/; }
.ui-widget-content a { color: #222222/*{fcContent}*/; }
.ui-widget input, .ui-widget select, .ui-widget textarea, .ui-widget button { font-family: Arial,sans-serif/*{ffDefault}*/; font-size: 1em; }
.ui-widget-header { border: 1px solid #aaaaaa/*{borderColorHeader}*/; background: #cccccc/*{bgColorHeader}*/ /*url(images/ui-bg_highlight-soft_75_cccccc_1x100.png)*//*{bgImgUrlHeader}*/ 50%/*{bgHeaderXPos}*/ 50%/*{bgHeaderYPos}*/ repeat-x/*{bgHeaderRepeat}*/; color: #222222/*{fcHeader}*/; font-weight: bold; }
.ui-widget-header a { color: #222222/*{fcHeader}*/; }
.ui-corner-all { -moz-border-radius: 4px/*{cornerRadius}*/; -webkit-border-radius: 4px/*{cornerRadius}*/; }
.ui-state-default, .ui-widget-content .ui-state-default { border: 1px solid #d3d3d3/*{borderColorDefault}*/; background: #e6e6e6/*{bgColorDefault}*/ /*url(images/ui-bg_glass_75_e6e6e6_1x400.png)*//*{bgImgUrlDefault}*/ 50%/*{bgDefaultXPos}*/ 50%/*{bgDefaultYPos}*/ repeat-x/*{bgDefaultRepeat}*/; font-weight: normal/*{fwDefault}*/; color: #555555/*{fcDefault}*/; outline: none; }
#tac_problems_slider{width:200px}
/***************************************************************************************************
*	COLOURS!
***************************************************************************************************/
td.white table tr td,
td.white:first-child table tr td:first-child,
#widget-tac_hosts table tr th,
#widget-tac_services table tr th,
#widget-tac_monfeat table tr th,
#widget-tac_hosts table tr th:first-child,
#widget-tac_services table tr th:first-child,
#widget-tac_monfeat table tr th:first-child,
#top-bar,
#quickbar,
#menu,
#menu ul,
#page_settings ul li,
#menu ul li,
#page_settings,
.widget-editbox {
	border-color: #e9e9e0;
	border-color: #dcdcdc;
}

.group_grid_table th.headerSortUp:first-child,
.group_overview_table th.headerSortUp:first-child,
.comments_table th.headerSortUp:first-child,
#group_summary_table th.headerSortUp:first-child,
#service_table th.headerSortUp:first-child,
#host_table th.headerSortUp:first-child,
th.headerSortUp:first-child,
.group_grid_table th.headerSortDown:first-child,
.group_overview_table th.headerSortDown:first-child,
.comments_table th.headerSortDown:first-child,
#group_summary_table th.headerSortDown:first-child,
#service_table th.headerSortDown:first-child,
#host_table th.headerSortDown:first-child,
th.headerSortDown:first-child,
.group_grid_table th:first-child,
.group_overview_table th:first-child,
.comments_table th:first-child,
th.headerNone:first-child,
#group_summary_table th:first-child,
#service_table th:first-child,
#host_table th:first-child,
#host_table th.no-sort:first-child,
.group_grid_table th,
.group_overview_table th,
.comments_table th,
#group_summary_table th,
#service_table th,
#service_table th.headerNone,
th.headerNone,
#host_table th,
#host_table th.no-sort,
.group_grid_table th.header:first-child,
.group_overview_table th.header:first-child,
.comments_table th.header:first-child,
#group_summary_table th.header:first-child,
#service_table th.header:first-child,
#host_table th.header:first-child,
th.header:first-child,
.group_grid_table th.header,
.group_overview_table th.header,
.comments_table th.header,
#group_summary_table th.header,
#service_table th.header,
#host_table th.header,
th.header,
.autocomplete,
tr.odd td:first-child,
tr.even td:first-child,
tr.even td,
tr.odd td,
tr.odd td.bl,
tr.even td.bl,
tr.odd td.bt,
tr.even td.bt,
.widget-placeholder,
.widget-header,
table.healt,
#quickbar{
	border-color: #dcdcdc;
	border-color: #d0d0d0;
}

#menu ul li.hr {
	border-color: #ffffff;
}

input[type=submit],
input[type=reset],
input[type=button],
input[type=text],
select {
	border: 1px solid #ffffff;
	outline:  1px solid #cdcdcd;
}
input.i160 { border: 0px; outline: 0px; }
#top-bar{
	background:#e9e9e9 url(images/bg.png) repeat-x;
}

#page_settings ul li,
#quickbar,
#menu ul li {
	background:#e0e0e0 url(images/bg.png) repeat-x;
}

tr td,
table.healt tr td div.border {
	background:#e9e9e9 url(images/bg.png) repeat;
}


.widget-header.dark,
.status,
th,
tr td.dark,
tr.even td.status {
	background:#b7b7b7 url(images/bg.png) repeat;
}

/*#menu ul li.header {
	background:#b7b7b7 url(images/bg.png) 0px -5px repeat;
}*/

.widget-header {
	/*background:#f5f5f5 url(images/bg.png) repeat-x;*/
}


.w-table,
table.extinfo {
	background: #dcdcdc;
	background: #d0d0d0;
}

#menu ul li.hr {
background: #ffffff;
}

tr.even td 	{ background: #fdfdfd; }
tr.odd td,
#menu,
#page_settings,
.autocomplete .selected { background-color: #f5f5f5; }

#page_settings ul li,
#menu ul li {
	background-position: 0px -1px;
}

.group_grid_table th.header,
.group_overview_table th.header,
.comments_table th.header,
#group_summary_table th.header,
#service_table th.header,
#host_table th.header,
th.header,
.group_grid_table th.header:first-child,
.group_overview_table th.header:first-child,
.comments_table th.header:first-child,
#group_summary_table th.header:first-child,
#service_table th.header:first-child,
#host_table th.header:first-child,
th.header:first-child {
	background: #dcdcdc url(images/sort.png) top right;
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
	background:#dcdcdc url(images/bg.png) repeat-x;
}

.group_grid_table th.headerSortDown,
.group_overview_table th.headerSortDown,
.comments_table th.headerSortDown,
#group_summary_table th.headerSortDown,
#service_table th.headerSortDown,
#host_table th.headerSortDown,
th.headerSortDown,
.group_grid_table th.headerSortDown:first-child,
.group_overview_table th.headerSortDown:first-child,
.comments_table th.headerSortDown:first-child,
#group_summary_table th.headerSortDown:first-child,
#service_table th.headerSortDown:first-child,
#host_table th.headerSortDown:first-child,
th.headerSortDown:first-child {
	background: #dcdcdc url(images/sort-down.png) top right;
}

.group_grid_table th.headerSortUp,
.group_overview_table th.headerSortUp,
.comments_table th.headerSortUp,
#group_summary_table th.headerSortUp,
#service_table th.headerSortUp,
#host_table th.headerSortUp,
th.headerSortUp,
.group_grid_table th.headerSortUp:first-child,
.group_overview_table th.headerSortUp:first-child,
.comments_table th.headerSortUp:first-child,
#group_summary_table th.headerSortUp:first-child,
#service_table th.headerSortUp:first-child,
#host_table th.headerSortUp:first-child,
th.headerSortUp:first-child {
	background: #dcdcdc url(images/sort-up.png) top right;
}
/***************************************************************************************************
*	DO NOT ADD ANY STYLE BELOW THE COLOURS SECTION. i.e. below this line
***************************************************************************************************/
<?php if(extension_loaded('zlib')){ob_end_flush();}?>