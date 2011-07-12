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
	public function control()
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
	public function is_alive()
	{
		$interval = Kohana::config('config.stale_data_limit');
		?>
			<script type="text/javascript" language="JavaScript">
			<!--
				var _stale_check_interval = <?php echo $interval ?>;
				$(document).ready(function() {
					check_alive();
				});

				function check_alive()
				{
					var url = _site_domain + _index_page + "/ajax/is_alive/";
					$.ajax({
						url: url,
						type: 'GET',
						success: function(data) {
							if (data !='' && data > 0) {
								$('#infobar-sml').show();
							} else {
								$('#infobar-sml').hide();
							}
						}
					});

					var url = _site_domain + _index_page + "/ajax/current_time/";
					$.ajax({
						url: url,
						type: 'GET',
						success: function(data) {
							if (data !='') {
								$('#page_last_updated').html(data);
							} // don't touch in case of error
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
