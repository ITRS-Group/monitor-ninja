<?php defined('SYSPATH') OR die("No direct access allowed");?>

<div style="margin: 0px 1%; border: 1px solid white">
	<h2 style="margin-top: 11px; margin-bottom: 0px"><?php echo $label_overall_totals ?></h2>

		<?php
		foreach ($result as $service_name => $ary) {
			$foo = explode(';', $service_name);
			$host_name = $foo[0];
			$service = $foo[1];
			//echo $label_service . "'" . $service . "' on " .$label_host . "'" . $host_name . "'<br />\n";
			$name = $service .' on '.$label_host.': '.$host_name;
			$this->_print_alert_totals_table($label_service_alerts, $ary['service'], $service_state_names, $ary['service_totals'], $name.);
		}
		//printf("Report completed in %.3f seconds<br />\n", $completion_time);
		?>
</div>
