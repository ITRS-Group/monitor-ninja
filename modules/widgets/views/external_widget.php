<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html>

<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo (isset($title)) ? Kohana::config('config.product_name').' » '.html::specialchars($title) : Kohana::config('config.product_name') ?></title>

		<link type="text/css" rel="stylesheet" href="<?php echo ninja::add_path('css/icons.css'); ?>" media="all" />
		<link type="text/css" rel="stylesheet" href="<?php echo ninja::add_path('css/layout.css'); ?>" media="all" />
		<link type="text/css" rel="stylesheet" href="<?php echo ninja::add_path('css/'.$current_skin.'common.css'); ?>" media="all" />
		<link type="text/css" rel="stylesheet" href="<?php echo ninja::add_path('css/'.$current_skin.'print.css'); ?>" media="print" />
		<link type="text/css" rel="stylesheet" href="<?php echo ninja::add_path('css/default/jquery-ui-custom.css') ?>" />
		<?php
		echo html::link('application/views/icons/favicon.ico','icon','image/x-icon');
		echo html::link('application/media/css/jquery.fancybox.css', 'stylesheet', 'text/css', false, 'screen');
		?>
		<!--[If IE]>
		<link type="text/css" rel="stylesheet" href="<?php echo ninja::add_path('css/default/ie7.css') ?>" />
		<?php echo (Router::$controller.'/'.Router::$method == 'histogram/generate') ? html::script('application/media/js/excanvas.compiled.js') : ''; ?>
		<![endif]-->
		<?php
			echo new View('css_header', array('css' => $css));
			echo html::script('application/media/js/jquery.js');
		?>
		<script type="text/javascript">
		$.ajaxSetup({
			'data': {
				'request_context': 'external_widget'
			}
		});
		</script>
		<?php
			echo html::script('application/media/js/jquery.fancybox.js');
			echo html::script('application/media/js/jquery-ui.js');
			echo html::script('application/media/js/jquery.form.js');
			echo html::script('modules/widgets/media/js/jquery.easywidgets.min.js');
			echo html::script('application/media/js/jquery.autocomplete.js');
			echo html::script('application/media/js/jquery.selectboxes.min.js');
			echo html::script('application/media/js/jquery.jeditable.min');
			echo html::script('application/media/js/jquery.query.js');
			echo html::script('application/media/js/jquery.hotkeys.min.js');
			echo html::script('application/media/js/jquery.field.js');
			echo html::script('application/media/js/date.js');
			echo html::script('application/views/js/global_search.js');
			echo html::script('application/views/js/pagination.js');
			if (!isset($disable_refresh) || $disable_refresh === false) {
				refresh::control();
			}
		?>

		<script type="text/javascript">
			//<!--
			<?php
				$cgi_esc_html_tags = config::get_cgi_cfg_key('escape_html_tags');
				if (empty($cgi_esc_html_tags)) {
					$cgi_esc_html_tags = 0;
				}
				if (Auth::instance()->logged_in()) { ?>

				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _csrf_token = '<?php echo Session::instance()->get(Kohana::config('csrf.csrf_token')) ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';
				var _current_uri = '<?php echo Router::$controller.'/'.Router::$method ?>';
				var _controller_name = '<?php echo Router::$controller ?>';
				var _logo_path = '<?php echo Kohana::config('config.logos_path').(substr(Kohana::config('config.logos_path'), -1) == '/' ? '' : '/'); ?>';
				var _escape_html_tags = <?php echo $cgi_esc_html_tags ?>;
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
				var _date_format = <?php echo json_encode(date::date_format()); ?>;
				var _server_utc_offset = <?php echo date::utc_offset(date_default_timezone_get()); ?>;
				var _notes_url_target = "<?php echo config::get('nagdefault.notes_url_target'); ?>";
				var _action_url_target = "<?php echo config::get('nagdefault.action_url_target'); ?>";

			<?php	if (config::get('keycommands.activated')) {	?>

					var _keycommands_active='<?php echo config::get('keycommands.activated'); ?>';
					var _keycommand_search='<?php echo config::get('keycommands.search'); ?>';
					var _keycommand_pause='<?php echo config::get('keycommands.pause'); ?>';
					var _keycommand_forward='<?php echo config::get('keycommands.forward'); ?>';
					var _keycommand_back='<?php echo config::get('keycommands.back'); ?>';
			<?php 	} else { ?>
					var _keycommands_active='0';
			<?php 	} ?>

				var _popup_delay='<?php echo config::get('config.popup_delay'); ?>';
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

				<?php $auth_user = op5auth::instance()->get_user(); ?>
				var _user = <?php echo json_encode(array(
					'username' => $auth_user->get_username(),
					'realname' => $auth_user->get_realname(),
					'auth_data' => $auth_user->get_auth_data())); ?>
			//-->
		</script>
		<?php
		$basepath = 'modules/lsfilter/';
		echo html::script($basepath.'media/js/lib.js');
		echo html::script($basepath.'media/js/LSFilterSaved.js'); ?>
		<?php echo html::script('application/views/js/common.js'); ?>
		<?php
			$mangled_js = array();
			foreach($js as $orig_js) {
				$delim = (strpos($orig_js, '?') === false) ? '?' : '&';
				$mangled_js[] = $orig_js . $delim . 'request_context=external_widget';
			}
			$js_view = new View('js_header', array('js' => $mangled_js));
			$js_view->render(true);
		?>

	</head>


	<body>
		<?php echo $widget->render('index', false); ?>
	</body>
</html>
