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
		var sURL = unescape(window.location.pathname + location.search);
		var _interval = 0;
		var _save_page_interval = 0;
		var current_interval = 0;
		var edit_visible = 0;

		$(document).ready(function() {
			ninja_refresh(<?php echo $refresh ?>);
			var old_refresh = 0;
			$("#ninja_refresh_control").bind('change', function() {
				if ($("#ninja_refresh_control").attr('checked')) {
					// save previous refresh rate
					// to be able to restore it later
					old_refresh = current_interval;
					$('#ninja_refresh_lable').css('font-weight', 'bold');
					ninja_refresh(0);
				} else {
					// restore previous refresh rate
					ninja_refresh(old_refresh);
					$('#ninja_refresh_lable').css('font-weight', '');
				}
			});
			if ($('#ninja_refresh_edit').text()!='') {
				create_slider('ninja_page_refresh');
			}
			$('#ninja_refresh_edit').bind('click', function() {
				if (!edit_visible) {
					$('#ninja_page_refresh_slider').show();
					edit_visible = 1;
				} else {
					$('#ninja_page_refresh_slider').hide();
					edit_visible = 0;
				}
			});
		});

		function create_slider(the_id)
		{
			$("#" + the_id + "_slider").slider({
				value: current_interval,
				min: 0,
				max: 500,
				step: 10,
				slide: function(event, ui) {
					$("#" + the_id + "_value").val(ui.value);
					current_interval = ui.value;
					control_save_refreshInterval();
					ninja_refresh(ui.value);
				}
			});
			// set slider position according to current_interval
			$("#" + the_id + "_slider").slider("value", current_interval);
			$('input[name=' + the_id + '_value]').val(current_interval);

		}

		function control_save_refreshInterval() {
			if (_save_page_interval) {
				clearTimeout(_save_page_interval);
			}
			_save_page_interval = setTimeout("save_refreshInterval()", 5000);
		}

		function save_refreshInterval()
		{
			var url = _site_domain + _index_page + "/ajax/save_page_setting/";
			var data = {page: '*', setting: current_interval, type: '<?php echo $refresh_key ?>'};
			$.post(url, data);
			$.jGrowl('Updated page refresh rate to ' + current_interval + ' seconds', { header: 'Success' });
		}

		function ninja_refresh(val)
		{
			if (_interval) {
				clearInterval(_interval);
			}
			var refresh_val = (val == null) ? <?php echo $refresh ?> : val;
			current_interval = refresh_val;
			if (val>0) {
				_interval = setInterval( "refresh()", refresh_val*1000 );
			}
		}

		function refresh() {window.location.href = sURL;}
		//-->
		</script>
		<script type="text/javascript" language="JavaScript1.1"><!-- function refresh() {window.location.replace( sURL );} //--></script>
		<script type="text/javascript" language="JavaScript1.2"><!-- function refresh() {window.location.reload( false );} //--></script>
		<?php
	}
}

?>
