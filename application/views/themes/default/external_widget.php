<?php defined('SYSPATH') OR die('No direct access allowed.');
$current_skin = false;
$authorized = false;
if (Auth::instance()->logged_in()) {
	$ninja_menu_setting = Ninja_setting_Model::fetch_page_setting('ninja_menu_state', '/');

	$auth = Nagios_auth_Model::instance();
	if ($auth->view_hosts_root) {
		$authorized = true;
	}

	# fetch info on current skin
	$current_skin = config::get('config.current_skin', '*', true);
	if (!substr($current_skin, -1, 1) != '/') {
		$current_skin .= '/';
	}

}
if (!empty($ninja_menu_setting) && !empty($ninja_menu_setting->setting)) {
	$ninja_menu_state = $ninja_menu_setting->setting;
} else {
	$ninja_menu_state = 'show';
}
if (isset($this->template->js_header))
	$this->template->js_header->js = $this->xtra_js;
?>
<!DOCTYPE html>

<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo (isset($title)) ? Kohana::config('config.product_name').' » '.html::specialchars($title) : Kohana::config('config.product_name') ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/'.$current_skin.'common.css') ?>" media="screen" />
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/'.$current_skin.'status.css') ?>" media="screen" />
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/'.$current_skin.'print.css') ?>" media="print" />
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/default/jquery-ui-custom.css') ?>" />
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
				var _reports_link='<?php echo Kohana::config('reports.reports_link') ?>';
				var _search_save_error = '<?php echo $this->translate->_("Length of \'%s\' must be between %s and %s characters.") ?>';
				var _search_string_field = '<?php echo $this->translate->_('Search string') ?>';
				var _search_remove_confirm = '<?php echo $this->translate->_('Are you sure that you wish to remove this saved search?') ?>';
				var _search_name_field = '<?php echo $this->translate->_('Name') ?>';
				var _search_save_ok = '<?php echo $this->translate->_('OK') ?>';
				var _search_save_error = '<?php echo $this->translate->_('ERROR') ?>';
				var _search_saved_ok = '<?php echo $this->translate->_('Your search was successfully saved.') ?>';
				var _search_saved_error = '<?php echo $this->translate->_('An error occured when trying to save your search.') ?>';

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
				if (!empty($inline_js)) {
					echo "$(document).ready(function() {";
					echo $inline_js;
					echo "});";
				}?>
			//-->
		</script>
		<?php echo html::script($this->add_path('js/common.js')); ?>
		<?php echo (!empty($js_header)) ? $js_header : ''; ?>

	</head>


	<body>
		<div align="center">
			<?php if (isset($content)) { echo $content; } else { url::redirect(Kohana::config('routes.logged_in_default')); } ?>
			<!--<p>Rendered in {execution_time} seconds, using {memory_usage} of memory</p> -->
		</div>
	</body>
</html>
