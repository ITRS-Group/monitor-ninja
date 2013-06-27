<?php defined('SYSPATH') OR die('No direct access allowed.'); $i = 0;
if ($options['report_type'] === 'hosts' || $options['report_type'] === 'hostgroups') {
	$var_types = array('UP' => 'UP', 'DOWN' => 'DOWN', 'UNREACHABLE' => 'UNREACHABLE');
	$filter_name = 'host_filter_status';
	$states = 'host_states';
} else {
	$var_types = array('OK' => 'OK', 'WARNING' => 'WARNING', 'UNKNOWN' => 'UNKNOWN', 'CRITICAL' => 'CRITICAL');
	$filter_name = 'service_filter_status';
	$states = 'service_states';
}

foreach (array_keys($options[$filter_name]) as $filtered) {
	$php_sucks = Reports_Model::$$states; # No, you cannot do this on one line, because bugs
	unset($var_types[strtoupper($php_sucks[$filtered])]);
}
foreach ($report_data as $avail_data) {
	if (!is_array($avail_data) || !isset($avail_data['states']))
		continue;
?>
<div id="state_breakdown" class="report-block">
	<table summary="<?php echo _('Result table') ?>">
		<tr>
			<th><?php echo help::render('availability') ?></th>
			<th><?php echo _('Type / Reason') ?></th>
			<th class="headerNone"><?php echo _('Time') ?></th>
			<th class="headerNone"><?php echo _('Total time') ?></th>
			<?php if ($options['include_pie_charts']) { ?>
				<th><?php echo _('Status overview') ?></th>
			<?php } ?>
		</tr>
		<?php
		foreach ($var_types as $var_type) { $i++; ?>
		<tr class="even" >
			<th style="border-top: 0px; vertical-align: bottom; width: 110px" rowspan="3">
					<?php echo ucfirst(strtolower($var_type)); ?>
			</th>
			<td><?php echo _('Unscheduled') ?></td>
			<td class="data" style="width: 80px"><?php echo time::to_string($avail_data['states']['TIME_' . $var_type .'_UNSCHEDULED']) ?></td>
			<td class="data" style="width: 80px"><?php echo reports::format_report_value($avail_data['states']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['states']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) > 0 ? '' : 'not-').strtolower($var_type).'.png'),
				array('alt' => strtolower($var_type),'title' => strtolower($var_type),'style' => 'height: 12px; width: 12px')); ?>
			</td>
			<?php if ($i == 1 && isset($pies)) { ?>
			<td rowspan="<?php echo count($var_types)*3+4; ?>" style="width: 200px; vertical-align: top">
				<?php foreach ($pies as $pie) echo $pie ?>
			</td>
			<?php } ?>
		</tr>
		<tr class="even">
			<td style="border-left: 0px"><?php echo _('Scheduled') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['states']['TIME_' . $var_type .'_SCHEDULED']) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['states']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['states']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) > 0 ? '' : 'not-').strtolower($var_type).'.png'),
				array('alt' => strtolower($var_type),'title' => strtolower($var_type),'style' => 'height: 12px; width: 12px')); ?>
			<?php if ($options['scheduleddowntimeasuptime'] == 2 && ($var_type === 'UP' || $var_type == 'OK') && ($avail_data['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP'] > 0)) { print '<br />('.reports::format_report_value($avail_data['states']['PERCENT_TIME_DOWN_COUNTED_AS_UP']).'% in other states)'; } ?>
			</td>
		</tr>
		<tr class="dark">
			<td><?php echo _('Total') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['states']['KNOWN_TIME_' . $var_type]) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['states']['PERCENT_KNOWN_TIME_' . $var_type]) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['states']['PERCENT_KNOWN_TIME_' . $var_type]) > 0 ? '' : 'not-').strtolower($var_type).'.png'),
				array('alt' => strtolower($var_type),'title' => strtolower($var_type),'style' => 'height: 12px; width: 12px')); ?>
			</td>
		</tr>
		<?php }
		if (!isset($options[$filter_name][Reports_Model::HOST_PENDING])) {
		?>
		<tr class="even">
			<th style="vertical-align: bottom; border-top: 0px" rowspan="3">
				<?php echo _('Undetermined') ?>
			</th>
			<td><?php echo _('Not running') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['states']['TIME_UNDETERMINED_NOT_RUNNING']) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['states']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['states']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => _('Undetermined'),'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')); ?> </td>
		</tr>
		<tr class="even">
			<td style="border-left: 0px"><?php echo _('Insufficient data') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['states']['TIME_UNDETERMINED_NO_DATA']) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['states']['PERCENT_TIME_UNDETERMINED_NO_DATA']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['states']['PERCENT_TIME_UNDETERMINED_NO_DATA']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => _('Undetermined'),'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')); ?></td>
		</tr>
		<tr class="dark total undetermined">
			<td><?php echo _('Total') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['states']['TOTAL_TIME_UNDETERMINED']) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['states']['PERCENT_TOTAL_TIME_UNDETERMINED']!=0 ? $avail_data['states']['PERCENT_TOTAL_TIME_UNDETERMINED'] : reports::format_report_value(0)) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['states']['PERCENT_TOTAL_TIME_UNDETERMINED']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => _('Undetermined'),'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')); ?></td>
		</tr>
		<?php } ?>
		<tr class="even total">
			<th style="border-top: 0px"><?php echo _('All') ?></th>
			<td><?php echo _('Total') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['states']['TOTAL_TIME_ACTIVE'] - $avail_data['states']['TOTAL_TIME_EXCLUDED']) ?></td>
			<td class="data"><?php echo reports::format_report_value(100 - $avail_data['states']['PERCENT_TOTAL_TIME_EXCLUDED']) ?> %</td>
		</tr>
	</table>
</div>
<?php
}
