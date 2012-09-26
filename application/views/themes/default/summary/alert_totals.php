<?php defined('SYSPATH') OR die("No direct access allowed"); ?>
<div class="report-block">
<p style="margin-top:-10px"><?php date::duration($options['start_time'], $options['end_time']); ?></p>
<?php
foreach ($result as $name => $ary) {
	$this->_print_alert_totals_table(_('Host Alerts'), $ary['host'], $host_state_names, $ary['host_totals'],$name);
	$this->_print_alert_totals_table(_('Service Alerts'), $ary['service'], $service_state_names, $ary['service_totals'],$name);
}
?>
</div>
