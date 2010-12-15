<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo Kohana::config('config.product_name').': '.$this->translate->_('logged out'); ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/default/common.css') ?>" />
		<style type="text/css">
			#logout-div {
				position: relative;
				margin: 150px auto;
				width: 240px;
				text-align: center;
				background: transparent url(<?php echo $this->add_template_path('css/default/') ?>images/logout.png) no-repeat top center;
				padding-top: 66px;
			}
		</style>
		<?php echo html::link($this->add_path('icons/16x16/favicon.ico'),'icon','image/icon') ?>
		<?php echo html::script('application/media/js/jquery.min.js'); ?>
		<?php echo (!empty($js_header)) ? $js_header : '' ?>
	</head>

	<body>
		<br />
		<div id="logout-div"><br />
		<?php echo $this->translate->_('You have been logged out. Please close all browser windows to log out completely.'); ?>
		</div>
		<br />
	</body>
</html>