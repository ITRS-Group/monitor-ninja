<?php defined('SYSPATH') OR die("No direct access allowed"); ?>
<div class="report-block">
<?php
foreach ($result as $name => $ary) {
	# Services are special. Of course.
	if ($options['report_type'] == 'services') {
		if (strpos($name, ';') !== false)
			$obj_types = array('service' => 'service');
		else
			$obj_types = array('host' => 'host');
	} else {
		$obj_types = array('host' => 'host', 'service' => 'service');
	}

	# Hide tables for excluded alert types
	if (!($options['alert_types'] & 1) && isset($obj_types['host']))
		unset($obj_types['host']);

	if (!($options['alert_types'] & 2) && isset($obj_types['service']))
		unset($obj_types['service']);

	foreach ($obj_types as $objtype) {
		echo "<table class=\"host_alerts\">";
		echo "<caption>".sprintf(($objtype == 'host' ? _('Host alerts for %s') : _('Service alerts for %s')), $name).'</caption><thead><tr>';
		echo "<th>" . _('State') . "</th>\n";
		echo "<th>" . _('Soft Alerts') . "</th>\n";
		echo "<th>" . _('Hard Alerts') . "</th>\n";
		echo "<th>" . _('Total Alerts') . "</th>\n";
		echo "</tr></thead><tbody>\n";

		$total = array(0, 0); # soft and hard
		$i = 0;
		foreach ($ary[$objtype] as $state_id => $sh) {
			if (!isset(${$objtype.'_state_names'}[$state_id]))
				continue;
			$i++;
			echo "<tr class=\"".($i%2 == 0 ? 'odd' : 'even')."\">\n";
			echo "<td>" . ${$objtype.'_state_names'}[$state_id] . "</td>\n"; # topic
			echo "<td>" . $sh[0] . "</td>\n"; # soft
			echo "<td>" . $sh[1] . "</td>\n"; # hard
			$tot = $sh[0] + $sh[1];
			echo "<td>" . $tot . "</td>\n"; # soft + hard
			echo "</tr>\n";
		}
		$i++;
		echo "<tr class=\"".($i%2 == 0 ? 'odd' : 'even')."\"><td>Total</td>\n";
		echo "<td>" . $ary[$objtype.'_totals']['soft'] . "</td>\n";
		echo "<td>" . $ary[$objtype.'_totals']['hard'] . "</td>\n";
		$tot = $ary[$objtype.'_totals']['soft'] + $ary[$objtype.'_totals']['hard'];
		echo "<td>" . $tot . "</td>\n";
		echo "</tr></tbody></table><br />\n";
	}
}
?>
</div>
