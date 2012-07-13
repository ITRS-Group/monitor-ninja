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
		<script type="text/javascript" language="JavaScript">
		<!--
		var _refresh_key = '<?php echo $refresh_key ?>';
		var _refresh = '<?php echo $refresh ?>';
		$(document).ready(function() {
			ninja_refresh(<?php echo $refresh ?>);
		});

		function refresh() {window.location.href = sURL;}
		//-->
		</script>
		<script type="text/javascript" language="JavaScript1.1"><!-- function refresh() {window.location.replace( sURL );} //--></script>
		<?php
	}

	/**
	*	Add ajax checks for freshness to be ued when page
	* 	refresh is disabled.
	*/
	public static function is_alive()
	{
		$interval = Kohana::config('config.stale_data_limit');
		?>
			<script type="text/javascript" language="JavaScript">
			<!--
				var _stale_check_interval = <?php echo $interval ?>;
				$(document).ready(function() {
					setTimeout('check_alive()', 1200);
				});

				function check_alive()
				{
					if (typeof _is_refreshing != 'undefined' && _is_refreshing) {
						return;
					}

					var url = _site_domain + _index_page + "/ajax/is_alive/";
					$.ajax({
						url: url,
						type: 'GET',
						dataType: 'json',
						success: function(data) {
							if (data.result) {
								$('#page_last_updated').html(data.result);
								$('#infobar-sml').hide();
							} else {
								$('#infobar-sml').show();
								$('#page_last_updated').html(data.error);
							}
						},
						error: function() {
							$('#infobar-sml').show();
						}
					});

 					setTimeout('check_alive()', _stale_check_interval * 1000);
				}
			//-->
			</script>

		<?php
	}
}

?>
