<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html>

<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo Kohana::config('config.product_name').': '._('logged out'); ?></title>
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
		<?php echo html::link($this->add_path('icons/favicon.ico'),'icon','image/x-icon') ?>
		<?php echo html::script('application/media/js/jquery.js'); ?>
		<?php echo new View('js_header', array('js' => $js)); ?>
	</head>

	<body>
		<br />
		<div id="logout-div"><br />
		<?php echo _('You have been logged out. Please close all browser windows to log out completely.'); ?>
		</div>
		<br />
	</body>
</html>
