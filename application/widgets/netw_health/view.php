<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm" id="widget-network_health">
	<div class="widget-header">
		<strong>Network health</strong>
	</div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<!--This is widget content:<br /><br />-->

		<table cellpadding="0" cellspacing="0" id="netw_health">
		<tr>
			<td valign="bottom" id="host_health" align="center">
				<div class="netw_health_label"><?php echo $host_value ?> %</div>
				<div><?php echo html::image($host_image, array('height' => $host_value, 'width' => '100%')) ?></div>
				<div class="netw_health_label">HOSTS</div>
			</td>
			<td valign="bottom" id="service_health" align="center">
				<div class="netw_health_label"><?php echo $service_value ?> %</div>
				<div><?php echo html::image($service_image, array('height' => $service_value, 'width' => '100%')) ?></div>
				<div class="netw_health_label">SERVICES</div>
			</td>
		</tr>
		</table>
<?php

	if (!empty($arguments)) {
		foreach ($arguments as $arg) {
			echo $arg."<br />";
		}
	}
?>
	</div>
</div>

