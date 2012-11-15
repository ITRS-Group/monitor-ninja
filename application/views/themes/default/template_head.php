<?php
	if (isset($this) && isset($this->template->js_header))
		$this->template->js_header->js = array_unique($this->xtra_js);
?>
<head>

<?php
if (PHP_SAPI == 'cli') {
	echo "<base href=\"https://localhost".url::base()."\" />";
}
else {
	echo (!empty($base_href)) ? '<base href="'.$base_href.'" />' : '';
}
?>

	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">

	<title><?php echo (isset($title)) ? Kohana::config('config.product_name').' » '.html::specialchars($title) : Kohana::config('config.product_name') ?></title>

	<?php echo html::link('application/views/themes/default/icons/16x16/favicon.ico','icon','image/icon') ?>

	<link href="<?php echo url::base().'application/views/themes/default/css/'.$current_skin.'common.css'; ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo url::base().'application/views/themes/default/css/'.$current_skin.'print.css'; ?>" type="text/css" rel="stylesheet" media="print" />
	<link href="<?php echo url::base().'application/views/themes/default/css/'.$current_skin.'handheld.css'; ?>" type="text/css" rel="stylesheet" media="handheld" />
	<link type="text/css" rel="stylesheet" href="<?php echo url::base().'application/views/themes/default/css/'.$current_skin.'reports.css' ?>" media="all" />
	<link type="text/css" rel="stylesheet" href="<?php echo url::base().'application/views/themes/default/css/'.$current_skin.'jquery-ui-custom.css' ?>" media="screen" />

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
		echo html::script('application/media/js/jquery.contextMenu.js');
		echo html::script('application/views/themes/default/js/pagination.js');
		echo html::script('application/views/themes/default/js/global_search.js');
		if (!isset($disable_refresh) || $disable_refresh === false) {
			refresh::control();
		}
	?>

	<!--[If IE]>
	<link type="text/css" rel="stylesheet" href="<?php echo url::base().'application/views/themes/default/css/default/ie7.css' ?>" />
	<?php echo (Router::$controller.'/'.Router::$method == 'histogram/generate') ? html::script('application/media/js/excanvas.compiled.js') : ''; ?>
	<![endif]-->

	<script type="text/javascript">
		//<!--
		<?php
			if (Auth::instance()->logged_in()) { ?>

			var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
			var _index_page = '<?php echo Kohana::config('config.index_page') ?>';
			var _current_uri = '<?php echo Router::$controller.'/'.Router::$method ?>';
			var _controller_name = '<?php echo str_replace("op5", null, Router::$controller) ?>';
			var _theme_path = '<?php echo 'application/views/themes/default/'; ?>';
			var _widget_refresh_msg = '<?php echo _('Refresh rate for all widgets has been updated to %s sec'); ?>';
			var _widget_refresh_error = '<?php echo _('Unable to update refresh rate for all widgets.'); ?>';
			var _widget_global_refresh_error = '<?php echo _('An error was encountered when trying to update refresh rate for all widgets.'); ?>';
			var _widget_order_error = '<?php echo _('Unable to fetch widget order from database.'); ?>';
			var _widget_settings_msg = '<?php echo _('Settings for widget %s was updated'); ?>';
			var _widget_settings_error = '<?php echo _('Unable to fetch setting for widget %s'); ?>';
			var _widget_notfound_error = '<?php echo _('Unable to find widget %s'); ?>';
			var _page_refresh_msg = '<?php echo _('Updated page refresh rate to %s seconds'); ?>';
			var _settings_msg = '<?php echo _('The settings were updated'); ?>';
			var _success_header = '<?php echo _('Success'); ?>';
			var _error_header = '<?php echo _('ERROR'); ?>';
			var _form_error_header = '<?php echo _("The form couldn\'t be processed since it contains one or more errors.%sPlease correct the following error(s) and try again:%s"); ?>';
			var _command_empty_field = '<?php echo _("Field \'%s\' is required but empty"); ?>';
			var _loading_str = '<?php echo _("Loading..."); ?>';
			var _wait_str='<?php echo _('Please wait') ?>';
			var _refresh_paused_msg='<?php echo _('Page refresh has been paused.') ?>';
			var _refresh_unpaused_msg='<?php echo _('Page refresh has been restored.') ?>';
			var _search_save_error = '<?php echo _("Length of \'%s\' must be between %s and %s characters.") ?>';
			var _search_string_field = '<?php echo _('Search string') ?>';
			var _search_remove_confirm = '<?php echo _('Are you sure that you wish to remove this saved search?') ?>';
			var _search_name_field = '<?php echo _('Name') ?>';
			var _search_save_ok = '<?php echo _('OK') ?>';
			var _search_save_error = '<?php echo _('ERROR') ?>';
			var _search_saved_ok = '<?php echo _('Your search was successfully saved.') ?>';
			var _search_saved_error = '<?php echo _('An error occured when trying to save your search.') ?>';
			var _nothing_selected_error = '<?php echo _('Please select at least one item.') ?>';
			var _no_action_error = '<?php echo _('Please select an action.') ?>';

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

	<?php echo html::script('application/views/themes/default/js/common.js'); ?>
	<?php echo (!empty($js_header)) ? $js_header : ''; ?>

</head>
