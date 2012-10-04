<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="state_breakdown" class="report-block">
	<table summary="<?php echo _('Result table') ?>">
		<!--<caption><?php echo str_replace(': ', ' '._('for').' '.$source.': ', $header_string); ?></caption>-->
		<tr>
			<th class="headerNone left"><?php echo help::render('availability') ?></th>
			<th class="headerNone left"><?php echo _('Type / Reason') ?></th>
			<th class="headerNone"><?php echo _('Time') ?></th>
			<th class="headerNone"><?php echo _('Total time') ?></th>
			<th class="headerNone left"><?php echo _('Status overview') ?></th>
		</tr>
		<?php $no_types = count($avail_data['var_types'] ); $i = 0; foreach ($avail_data['var_types'] as $var_type) { $i++; ?>
		<tr class="even" >
			<th class="headerNone left" style="border-top: 0px; vertical-align: bottom; width: 110px" rowspan="3">
					<?php echo ucfirst(strtolower($state_values[$var_type])); ?>
			</th>
			<td><?php echo _('Unscheduled') ?></td>
			<td class="data" style="width: 80px"><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_UNSCHEDULED']) ?></td>
			<td class="data" style="width: 80px"><?php echo reports::format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'height: 12px; width: 12px')); ?>
			</td>
			<?php if ($i == 1 && isset($pie)) { ?>
			<td rowspan="<?php echo $no_types*3+4; ?>" style="width: 200px; vertical-align: top">
				<?php echo $pie ?>
			</td>
			<?php } ?>
		</tr>
		<tr class="even">
			<td style="border-left: 0px"><?php echo _('Scheduled') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_SCHEDULED']) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'height: 12px; width: 12px')); ?>
			<?php if ($options['scheduleddowntimeasuptime'] == 2 && ($var_type === 'UP' || $var_type == 'OK') && ($avail_data['values']['PERCENT_TIME_DOWN_COUNTED_AS_UP'] > 0)) { print '<br />('.reports::format_report_value($avail_data['values']['PERCENT_TIME_DOWN_COUNTED_AS_UP']).'% in other states)'; } ?>
			</td>
		</tr>
		<tr class="dark">
			<td><?php echo _('Total') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['KNOWN_TIME_' . $var_type]) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'height: 12px; width: 12px')); ?>
			</td>
		</tr>
		<?php } ?>
		<tr class="even">
			<th class="headerNone left" style="vertical-align: bottom; border-top: 0px" rowspan="3">
				<?php echo _('Undetermined') ?>
			</th>
			<td><?php echo _('Not running') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NOT_RUNNING']) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => _('Undetermined'),'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')); ?> </td>
		</tr>
		<tr class="even">
			<td style="border-left: 0px"><?php echo _('Insufficient data') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NO_DATA']) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NO_DATA']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NO_DATA']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => _('Undetermined'),'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')); ?></td>
		</tr>
		<tr class="dark total undetermined">
			<td><?php echo _('Total') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['TOTAL_TIME_UNDETERMINED']) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']!=0 ? $avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED'] : reports::format_report_value(0)) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => _('Undetermined'),'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')); ?></td>
		</tr>
		<tr class="even total">
			<th class="headerNone left" style="border-top: 0px"><?php echo _('All') ?></th>
			<td><?php echo _('Total') ?></td>
			<td class="data"><?php echo time::to_string($avail_data['tot_time']) ?></td>
			<td class="data"><?php echo reports::format_report_value($avail_data['tot_time_perc']) ?> %</td>
		</tr>
	</table>
</div>
