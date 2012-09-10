<?php defined('SYSPATH') OR die("No direct access allowed");
if (isset($schedules)) {
	echo $schedules;
}
?>

<div class="left w98">
	<h1><?php echo _('Overall Totals') ?></h1>
	<p style="margin-top:-10px"><?php $this->_print_duration($options['start_time'], $options['end_time']); ?></p>
	<?php
		foreach ($result as $hg_name => $ary) {
			$this->_print_alert_totals_table(_('Host Alerts'), $ary['host'], $host_state_names, $ary['host_totals'], $hg_name);
			$this->_print_alert_totals_table(_('Service Alerts'), $ary['service'], $service_state_names, $ary['service_totals'], $hg_name);
		}
		//printf("Report completed in %.3f seconds<br />\n", $completion_time);
	?>
</div>

