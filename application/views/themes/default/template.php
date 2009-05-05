<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php if (isset($title)) echo html::specialchars($title) ?></title>
		<?php echo html::stylesheet('application/views/themes/default/css/common.css') ?>
		<?php echo html::stylesheet('application/views/themes/default/css/status.css') ?>
		<?php echo html::stylesheet('application/views/themes/default/css/css-buttons.css') ?>
		<?php echo html::link('application/views/themes/default/images/favicon.ico','icon','image/ico') ?>
		<?php
			if (!empty($css_header)) {
				echo $css_header;
			}
		?>
		<?php echo html::script('application/media/js/jquery.min.js') ?>
		<?php echo html::script('application/media/js/jquery.form.js') ?>
		<?php echo html::script('application/media/js/jquery.tablesorter.min.js') ?>
		<?php echo html::script('application/media/js/jquery.easywidgets.min.js') ?>
		<?php echo html::script('application/media/js/jquery-ui.min.js') ?>
		<?php echo html::script('application/media/js/btn.js') ?>
		<script type="text/javascript">
			//<!--
				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';

				$(document).ready(function() {
					$("#sort-table").tablesorter({
						sortList: [[1,0]],
						headers: {
						  0: { sorter: false },
							6: { sorter: false }
						}
					});
				});

				$(function(){
	$.fn.EasyWidgets({
		i18n : {
			editText : '<img src="/ninja/application/views/themes/default/images/icons/box-config.png" alt="Settings" style="margin: -12px 30px 0px auto; display: block" />',
			closeText : '<img src="/ninja/application/views/themes/default/images/icons/box-close.png" alt="Close widget"   style="margin: -12px 0px 0px auto; display: block" />',
			collapseText : '<img src="/ninja/application/views/themes/default/images/icons/box-maximize.png" alt="Collapse"  style="margin: -12px 15px 0px auto; display: block" />',
			cancelEditText : '<img src="/ninja/application/views/themes/default/images/icons/box-config.png" alt="Cancel" style="margin: -12px 30px 0px auto; display: block" />',
			extendText : '<img src="/ninja/application/views/themes/default/images/icons/box-mimimize.png" alt="Extend" style="margin: -12px 15px 0px auto; display: block" />'
		},
		effects : {
			effectDuration : 150,
			widgetShow : 'slide',
			widgetHide : 'slide',
			widgetClose : 'slide',
			widgetExtend : 'slide',
			widgetCollapse : 'slide',
			widgetOpenEdit : 'slide',
			widgetCloseEdit : 'slide',
			widgetCancelEdit : 'slide'
		}
	});
});
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
			<?php echo html::image('application/views/themes/default/images/nagios-sml.gif','Nagios'); ///ninja/index.php/tac ?>
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
				<?php echo $this->translate->_('Updated') ?>: <?php echo date('d F Y H:i:s'); ?>
			</div>
		</div>

		<div id="menu">
			<div id="close-menu" title="Hide menu" onclick="collapse_menu()"></div>
			<ul>
			<?php
				foreach ($links as $header => $link):
						echo '<li class="header">'.html::specialchars($header).'</li>';
						foreach ($link as $title => $url):
							echo '<li>'.html::image('application/views/themes/default/images/star.png',array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' '.html::anchor($url, html::specialchars($title)).'</li>';
						endforeach;
					endforeach;
				?>
			</ul>
		</div>

		<div id="content">
			<?php if (isset($content)) echo $content ?>
			<!--<p>Rendered in {execution_time} seconds, using {memory_usage} of memory</p> -->
		</div>
	</body>
</html>
