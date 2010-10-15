<?php defined('SYSPATH') OR die('No direct access allowed.');
if (Auth::instance()->logged_in()) {
	$ninja_menu_setting = Ninja_setting_Model::fetch_page_setting('ninja_menu_state', '/');
}
if (!empty($ninja_menu_setting) && !empty($ninja_menu_setting->setting)) {
	$ninja_menu_state = $ninja_menu_setting->setting;
} else {
	$ninja_menu_state = 'show';
}
if (isset($this->template->js_header))
	$this->template->js_header->js = $this->xtra_js;

# fetch info on current skin
$current_skin = config::get('config.current_skin', '*', true);
if (!substr($current_skin, -1, 1) != '/') {
	$current_skin .= '/';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo (isset($title)) ? Kohana::config('config.product_name').' » '.html::specialchars($title) : Kohana::config('config.product_name') ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/'.$current_skin.'common.css') ?>" media="screen" />
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/'.$current_skin.'status.css') ?>" media="screen" />
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/'.$current_skin.'print.css') ?>" media="print" />
		<?php echo html::link($this->add_path('icons/16x16/favicon.ico'),'icon','image/icon') ?>
		<!--[If IE]>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/default/ie7.css') ?>" />
		<?php echo (Router::$controller.'/'.Router::$method == 'histogram/generate') ? html::script('application/media/js/excanvas.compiled.js') : ''; ?>
		<![endif]-->
		<?php
			echo (!empty($css_header)) ? $css_header : '';
			echo html::script('application/media/js/jquery.min.js');
			echo html::script('application/media/js/jquery-ui.min.js');
			echo html::script('application/media/js/jquery.form.js');
			echo html::script('application/media/js/jquery.easywidgets.min.js');
			echo html::script('application/media/js/jquery.autocomplete.min');
			echo html::script('application/media/js/jquery.selectboxes.min.js');
			echo html::script('application/media/js/jquery.jeditable.min');
			echo html::script('application/media/js/jquery.query.js');
			echo html::script('application/media/js/jquery.jgrowl.js');
			echo html::script('application/media/js/jquery.floatheader.js');
			echo html::script('application/media/js/jquery.qtip.min.js');
			echo html::script('application/media/js/jquery.hotkeys.min.js');
			echo html::script($this->add_path('js/collapse_menu.js'));
			echo html::script($this->add_path('js/global_search.js'));
			echo html::script($this->add_path('js/pagination.js'));
			if (!isset($disable_refresh) || $disable_refresh === false) {
				refresh::control();
			} else {
				refresh::is_alive();
			}
		?>

		<script type="text/javascript">
			//<!--
			<?php
				if (Auth::instance()->logged_in()) { ?>

				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';
				var _current_uri = '<?php echo Router::$controller.'/'.Router::$method ?>';
				var _theme_path = '<?php echo 'application/views/'.$this->theme_path ?>';
				var _widget_refresh_msg = '<?php echo $this->translate->_('Refresh rate for all widgets has been updated to %s sec'); ?>';
				var _widget_refresh_error = '<?php echo $this->translate->_('Unable to update refresh rate for all widgets.'); ?>';
				var _widget_global_refresh_error = '<?php echo $this->translate->_('An error was encountered when trying to update refresh rate for all widgets.'); ?>';
				var _widget_order_error = '<?php echo $this->translate->_('Unable to fetch widget order from database.'); ?>';
				var _widget_settings_msg = '<?php echo $this->translate->_('Settings for widget %s was updated'); ?>';
				var _widget_settings_error = '<?php echo $this->translate->_('Unable to fetch setting for widget %s'); ?>';
				var _widget_notfound_error = '<?php echo $this->translate->_('Unable to find widget %s'); ?>';
				var _page_refresh_msg = '<?php echo $this->translate->_('Updated page refresh rate to %s seconds'); ?>';
				var _success_header = '<?php echo $this->translate->_('Success'); ?>';
				var _error_header = '<?php echo $this->translate->_('ERROR'); ?>';
				var _ninja_menu_state = '<?php echo $ninja_menu_state ?>';
				var _ninja_menusection_About = '<?php echo config::get('ninja_menusection_About', '/', false, true) ?>';
				var _ninja_menusection_Monitoring = '<?php echo config::get('ninja_menusection_Monitoring', '/', false, true) ?>';
				var _ninja_menusection_Reporting = '<?php echo config::get('ninja_menusection_Reporting', '/', false, true) ?>';
				var _ninja_menusection_Configuration = '<?php echo config::get('ninja_menusection_Configuration', '/', false, true) ?>';
				var _form_error_header = '<?php echo $this->translate->_("The form couldn\'t be processed since it contains one or more errors.%sPlease correct the following error(s) and try again:%s"); ?>';
				var _command_empty_field = '<?php echo $this->translate->_("Field \'%s\' is required but empty"); ?>';
				var _loading_str = '<?php echo $this->translate->_("Loading..."); ?>';
				var _wait_str='<?php echo $this->translate->_('Please wait') ?>';
				var _refresh_paused_msg='<?php echo $this->translate->_('Page refresh has been paused.') ?>';
				var _refresh_unpaused_msg='<?php echo $this->translate->_('Page refresh has been restored.') ?>';
			<?php	if (config::get('keycommands.activated', '*', true)) {	?>

					var _keycommands_active='<?php echo config::get('keycommands.activated', '*', true); ?>';
					var _keycommand_search='<?php echo config::get('keycommands.search', '*', true); ?>';
					var _keycommand_pause='<?php echo config::get('keycommands.pause', '*', true); ?>';
					var _keycommand_forward='<?php echo config::get('keycommands.forward', '*', true); ?>';
					var _keycommand_back='<?php echo config::get('keycommands.back', '*', true); ?>';
			<?php 	} else { ?>
					var _keycommands_active='0';
			<?php 	} ?>

				<?php
					if (!empty($js_strings)) {
						echo $js_strings;
					}
				}
				if (!empty($inline_js)) {
					echo "$(document).ready(function() {";
					echo $inline_js;
					echo "});";
				}?>
			//-->
		</script>
	<?php	# use xajax if controller needs it
			if (isset($xajax_js)) echo $xajax_js; ?>

		<?php echo html::script($this->add_path('js/common.js')); ?>
		<?php echo (!empty($js_header)) ? $js_header : ''; ?>

	</head>

	<body>
		<div id="infobar-sml">
			<p><?php echo html::image($this->add_path('/icons/16x16/shield-warning.png'),array('style' => 'float: left; margin-right: 5px')).' '.sprintf($this->translate->_('It appears that the database is not up to date. Verify that Merlin and %s are running properly.'), Kohana::config('config.product_name')); ?></p>
		</div>
		<div id="top-bar">
			<?php echo html::image($this->add_path('icons/icon.png'),''); ?>
			<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/lookup" id="global_search" method="get">
				<div id="navigation">
					<ul>
					<?php
					if (isset($breadcrumb) && !empty($breadcrumb)){
						$link = explode(' » ',$breadcrumb);
						for($i = 0; $i < count($link); $i++) {
							echo '<li>'.$link[$i].'</li>';
						}
					} elseif (isset($title)) {
						$link = explode(' » ',$title);
						for($i = 0; $i < count($link); $i++) {
							echo '<li>'.$link[$i].'</li>';
						}
					}
					?>
					</ul>
					<?php
					$query = arr::search($_REQUEST, 'query');
					if ($query !== false && Router::$controller == 'search' && Router::$method == 'lookup') { ?>
					<input type="text" name="query" id="query" class="textbox" value="<?php echo $query ?>" />
					<? } else { ?>
					<input type="text" name="query" id="query" class="textbox" value="<?php echo $this->translate->_('Search')?>" onfocus="this.value=''" onblur="this.value='<?php echo $this->translate->_('Search')?>'" />
			<?php	} ?>
					<p><?php echo $this->translate->_('Welcome'); ?> <?php echo user::session('username') ?> | <?php echo html::anchor('default/logout', html::specialchars($this->translate->_('Log out'))) ?></p>
				</div>
			</form>
		</div>

		<div id="quickbar">
			<div id="quicklinks">
			</div>
			<div id="icons">
				<ul>
					<li id="settings_icon"<?php if ((isset($disable_refresh) && $disable_refresh !== false) && !isset($settings_widgets)) { ?> style="display:none"<?php } ?>><?php echo html::image($this->add_path('icons/16x16/settings.gif'),array('alt' => $this->translate->_('Settings'), 'title' => $this->translate->_('Settings'))) ?></li>
					<li onclick="show_info()"><?php echo html::image($this->add_path('icons/16x16/versioninfo.png'),array('alt' => $this->translate->_('Product information'), 'title' => $this->translate->_('Product information'))) ?></li>
					<li onclick="window.location.reload()"><?php echo html::image($this->add_path('icons/16x16/refresh.png'),array('alt' => $this->translate->_('Refresh page'), 'title' => $this->translate->_('Refresh page'))) ?></li>
					<li onclick="window.location.reload()"><?php echo $this->translate->_('Updated') ?>: <?php echo Auth::instance()->logged_in() ? date(nagstat::date_format()) : ''; ?></li>
				</ul>
			</div>
		</div>
		<div id="close-menu" title="<?php echo $this->translate->_('Mimimize menu') ?>" onclick="collapse_menu('hide')"></div>
		<div id="show-menu" title="<?php echo $this->translate->_('Expand menu') ?>" onclick="collapse_menu('show')"></div>
		<div style="position: fixed; left: 0px; z-index:2">

		<div id="menu" style="overflow-y:auto;">
			<ul>
			<?php
			if (isset($links))
				foreach ($links as $header => $link):
						echo '<li class="header" onclick="collapse_section(\''.html::specialchars($header).'\')">
									<cite class="menusection">'.html::specialchars($header).'</cite>
									<em>'.substr(html::specialchars($header),0,1).'</em>
								</li>'."\n";
						foreach ($link as $title => $url):
							// internal links
							if ($url[2] == 0) {
								$query_string = explode('&',Router::$query_string);
								$unhandled_string = array(
									'?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_PENDING),
									'?hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED),
									'?service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED),
									'?hoststatustypes='.(nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE)
								);

								if($url[1] == 'serviceproblems' && in_array('?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN),$query_string) == true)
									echo '<li class="'.html::specialchars($header).'">'.
											html::anchor($url[0], html::image($this->add_path('icons/menu-dark/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.
											html::anchor($url[0],html::specialchars($title),array('style' => 'font-weight: bold', 'class' => 'ninja_menu_links')).'</li>'."\n";

								elseif($url[1] == 'problems' && array_intersect($unhandled_string, $query_string) == true)
									echo '<li class="'.html::specialchars($header).'">'.
											html::anchor($url[0], html::image($this->add_path('icons/menu-dark/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.
											html::anchor($url[0],html::specialchars($title),array('style' => 'font-weight: bold', 'class' => 'ninja_menu_links')).'</li>'."\n";

								elseif($url[0] == '/'.Router::$current_uri.'?items_per_page=10')
									echo '<li class="'.html::specialchars($header).'">'.
											html::anchor($url[0], html::image($this->add_path('icons/menu-dark/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.
											html::anchor($url[0],html::specialchars($title),array('style' => 'font-weight: bold', 'class' => 'ninja_menu_links')).'</li>'."\n";

								elseif($url[0] == '/'.Router::$current_uri && !in_array('?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN),$query_string) && !array_intersect($unhandled_string, $query_string))
									echo '<li class="'.html::specialchars($header).'">'.
											html::anchor($url[0], html::image($this->add_path('icons/menu-dark/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.
											html::anchor($url[0],html::specialchars($title),array('style' => 'font-weight: bold', 'class' => 'ninja_menu_links')).'</li>'."\n";
								else
									echo '<li class="'.html::specialchars($header).'">'.
											html::anchor($url[0], html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.
											html::anchor($url[0],html::specialchars($title), array('class' => 'ninja_menu_links')).'</li>'."\n";
							}
							// common external links
							elseif($url[2] == 1) {
								echo '<li class="'.html::specialchars($header).'">'.
									  '<a href="'.$url[0].'" target="_blank">'.html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).'</a> '.
									  '<a href="'.$url[0].'" target="_blank" class="ninja_menu_links">'.html::specialchars($title).'</a></li>'."\n";
							}
							// local external links
							elseif($url[2] == 2 && Kohana::config('config.site_domain') == '/monitor/') {
								echo '<li class="'.html::specialchars($header).'">'.
									  '<a href="'.$url[0].'" target="_blank">'.html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).'</a> '.
									  '<a href="'.$url[0].'" target="_blank" class="ninja_menu_links">'.html::specialchars($title).'</a></li>'."\n";
							}
							// ninja external links
							elseif ($url[2] == 3 && Kohana::config('config.site_domain') != '/monitor/') {
								echo '<li class="'.html::specialchars($header).'">'.
									  '<a href="'.$url[0].'" target="_blank">'.html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).'</a> '.
									  '<a href="'.$url[0].'" target="_blank" class="ninja_menu_links">'.html::specialchars($title).'</a></li>'."\n";
							}
						endforeach;
					endforeach;
				?>
			</ul>
		</div>
		</div>
		<div id="page_settings">
			<ul>
				<li id="menu_global_settings" class="header"<?php	if (isset($disable_refresh) && $disable_refresh !== false) { ?> style="display:none"<?php } ?>><?php echo $this->translate->_('Global Settings') ?></li>
				<li id="noheader_ctrl" style="display:none">
					<input type="checkbox" id="noheader_chbx" value="1" /><label id="noheader_label" for="noheader_chbx"> <?php echo $this->translate->_('Hide page header')?></label>
				</li>
				<?php	if (!isset($disable_refresh) || $disable_refresh === false) { ?>
				<li id="ninja_page_refresh">
					<input type="checkbox" id="ninja_refresh_control" />
					<label id="ninja_refresh_lable" for="ninja_refresh_control"> <?php echo $this->translate->_('Pause refresh') ?></label>
				</li>
				<li id="ninja_refresh_edit">
					<?php echo $this->translate->_('Edit global refresh rate') ?><br />
					<div id="ninja_page_refresh_slider" style="width:200px; margin-top: 8px;">
						<input type="text" maxlength="3" size="3" id="ninja_page_refresh_value" name="ninja_page_refresh_value" style="position: absolute; font-size: 11px; margin-left: 160px; padding: 1px; margin-top:-25px;z-index: 500" /> <div style="position: aboslute; margin-left: 192px; margin-top: -23px">s</div>
					</div>
				</li>

				<?php
					} # end if disable_refresh

					$settings_widgets = (isset($settings_widgets)) ? $settings_widgets : '';
					if (is_array($settings_widgets)) {
						echo '<li class="header">'.$this->translate->_('Available Widgets').'</li>'."\n";
						foreach($settings_widgets as $id => $widget) {
							if (isset($user_widgets) && is_array($user_widgets)) {
								$class_name = array_key_exists($id, $user_widgets) ? 'selected' : 'unselected';
							} else {
								$class_name = 'selected';
							}
							echo '<li id="li_'.$id.'" class="'.$class_name.'" onclick="control_widgets(\''.$id.'\',this)">'.$widget.'</li>'."\n";
						}
						echo '<li onclick="restore_widgets();">'.$this->translate->_('Restore to factory settings').'</li>'."\n";
						echo '<li onclick="widget_page_refresh();">'.$this->translate->_('Set widget refresh rate (s.)').'</li>'."\n";
					}
				?>
			</ul>
		</div>

		<div id="version_info">
			<ul>
				<li>
					<?php
					function get_version_info() {
						$file = Kohana::config('config.version_info');
						if (file_exists($file)) {
							$handle = fopen($file, 'r');
							$contents = fread($handle, filesize($file));
							fclose($handle);
							return str_replace('VERSION=','',$contents);
							}
						}
				       echo  Kohana::config('config.product_name') . ":" . get_version_info();
					?>
				</li>
			</ul>
		</div>

		<div id="content"<?php echo (isset($nacoma) && $nacoma == true) ? ' class="ie7conf"' : ''?>>
		<?php	if ($this->notifications_disabled || $this->checks_disabled) {	?>
			<div id="notification_checks" style="padding-left:10px;padding-top:2px;color:darkred;">
				<ul>
					<?php if ($this->notifications_disabled) { ?><li>- Notifications are disabled</li><?php } ?>
					<?php if ($this->checks_disabled) { ?><li>- Service Checks are disabled</li><?php } ?>
				</ul>
			</div>
		<?php 	} ?>

			<?php if (isset($content)) { echo $content; } else { url::redirect('tac'); }?>
			<!--<p>Rendered in {execution_time} seconds, using {memory_usage} of memory</p> -->
		</div>
	</body>
</html>
