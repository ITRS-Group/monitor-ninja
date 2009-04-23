<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php if (isset($title)) echo html::specialchars($title) ?></title>
		<?php echo html::stylesheet('application/views/themes/default/css/common.css') ?>
		<?php echo html::stylesheet('application/views/themes/default/css/status.css') ?>
		<?php echo html::link('application/views/themes/default/images/favicon.ico','icon','image/ico') ?>
		<?php
			if (!empty($css_header)) {
				echo $css_header;
			}
		?>
		<?php echo html::script('application/media/js/jquery.min.js') ?>
		<?php echo html::script('application/media/js/jquery.form.js') ?>
		<?php echo html::script('application/media/js/jquery.tablesorter.min.js') ?>
		<script type="text/javascript">
			//<!--
				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';

				$(document).ready(function() {
					$("#sort-table").tablesorter({sortList: [[1,0], [0,0]]});
				}
);
			//-->
		</script>
		<?php echo html::script('application/media/js/ajax_test.js') ?>
		<?php
			if (!empty($js_header)) {
				echo $js_header;
			}
	?>

	</head>

	<body>
		<div id="top-bar">
			<?php echo html::image('application/views/themes/default/images/nagios-sml.gif','Nagios') ?>
			<form action="">
				<div id="navigation">
					<?php //print_r( html::breadcrumb()); ?>
					<?php echo html::image('application/views/themes/default/images/menu-arrow.gif','>') ?>
					<?php $link = html::breadcrumb();
					for($i = 0; $i < count($link); $i++) {
						echo $link[$i].' '.html::image('application/views/themes/default/images/menu-arrow.gif','>');
					}
					?>

					<input type="text" name="show_host" value="Show host" onfocus="this.value=''" onblur="this.value='Show host'" />
					<p><?php echo $this->translate->_('Welcome'); ?> <?php echo user::session('username') ?> | <?php echo html::anchor('default/logout', html::specialchars($this->translate->_('Log out'))) ?></p>
				</div>
			</form>
		</div>

		<div id="quickbar">
			<div id="quicklinks">
				<!--<ul>
					<li>Tactical Overview</li>
					<li>Host Detail</li>
					<li>Service Detail</li>
				</ul>-->
			</div>
			<div id="icons">
				<ul>
					<li><?php echo html::image('application/views/themes/default/images/star.png','Bookmark') ?></li>
					<li><?php echo html::image('application/views/themes/default/images/nyckel.png','Settings') ?></li>
				</ul>
			</div>
			<div id="status">
				<?php echo $this->translate->_('Updated') ?>: DD Month Year HH:MM:SS CET &nbsp;
			</div>
		</div>

		<div id="menu">
			<div id="close-menu" title="Hide menu" onclick="collapse_menu()"></div>
			<ul>
				<li class="header"><?php echo $this->translate->_('Monitoring') ?></li>
				<li class="selected"><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Tactical Overview</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Host Detail</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Service Detail</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Hostgroup Summary</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Hostgroup Overview</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Hostgroup Grid</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Servicegroup Summary</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Servicegroup Overview</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Servicegroup Grid</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Status Map</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Network Map</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Hyper Map</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Network Outages</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Host Problems</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Service Problems</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Unhandled problems</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Comments</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Schedule Downtime</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Process Info</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Performance Info</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Scheduling Queue</a></li>
				<li class="header"><?php echo $this->translate->_('Reporting') ?></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Trends</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Availability</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">SLA Reporting</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Schedule Reports</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Alert History</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Alert Summary</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Notifications</a></li>
				<li><?php echo html::image('application/views/themes/default/images/star.png','tmp') ?> <a href="#">Event Log</a></li>
			</ul>
		</div>

		<div id="content">
			<?php if (isset($content)) echo $content ?>
			<!--<p>Rendered in {execution_time} seconds, using {memory_usage} of memory</p> -->
		</div>
	</body>
</html>
