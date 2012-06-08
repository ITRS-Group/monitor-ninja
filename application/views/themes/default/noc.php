<?php defined('SYSPATH') OR die('No direct access allowed.');
$current_skin = false;
$authorized = false;
if (Auth::instance()->logged_in()) {
	$ninja_menu_setting = Ninja_setting_Model::fetch_page_setting('ninja_menu_state', '/');

	$auth = new Nagios_auth_Model();
	if ($auth->view_hosts_root) {
		$authorized = true;
	}

	# fetch info on current skin
	$current_skin = config::get('config.current_skin', '*', true);
	if (!substr($current_skin, -1, 1) != '/') {
		$current_skin .= '/';
	}
	$this->session->set('use_noc', true);
}
$ninja_menu_state = 'hide';

if (isset($this->template->js_header))
	$this->template->js_header->js = $this->xtra_js;
?>
<!DOCTYPE html>

<html lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=8" /> <!-- Please remove me -->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo (isset($title)) ? Kohana::config('config.product_name').' » '.html::specialchars($title) : Kohana::config('config.product_name') ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/'.$current_skin.'common.css') ?>" media="screen" />
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/'.$current_skin.'status.css') ?>" media="screen" />
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/'.$current_skin.'print.css') ?>" media="print" />
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/default/jquery-ui-custom.css') ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo url::base(false).'application/media/css/jmenu.css' ?>" />
		<?php echo html::link($this->add_path('icons/16x16/favicon.ico'),'icon','image/icon') ?>
		<!--[If IE]>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/default/ie7.css') ?>" />
		<?php echo (Router::$controller.'/'.Router::$method == 'histogram/generate') ? html::script('application/media/js/excanvas.compiled.js') : ''; ?>
		<![endif]-->
		<style type="text/css">
			#version_info {
				top: 35px;
			}
			#logo_container{
				position:absolute;
				top:5px;
				left:5px;
				background: url(<?php echo $this->add_template_path('icons/icon.png'); ?>) no-repeat;
				width:19px;
				height:35px;
				z-index:999;
			}
			#jmenu {
				position: absolute;
				left:150px;
				padding-left:130px;
			}
			#jmenu_container {
				width:200px;
				top:0;
				left:140px;
				/*background:#ffffff;*/
			}

			#infobar-sml{
				top:35px;
				z-index:100;
			}
			.config_header{
				top:20px !important;
				position:fixed;
			}
			.ie-pag-config {
				right:100px !important;
			}
		</style>
		<?php
			$use_contextmenu = false;
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
			echo html::script('application/media/js/jquery.qtip.min.js');
			echo html::script('application/media/js/jquery.hotkeys.min.js');
			echo html::script('application/media/js/jmenu.js');
			if (Router::$controller == 'status') {
				$use_contextmenu = true;
				# only required for status controller so no need to always include it
				echo html::script('application/media/js/jquery.contextMenu.js');
			}
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
				var _settings_msg = '<?php echo $this->translate->_('The settings were updated'); ?>';
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
				var _use_contextmenu=<?php echo $use_contextmenu === true ? 1 : 0; ?>;
				var _reports_link='<?php echo Kohana::config('reports.reports_link') ?>';
				var _search_save_error = '<?php echo $this->translate->_("Length of \'%s\' must be between %s and %s characters.") ?>';
				var _search_string_field = '<?php echo $this->translate->_('Search string') ?>';
				var _search_remove_confirm = '<?php echo $this->translate->_('Are you sure that you wish to remove this saved search?') ?>';
				var _search_name_field = '<?php echo $this->translate->_('Name') ?>';
				var _search_save_ok = '<?php echo $this->translate->_('OK') ?>';
				var _search_save_error = '<?php echo $this->translate->_('ERROR') ?>';
				var _search_saved_ok = '<?php echo $this->translate->_('Your search was successfully saved.') ?>';
				var _search_saved_error = '<?php echo $this->translate->_('An error occured when trying to save your search.') ?>';
				var _no_menu_refresh = true;
				var _is_noc_template = true;

			<?php	if (config::get('keycommands.activated', '*', true)) {	?>

					var _keycommands_active='<?php echo config::get('keycommands.activated', '*', true); ?>';
					var _keycommand_search='<?php echo config::get('keycommands.search', '*', true); ?>';
					var _keycommand_pause='<?php echo config::get('keycommands.pause', '*', true); ?>';
					var _keycommand_forward='<?php echo config::get('keycommands.forward', '*', true); ?>';
					var _keycommand_back='<?php echo config::get('keycommands.back', '*', true); ?>';
			<?php 	} else { ?>
					var _keycommands_active='0';
			<?php 	} ?>

				var _use_popups=<?php echo (int)config::get('config.use_popups', '*', true); ?>;
				var _popup_delay='<?php echo config::get('config.popup_delay', '*', true); ?>';
				<?php
					if (!empty($js_strings)) {
						echo $js_strings;
					}
				}
				echo "$(document).ready(function() {"; ?>
				<?php
				echo isset($inline_js) ? $inline_js : '';
				echo "});";
				?>
			//-->
		</script>
		<?php echo html::script($this->add_path('js/common.js')); ?>
		<?php echo (!empty($js_header)) ? $js_header : '';
		echo html::script($this->add_path('noc/js/noc'));?>

	</head>


	<body onload="loadScroll()" onunload="saveScroll()">
		<?php echo (!empty($context_menu)) ? $context_menu : ''; ?>
		<div id="infobar-sml">
			<p><?php echo html::image($this->add_path('/icons/16x16/shield-warning.png'),array('style' => 'float: left; margin-right: 5px;', 'alt' => 'Warning')).' '.sprintf($this->translate->_('It appears that the database is not up to date. Verify that Merlin and %s are running properly.'), Kohana::config('config.product_name')); ?></p>
		</div>
		<div id="top-bar"></div>
		<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/lookup" id="global_search" method="get">
		<div id="quickbar" style="top:0">
			<div id="logo_container"></div>
			<div id="jmenu_container" style="display:block">
				<ul id="jmenu">
				<?php
				if (isset($links))
					foreach ($links as $header => $link):
							if (empty($link)) {
								continue;
							}
							echo '<li class="">
										<a href="#">'.html::specialchars($header).'</a>';
							echo "<ul>";
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
												html::anchor($url[0], html::image($this->add_path('icons/menu-dark/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' <span>'.html::specialchars($title).'</span>').'</li>'."\n";

									elseif($url[1] == 'problems' && array_intersect($unhandled_string, $query_string) == true)
										echo '<li class="'.html::specialchars($header).'">'.
												html::anchor($url[0], html::image($this->add_path('icons/menu-dark/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' <span>'.html::specialchars($title).'</span>').'</li>'."\n";

									elseif($url[0] == '/'.Router::$current_uri.'?items_per_page=10')
										echo '<li class="'.html::specialchars($header).'">'.
												html::anchor($url[0], html::image($this->add_path('icons/menu-dark/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' <span>'.html::specialchars($title).'</span>').'</li>'."\n";

									elseif($url[0] == '/'.Router::$current_uri && !in_array('?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN),$query_string) && !array_intersect($unhandled_string, $query_string))
										echo '<li class="'.html::specialchars($header).'">'.
												html::anchor($url[0], html::image($this->add_path('icons/menu-dark/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' <span>'.html::specialchars($title).'</span>').'</li>'."\n";
									else
										echo '<li class="'.html::specialchars($header).'">'.
												html::anchor($url[0], html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' <span>'.html::specialchars($title).'</span>').'</li>'."\n";
								}
								// common external links
								elseif($url[2] == 1) {
									echo '<li class="'.html::specialchars($header).'">'.
										  '<a href="'.$url[0].'" target="_blank">'.html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' <span>'.html::specialchars($title).'</span></a></li>';
								}
								// local external links
								elseif($url[2] == 2 && Kohana::config('config.site_domain') == '/monitor/') {
									echo '<li class="'.html::specialchars($header).'">'.
										  '<a href="'.$url[0].'" target="_blank">'.html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' <span>'.html::specialchars($title).'</span></a></li>';
								}
								// ninja external links
								elseif ($url[2] == 3 && Kohana::config('config.site_domain') != '/monitor/') {
									echo '<li class="'.html::specialchars($header).'">'.
										  '<a href="'.$url[0].'" target="_blank">'.html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' <span>'.html::specialchars($title).'</span></a></li>';
								}

							endforeach;
							echo "</ul>";
							echo "</li>";
						endforeach;
					?>
				</ul>
			</div>
			<div id="icons">
				<ul>
					<li id="settings_icon"<?php if ((isset($disable_refresh) && $disable_refresh !== false) && !isset($widgets)) { ?> style="display:none"<?php } ?>><?php echo html::image($this->add_path('icons/16x16/settings.gif'),array('alt' => $this->translate->_('Settings'), 'title' => $this->translate->_('Settings'))) ?></li>
					<li onclick="show_info()"><?php echo html::image($this->add_path('icons/16x16/versioninfo.png'),array('id' => 'info_icon', 'alt' => $this->translate->_('Product information'), 'title' => $this->translate->_('Product information'))) ?></li>
					<li onclick="window.location.reload()"><?php echo $this->translate->_('Updated') ?>: <?php echo Auth::instance()->logged_in() ? '<span id="page_last_updated">'.date(nagstat::date_format()).'</span>' : ''; ?></li>
					<li <?php if (!isset($is_searches) || empty($is_searches)) { ?>style="display:none"<?php } ?> id="my_saved_searches"><?php echo html::image($this->add_path('icons/24x24/save_search.png'), array('title' => $this->translate->_('Click to view your saved searches'), 'id' => 'my_saved_searches_img')) ?></li>
				</ul>
			</div>
			<p align="right" style="padding-top:10px;"><?php echo $this->translate->_('Welcome'); ?> <?php echo user::session('username') ?> | <?php echo html::anchor('default/logout', html::specialchars($this->translate->_('Log out'))) ?> &nbsp; </p>
			<div id="navigation" style="padding-right:450px;padding-top:10px">
			<?php
			$query = arr::search($_REQUEST, 'query');
			if ($query !== false && Router::$controller == 'search' && Router::$method == 'lookup') { ?>
			<input type="text" name="query" id="query" class="textbox" value="<?php echo $query ?>" />
			<?php } else { ?>
			<input type="text" name="query" id="query" class="textbox" value="<?php echo $this->translate->_('Search')?>" onfocus="this.value=''" onblur="this.value='<?php echo $this->translate->_('Search')?>'" />
	<?php	} ?>
			</div>
			<div id="version_info">
				<ul>
					<li>
					<?php echo  Kohana::config('config.product_name') . ":" . config::get_version_info(); ?>
					</li>
				</ul>
			</div>

		</div>
		</form>
		<div style="top:30px;margin-left:0" id="content"<?php echo (isset($nacoma) && $nacoma == true) ? ' class="ie7conf"' : ''?>>

			<?php if (isset($content)) { echo $content; } else { url::redirect(Kohana::config('routes.logged_in_default')); }?>
			<!--<p>Rendered in {execution_time} seconds, using {memory_usage} of memory</p> -->
		</div>

		<div id="page_settings" style="top:35px">
			<ul>
				<li id="menu_global_settings" class="header"<?php	if (isset($disable_refresh) && $disable_refresh !== false) { ?> style="display:none"<?php } ?>><?php echo $this->translate->_('Global Settings') ?></li>
				<li id="noheader_ctrl" style="display:none">
					<input type="checkbox" id="noheader_chbx" value="1" /><label id="noheader_label" for="noheader_chbx"> <?php echo $this->translate->_('Hide page header')?></label>
				</li>
				<li id="ninja_use_noc">
					<?php echo form::checkbox(array('id' => 'ninja_noc_control'), '', $this->session->get('use_noc', false)) ?>
					<label id="ninja_noc_lable" for="ninja_noc_control"> <?php echo $this->translate->_('Use noc (experimental)') ?></label>
				</li>

				<?php	if (!isset($disable_refresh) || $disable_refresh === false) { ?>
				<li id="ninja_page_refresh">
					<input type="checkbox" id="ninja_refresh_control" />
					<label id="ninja_refresh_lable" for="ninja_refresh_control"> <?php echo $this->translate->_('Pause refresh') ?></label>
				</li>
				<li id="ninja_refresh_edit">
					<?php echo $this->translate->_('Edit global refresh rate') ?><br />
					<div id="ninja_page_refresh_slider" style="width:200px; margin-top: 8px;">
						<input type="text" maxlength="3" size="3" id="ninja_page_refresh_value" name="ninja_page_refresh_value" style="position: absolute; font-size: 11px; margin-left: 160px; padding: 1px; margin-top:-25px;z-index: 500" /> <div style="position: absolute; margin-left: 192px; margin-top: -23px"></div>
					</div>
				</li>

				<?php
					} # end if disable_refresh

					if (isset($widgets) && is_array($widgets)) {
						echo '<li class="header">'.$this->translate->_('Available Widgets').'</li>'."\n";
						foreach($widgets as $widget) {
							$class_name = isset($widget->id) ? 'selected' : 'unselected';
							echo '<li id="li_'.$widget->name.'-'.$widget->instance_id.'" data-name="'.$widget->name.'" data-instance_id="'.$widget->instance_id.'" class="'.$class_name.' widget-selector" onclick="control_widgets(this)">'.$widget->friendly_name.'</li>'."\n";
						}
						echo '<li onclick="restore_widgets();">'.$this->translate->_('Restore overview to factory settings').'</li>'."\n";
						if ($authorized === true) {
							echo '<li onclick="widget_upload();">'.$this->translate->_('Upload new widget').'</li>'."\n";
						}
						echo '<li id=show_global_widget_refresh">'._('Set widget refresh rate (s.)').'</li>'."\n";
					}
				?>
			</ul>
		</div>
<?php 	if (isset($saved_searches) && !empty($saved_searches)) {
			echo $saved_searches;
		}
		 ?>
		<div id="save-search-form" title="<?php echo $this->translate->_('Save search') ?>" style="display:none">
			<form>
			<p class="validateTips"></p>
			<fieldset>
				<label for="search_query"><?php echo $this->translate->_('Search string') ?></label>
				<input type="text" name="search_query" id="search_query" value="<?php echo isset($query_str) ? $query_str : '' ?>" class="texts search_query ui-widget-content ui-corner-all" />
				<label for="search_name"><?php echo $this->translate->_('Name') ?></label>
				<input type="text" name="search_name" id="search_name" class="texts ui-widget-content ui-corner-all" />
				<label for="search_description"><?php echo $this->translate->_('Description') ?></label>
				<textarea cols="30" rows="3" name="search_description" id="search_description" class="texts ui-widget-content ui-corner-all"></textarea>
				<input type="hidden" name="search_id" id="search_id" value="0">
			</fieldset>
			</form>
		</div>
	</body>
</html>
