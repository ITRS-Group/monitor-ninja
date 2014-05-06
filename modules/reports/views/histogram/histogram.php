<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="report_block">

<table id="histogram_holder">
	<tr>
		<td><div id="histogram_graph" style="width:800px;height:300px; margin-bottom: 15px"></div></td>

		<td style="vertical-align: bottom; padding-bottom: 30px; width: 100%;">
			<p id="choices" style="padding-left:6px; margin-bottom: 7px;"><?php echo _('Show') ?>:</p>
			<div id="overviewLegend" style=" width: 40px"></div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table id="histogram_overview">
				<tr>
					<th><?php echo _('EVENT TYPE') ?></th>
					<th><?php echo _('MIN') ?></th>
					<th><?php echo _('MAX') ?></th>
					<th><?php echo _('SUM') ?></th>
					<th><?php echo _('AVG') ?></th>
				</tr>
					<?php $i=0; foreach ($available_states as $state) { $i++;?>
					<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
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
</div>
<div class="report_block">
<h3><?php echo (substr($options['report_type'], 0, 4) == 'host') ? _("Included hosts") : _("Included services"); ?></h3>
<table>
<?php
$i = 0;
foreach ($options->get_report_members() as $object) {
	echo '<tr class="'.($i++ % 2 ? 'even' : 'odd').'"><td>'.$object.'</td></tr>';
} ?>
</table>
</div>
