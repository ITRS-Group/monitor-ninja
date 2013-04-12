<?php defined('SYSPATH') OR die("No direct access allowed"); ?>
<div class="report-block">
<?php
foreach ($result as $name => $ary) {
	foreach (array('host', 'service') as $objtype) {
		echo "<br /><table class=\"host_alerts\"><tr>\n";
		echo "<caption style=\"margin-top: 15px\">".sprintf(($objtype == 'host' ? _('Host alerts for %s') : _('Service alerts for %s')), $name).'</caption>';
		echo "<th class=\"headerNone\">" . _('State') . "</th>\n";
		echo "<th class=\"headerNone\">" . _('Soft Alerts') . "</th>\n";
		echo "<th class=\"headerNone\">" . _('Hard Alerts') . "</th>\n";
		echo "<th class=\"headerNone\">" . _('Total Alerts') . "</th>\n";
		echo "</tr>\n";

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
		echo "</tr></table><br />\n";
	}
}
?>
</div>
