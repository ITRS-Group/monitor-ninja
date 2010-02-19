<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div style="padding:10px">
	<?php echo $title ?><br />
	<?php echo $report_time ?>
</div>
<table id="histogram_holder" style="width:auto">
	<tr>
		<td valign="top"><div id="histogram_graph" style="width:800px;height:300px"></div></td>
		<td valign="top">
			<div id="overviewLegend" style="width:50px"></div>
			<br />
			<p id="choices" style="padding-left:6px"><?php echo $this->translate->_('Show') ?>:</p>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table id="histogram_overview">
				<tr>
					<th><?php echo $label_eventtype ?></th>
					<th><?php echo $label_min ?></th>
					<th><?php echo $label_max ?></th>
					<th><?php echo $label_sum ?></th>
					<th><?php echo $label_avg ?></th>
				</tr>
					<?php foreach ($available_states as $state) { ?>
					<tr>
						<td><?php echo $states[$state] ?></td>
						<td><?php echo $min[$state] ?></td>
						<td><?php echo $max[$state] ?></td>
						<td><?php echo $sum[$state] ?></td>
						<td><?php echo $avg[$state] ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
	</tr>
</table>
