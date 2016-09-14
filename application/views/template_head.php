<head>

<?php
if (!empty($base_href)) {
	echo (!empty($base_href)) ? '<base href="'.$base_href.'" />' : '';
}
?>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">

	<title><?php echo (isset($title)) ? Kohana::config('config.product_name').' » '.html::specialchars($title) : Kohana::config('config.product_name') ?></title>

	<?php
		echo html::link('application/views/icons/favicon.ico','icon','image/x-icon');
		echo html::link('application/media/css/jquery.fancybox.css', 'stylesheet', 'text/css', false, 'screen');
		echo html::link('application/media/css/lib.popover.css', 'stylesheet', 'text/css', false, 'screen');
		echo html::link('application/media/css/lib.notify.css', 'stylesheet', 'text/css', false, 'screen');
		echo html::link('application/media/css/c3.css', 'stylesheet', 'text/css', false, 'screen');
		echo html::link('application/media/css/lightbox.css', 'stylesheet', 'text/css', false, 'screen');
	?>

	<link href="<?php echo ninja::add_path('css/layout.css'); ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo ninja::add_path('css/form.css'); ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo ninja::add_path('css/icons.css'); ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo ninja::add_path('css/ninja-icons.css'); ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo ninja::add_path('css/'.$current_skin.'common.css'); ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo ninja::add_path('css/'.$current_skin.'print.css'); ?>" type="text/css" rel="stylesheet" media="print" />
	<link type="text/css" rel="stylesheet" href="<?php echo ninja::add_path('css/'.$current_skin.'jquery-ui-custom.css') ?>" media="screen" />
	<?php
		$v = new View('css_header', array('css' => isset($css)?$css:array()));
		$v->render(true);
	?>
<script type="text/javascript">
/* Hack for lack of console.log() in ie7 */
		if (!window.console) console = {log: function() {}, error: function() {}, dir: function() {}};
		var listview_renderer_table = {};
		var listview_renderer_buttons = {};
		var listview_renderer_extra_objects = {};
		var listview_renderer_totals = {};
</script>

	<script type="text/javascript">
		//<!--
		<?php
			$cgi_esc_html_tags = config::get_cgi_cfg_key('escape_html_tags');
			if (empty($cgi_esc_html_tags)) {
				$cgi_esc_html_tags = 0;
			}
			?>

			var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
			var _csrf_token = '<?php echo Session::instance()->get(Kohana::config('csrf.csrf_token')) ?>';
			var _index_page = '<?php echo Kohana::config('config.index_page') ?>';
			var _current_uri = '<?php echo Router::$controller.'/'.Router::$method ?>';
			var _controller_name = '<?php echo str_replace("op5", null, Router::$controller) ?>';
			var _logo_path = '<?php echo Kohana::config('config.logos_path').(substr(Kohana::config('config.logos_path'), -1) == '/' ? '' : '/'); ?>';
			var _escape_html_tags = <?php echo $cgi_esc_html_tags ?>;
			var _widget_refresh_msg = '<?php echo _('Refresh rate for all widgets has been updated to %s sec'); ?>';
			var _widget_refresh_error = '<?php echo _('Unable to update refresh rate for all widgets.'); ?>';
			var _widget_global_refresh_error = '<?php echo _('An error was encountered when trying to update refresh rate for all widgets.'); ?>';
			var _widget_order_error = '<?php echo _('Unable to fetch widget order from database.'); ?>';
			var _widget_settings_msg = '<?php echo _('Settings for widget %s was updated'); ?>';
			var _widget_settings_error = '<?php echo _('Unable to fetch setting for widget %s'); ?>';
			var _widget_notfound_error = '<?php echo _('Unable to find widget %s'); ?>';
			var _page_refresh_msg = '<?php echo _('Updated page refresh rate to {delay} seconds'); ?>';
			var _listview_refresh_msg = '<?php echo _('Updated list view refresh rate to {delay} seconds'); ?>';
			var _settings_msg = '<?php echo _('The settings were updated'); ?>';
			var _success_header = '<?php echo _('Success'); ?>';
			var _error_header = '<?php echo _('ERROR'); ?>';
			var _form_error_header = '<?php echo _("The form couldn\'t be processed since it contains one or more errors.%sPlease correct the following error(s) and try again:%s"); ?>';
			var _command_empty_field = '<?php echo _("Field \'%s\' is required but empty"); ?>';
			var _loading_str = '<?php echo _("Loading..."); ?>';
			var _wait_str='<?php echo _('Please wait') ?>';
			var _refresh_paused_msg='<?php echo _('Page refresh has been paused.') ?>';
			var _refresh_unpaused_msg='<?php echo _('Page refresh has been restored.') ?>';
			var _listview_refresh_paused_msg='<?php echo _('List view refresh has been paused.') ?>';
			var _listview_refresh_unpaused_msg='<?php echo _('List view refresh has been restored.') ?>';
			var _nothing_selected_error = '<?php echo _('Please select at least one item.') ?>';
			var _no_action_error = '<?php echo _('Please select an action.') ?>';
			var _date_format = <?php echo json_encode(date::date_format()); ?>;
			var _server_utc_offset = <?php echo date::utc_offset(date_default_timezone_get()); ?>;
			var _notes_url_target = "<?php echo config::get('nagdefault.notes_url_target'); ?>";
			var _action_url_target = "<?php echo config::get('nagdefault.action_url_target'); ?>";
			var _pnp_web_path = "<?php echo Kohana::config('config.pnp4nagios_path'); ?>";

		<?php	if ($keycommands_active === 1) {	?>

				var _keycommands_active='<?php echo config::get('keycommands.activated'); ?>';
				var _keycommand_search='<?php echo config::get('keycommands.search'); ?>';
				var _keycommand_pause='<?php echo config::get('keycommands.pause'); ?>';
				var _keycommand_forward='<?php echo config::get('keycommands.forward'); ?>';
				var _keycommand_back='<?php echo config::get('keycommands.back'); ?>';
		<?php 	} else { ?>
				var _keycommands_active='0';
		<?php 	} ?>

			var _popup_delay='<?php echo config::get('config.popup_delay'); ?>';

			var loading_img = '/application/media/images/loading.gif';
			<?php $auth_user = op5auth::instance()->get_user(); ?>
			var _user = <?php echo json_encode(array(
				'username' => $auth_user->get_username(),
				'realname' => $auth_user->get_realname(),
				'auth_data' => $auth_user->get_auth_data())); ?>
		//-->
	</script>
	<?php

		$v = new View('js_header', array('js' => isset($js)?$js:array()));
		$v->render(true);

		refresh::lv_control();
		if (!isset($disable_refresh) || $disable_refresh === false) {
			refresh::control();
		}

	?>
	<script type="text/javascript">
		//<!--
			<?php
			if (!empty($js_strings)) {
				echo $js_strings;
			}
			if (!empty($inline_js)) {
				echo '$(document).ready(function() {';
				echo $inline_js;
				echo "});";
			}?>
		//-->
	</script>


</head>
