<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo (isset($title)) ? 'Ninja » '.ucwords(html::specialchars($title)) : 'Ninja' ?></title>
		<?php echo html::stylesheet('application/views/themes/default/css/default/common.css') ?>
		<?php echo html::stylesheet('application/views/themes/default/css/default/status.css') ?>
		<?php echo html::stylesheet('application/views/themes/default/css/default/autocomplete_styles.css') ?>
		<?php echo html::link('application/views/themes/default/icons/16x16/favicon.ico','icon','image/icon') ?>
		<?php echo (!empty($css_header)) ? $css_header : '' ?>
		<?php echo html::script('application/media/js/jquery.min.js') ?>
		<?php echo html::script('application/media/js/jquery-ui.min.js') ?>
		<?php echo html::script('application/media/js/jquery.form.js') ?>
		<?php echo html::script('application/media/js/jquery.easywidgets.min.js') ?>
		<?php echo html::script('application/media/js/jquery.autocomplete.min') ?>
		<?php echo html::script('application/media/js/jquery.selectboxes.min.js') ?>
		<?php echo html::script('application/media/js/jquery.jeditable.min') ?>
		<?php echo html::script('application/media/js/jquery.query.js') ?>
		<?php echo html::script('application/views/themes/default/js/collapse_menu.js') ?>
		<?php echo html::script('application/views/themes/default/js/global_search.js') ?>
		<?php echo html::script('application/views/themes/default/js/pagination.js') ?>
		<?php echo (!empty($js_header)) ? $js_header : '' ?>
		<script type="text/javascript">
			//<!--
				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';
				<?php
				if (!empty($inline_js)) {
					echo "$(document).ready(function() {";
					echo $inline_js;
					echo "});";
				}
				?>
			//-->
		</script>
	</head>

	<body>
		<div id="top-bar">
			<?php echo html::image('application/views/themes/default/icons/ninja.png','NINJA'); ?>
			<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/lookup" id="global_search" method="get">
				<div id="navigation">
					<ul>
					<?php
					/*$link = html::breadcrumb();
					for($i = 0; $i < count($link); $i++) {
						echo '<li>'.$link[$i].'</li>';
					}*/
					if (isset($title)){
						$link = split(' » ',$title);
						for($i = 0; $i < count($link); $i++) {
							echo '<li><a href="#">'.$link[$i].'</a></li>';
						}
					}
					?>
					</ul>
					<input type="text" name="query" id="query" class="textbox" value="<?php echo $this->translate->_('Search')?>" onfocus="this.value=''" onblur="this.value='<?php echo $this->translate->_('Search')?>'" />
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
					<?php
						$settings_widgets = (isset($settings_widgets)) ? $settings_widgets : '';
						if (is_array($settings_widgets))
							echo '<li onclick="settings()">'.html::image('application/views/themes/default/icons/16x16/settings.gif',array('alt' => $this->translate->_('Settings'), 'title' => $this->translate->_('Settings'))).'</li>';
					?>
					<li onclick="window.location.reload()"><?php echo $this->translate->_('Updated') ?>: <?php echo date('d F Y H:i:s'); ?></li>
				</ul>
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
							if($url[0] == str_replace('/ninja/index.php/','',$_SERVER['PHP_SELF']))
								echo '<li>'.html::anchor($url[0], html::image('application/views/themes/default/icons/12x12/menu-'.$url[1].'_highlight.png',array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.html::anchor($url[0], html::specialchars($title),array('style' => 'font-weight: bold', 'class' => 'ninja_menu_links')).'</li>';
							elseif($url[0] == '')
								echo '<li class="hr">&nbsp;</li>';
							else
								echo '<li>'.html::anchor($url[0], html::image('application/views/themes/default/icons/12x12/menu-'.$url[1].'.png',array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.html::anchor($url[0], html::specialchars($title), array('class' => 'ninja_menu_links')).'</li>';
						endforeach;
					endforeach;
				?>
			</ul>
		</div>

		<?php
		/*$settings_page = array(
			$this->translate->_('Page Contrast') => array(
				'low' => $this->translate->_('Low'),
				'medium' => $this->translate->_('Medium'),
				'high' => $this->translate->_('High')
			),
		);*/
		$settings_page = '';
		?>

		<div id="page_settings">
			<ul>
				<?php
					if (is_array($settings_widgets)) {
						echo '<li class="header">'.$this->translate->_('Availiable Widgets').'</li>';
						foreach($settings_widgets as $id => $widget) {
							if (isset($user_widgets) && is_array($user_widgets)) {
								$class_name = array_key_exists($id, $user_widgets) ? 'selected' : 'unselected';
							} else {
								$class_name = 'selected';
							}
							echo '<li id="li_'.$id.'" class="'.$class_name.'" onclick="control_widgets(\''.$id.'\',this)">'.$widget.'</li>';
						}
						echo '<li onclick="restore_widgets();">'.$this->translate->_('Restore to factory settings').'</li>';
					}
					if (is_array($settings_page)) {
						foreach($settings_page as $group => $settings) {
							echo '<li class="header">'.$group.'</li>';
							foreach($settings as $id => $title) {
								echo '<li id="pagesettings_'.$id.'" class="unselected" onclick="page_settings(\''.$id.'\',this)">'.$title.'</li>';
							}
						}
					}
				?>
			</ul>
		</div>
		<div id="content">
			<?php if (isset($content)) { echo $content; } else { url::redirect('tac'); } ?>
			<!--<p>Rendered in {execution_time} seconds, using {memory_usage} of memory</p> -->
		</div>
	</body>
</html>