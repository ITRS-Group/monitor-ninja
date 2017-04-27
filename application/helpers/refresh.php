<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This helper is intended to be used to control
 * the page reload (refresh) using javascript
 */
class refresh {

	/**
	 * Print javascript to control listview reload
	 */
	public static function lv_control()
	{
		# fetch setting
		$lv_refresh_key = 'config.listview_refresh_rate';
		$lv_refresh = (int)config::get($lv_refresh_key);
		?>
		<script type="text/javascript">
		_lv_refresh_key = '<?php echo $lv_refresh_key ?>';
		_lv_refresh_delay = '<?php echo $lv_refresh ?>';
		</script>
		<?php
	}
}
