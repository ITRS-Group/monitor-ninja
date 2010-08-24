<?php defined('SYSPATH') OR die("No direct access allowed");?>

<div class="widget left w98">
	<h1><?php echo $label_overall_totals ?></h1>
	<p style="margin-top:-10px"><?php $this->_print_duration($options['start_time'], $options['end_time']); ?></p>
	<?php
	foreach ($result as $host_name => $ary) {
		$this->_print_alert_totals_table($label_host_alerts, $ary['host'], $host_state_names, $ary['host_totals'],$host_name);
		$this->_print_alert_totals_table($label_service_alerts, $ary['service'], $service_state_names, $ary['service_totals'],$host_name);
	}
	//printf("Report completed in %.3f seconds<br />\n", $completion_time);
	?>
</div>
