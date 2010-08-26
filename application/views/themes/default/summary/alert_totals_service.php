<?php defined('SYSPATH') OR die("No direct access allowed");
if (isset($schedules)) {
	echo $schedules;
}
?>

<div class="widget left w98">
	<h1><?php echo $label_overall_totals ?></h1>
	<p style="margin-top:-10px"><?php $this->_print_duration($options['start_time'], $options['end_time']); ?></p>
		<?php
		foreach ($result as $service_name => $ary) {
			$foo = explode(';', $service_name);
			$host_name = $foo[0];
			$service = $foo[1];
			//echo $label_service . "'" . $service . "' on " .$label_host . "'" . $host_name . "'<br />\n";
			$name = $service .' on '.$label_host.': '.$host_name;
			$this->_print_alert_totals_table($label_service_alerts, $ary['service'], $service_state_names, $ary['service_totals'], $name);
		}
		//printf("Report completed in %.3f seconds<br />\n", $completion_time);
		?>
</div>
