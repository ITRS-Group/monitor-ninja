<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php if (isset($title)) echo html::specialchars($title) ?></title>
		<?php echo html::stylesheet('application/views/themes/default/css/common.css') ?>
		<?php echo html::stylesheet('application/views/themes/default/css/status.css') ?>
		<?php echo html::stylesheet('application/views/themes/default/css/css-buttons.css') ?>
		<?php //echo html::link('application/views/themes/default/images/favicon.ico','icon','image/ico') ?>
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
		<?php //echo html::script('application/media/js/btn.js') ?>
		<script type="text/javascript">
			//<!--
				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';

				$(document).ready(function() {
					$("#sort-table").tablesorter({
						sortList: [[1,0]],
						headers: {
							6: { sorter: false }
						}
					});
				});

				$(document).ready(function() {
					$("#sort-group-grid").tablesorter({
						//sortList: [[1,0]],
						headers: {
							1: { sorter: false },
							2: { sorter: false }
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

			function collapse_menu(action) {

				if (action == '') {
					//alert('kolla kaka');
					kakor = document.cookie;
					kakor_delar = kakor.split('; ');
					for (j = 0; j < kakor_delar.length; j++) {
						tva_delar = kakor_delar[j].split('=');
						if (tva_delar[0] == 'ninja_menu') {
							action = tva_delar[1];
						}
					}
				}

				if (action == 'hide') {
					//alert('göm mig');
					document.getElementById('menu').style.width = '35px';
					document.getElementById('close-menu').style.display = 'none';
					document.getElementById('show-menu').style.display = 'block';
					var menu = document.getElementById('menu');
					menu.getElementsByTagName('cite')[0].setAttribute('style','display: none');
					for (var i = 1; i < menu.getElementsByTagName('a').length; i = i+2) {
						menu.getElementsByTagName('a')[i].setAttribute('style','display: none');
					}
					document.getElementById('content').style.marginLeft = '35px';
					document.cookie = 'ninja_menu=hide';
				}
				if (action == 'show') {
					//alert('visa mig');
					document.getElementById('menu').style.width = '161px';
					document.getElementById('close-menu').style.display = 'block';
					document.getElementById('show-menu').style.display = 'none';
					var menu = document.getElementById('menu');
					menu.getElementsByTagName('cite')[0].setAttribute('style','display: inline');
					for (var i = 1; i < menu.getElementsByTagName('a').length; i = i+2) {
						menu.getElementsByTagName('a')[i].setAttribute('style','display: inline');
					}
					document.getElementById('content').style.marginLeft = '161px';
					document.cookie = 'ninja_menu=show';
				}
			}
			//-->
		</script>
		<?php
			if (!empty($js_header)) {
				echo $js_header;
			}
		?>
	</head>

	<body onload="collapse_menu('')">
		<div id="top-bar">
			<?php //echo html::image('application/views/themes/default/images/nagios-sml.gif','Nagios'); ///ninja/index.php/tac ?>
			<div style="font-size: 16px; margin: 7px 10px; float: left">NINJA</div>
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
			<div id="close-menu" title="<?php echo $this->translate->_('Hide menu') ?>" onclick="collapse_menu('hide')"></div>
			<div id="show-menu" title="<?php echo $this->translate->_('Show menu') ?>" onclick="collapse_menu('show')"></div>
			<ul>
			<?php
				foreach ($links as $header => $link):
						echo '<li class="header"><cite>'.html::specialchars($header).'</cite></li>';
						foreach ($link as $title => $url):
							echo '<li>'.html::anchor($url, html::image('application/views/themes/default/images/star.png',array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.html::anchor($url, html::specialchars($title)).'</li>';
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