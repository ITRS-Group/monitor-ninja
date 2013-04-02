<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This helper is intended to be used to control
 * the page reload (refresh) using javascript
 */
class refresh_Core {

	/**
	*	Print javascript to control page reload
	*	Modified from http://grizzlyweb.com/webmaster/javascripts/refresh.asp
	*/
	public static function control()
	{
		if (!Auth::instance()->logged_in()) {
			return;
		}
		# fetch setting
		$refresh_key = 'config.page_refresh_rate';
		$refresh = (int)config::get($refresh_key, '*', true, true);
		?>
		<script>
		var _refresh_key = '<?php echo $refresh_key ?>';
		var _refresh = '<?php echo $refresh ?>';
		$(document).ready(function() {
			ninja_refresh(<?php echo $refresh ?>);
		});

		function refresh() {window.location.replace(sURL);}
		</script>
		<?php
	}

	/**
	 *	Print javascript to control listview reload
	 *
	 * @return void
	 **/
	public static function lv_control()
	{
		if (!Auth::instance()->logged_in()) {
			return;
		}
		# fetch setting
		$lv_refresh_key = 'config.listview_refresh_rate';
		$lv_refresh = (int)config::get($lv_refresh_key, '*', true, true) * 1000;
		?>
		<script type="text/javascript">
		lv_refresh_delay = '<?php echo $lv_refresh ?>';
		</script>
		<?php
	}
}
