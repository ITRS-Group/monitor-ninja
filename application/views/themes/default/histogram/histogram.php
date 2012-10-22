<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div>
<h1><?php echo $title ?></h1>
<?php
$print_limit = 3;
if (sizeof($objects) > $print_limit) {
	$show_objects = array_slice($objects, 0, $print_limit);
	$rest_objects = array_slice($objects, $print_limit); ?>
		<?php echo implode(', ', $show_objects); ?>
		...<a title="<?php echo _('Click to show/hide list of objects') ?>" href="#" id="show_all_objects"><?php echo sprintf(_('Show %s more'), sizeof($rest_objects)) ?></a>
		</h1>
	<div id="all_objects" style="display:none">
		<?php echo implode(', ', $rest_objects); ?>
	</div><br />
<?php
} else { ?>
	<?php echo implode(', ', $objects); ?>
<?php
} ?></h1>
<p style=""><?php echo $report_time ?></p>

<table id="histogram_holder" style="width:auto; margin-top: 15px">
	<tr>
		<td><div id="histogram_graph" style="width:800px;height:300px; margin-bottom: 15px"></div></td>

		<td style="vertical-align: bottom; padding-bottom: 30px">
			<p id="choices" style="padding-left:6px; margin-bottom: 7px;"><?php echo _('Show') ?>:</p>
			<div id="overviewLegend" style=" width: 40px"></div>
		</td>
	</tr>
	<tr>
		<td>
			<table id="histogram_overview">
				<tr>
					<th class="headerNone"><?php echo _('EVENT TYPE') ?></th>
					<th class="headerNone"><?php echo _('MIN') ?></th>
					<th class="headerNone"><?php echo _('MAX') ?></th>
					<th class="headerNone"><?php echo _('SUM') ?></th>
					<th class="headerNone"><?php echo _('AVG') ?></th>
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
		<td></td>
	</tr>
</table>
</div>
