<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="state_breakdown" style="margin-top: 15px">

	<div class="icon-help" onclick="general_help('avail')"></div>
	<fieldset id="avail">
		<table summary="Result table">
			<caption><?php echo str_replace(': ', ' '.$this->translate->_('for').' '.$source.': ', $header_string); ?></caption>
			<tr>
				<th class="headerNone"><?php echo $this->translate->_('Status') ?></th>
				<th class="headerNone"><?php echo $label_type_reason ?></th>
				<th class="headerNone"><?php echo $label_time ?></th>
				<th class="headerNone"><?php echo $label_tot_time ?></th>
			</tr>
			<?php $i = 0; foreach ($avail_data['var_types'] as $var_type) { $i++; ?>
			<tr class="even<?php //echo ($i%2 == 0) ? 'odd' : 'even'?>">
				<td class="status icon label <?php echo strtolower($var_type); ?>-left" rowspan="3">

					<?php echo html::image($this->add_path('icons/24x24/shield-'.strtolower($state_values[$var_type]).'.png'),
							array(
								'alt' => strtolower($state_values[$var_type]),
								'title' => strtolower($state_values[$var_type]),
								'style' => 'cursor: pointer; margin-bottom: -4px')
							) ?>
						<?php //echo ucfirst(strtolower($state_values[$var_type])); ?>
				</td>
				<td><?php echo $label_unscheduled ?></td>
				<td><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_UNSCHEDULED']) ?></td>
				<td><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) ?> %</td>
			</tr>
			<tr class="even<?php //echo ($i%2 == 0) ? 'even' : 'odd'?>">
				<td><?php echo $label_scheduled ?></td>
				<td><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_SCHEDULED']) ?></td>
				<td><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) ?> %</td>
			</tr>
			<tr class="dark<?php //echo ($i%2 == 0) ? 'odd' : 'even'?> total <?php echo strtolower($var_type); ?>">
				<td><?php echo $label_total ?></td>
				<td><?php echo time::to_string($avail_data['values']['KNOWN_TIME_' . $var_type]) ?></td>
				<td><?php echo $this->_format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) ?> %</td>
			</tr>

			<?php } ?>
			<tr class="even">
				<td class="status icon label undetermined-left" rowspan="3">
					<?php //echo $label_undetermined ?>
					<?php echo html::image($this->add_path('icons/32x32/shield-unreachable.png'),
							array(
								'alt' => strtolower($state_values[$var_type]),
								'title' => strtolower($state_values[$var_type]),
								'style' => 'cursor: pointer; margin-bottom: -4px')
							) ?>
				</td>
				<td><?php echo $label_not_running ?></td>
				<td><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NOT_RUNNING']) ?></td>
				<td><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) ?> %</td>
			</tr>
			<tr class="even">
				<td><?php echo $label_insufficient_data ?></td>
				<td><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NO_DATA']) ?></td>
				<td><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NO_DATA']) ?> %</td>
			</tr>
			<tr class="dark total undetermined">
				<td><?php echo $label_total ?></td>
				<td><?php echo time::to_string($avail_data['values']['TOTAL_TIME_UNDETERMINED']) ?></td>
				<td><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']!=0 ? $avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED'] : $this->_format_report_value(0)) ?> %</td>
			</tr>
			<tr class="dark border-top total">
				<td class="status icon label all-left"><?php echo $label_all ?></td>
				<td><?php echo $label_total ?></td>
				<td><? echo time::to_string($avail_data['tot_time']) ?></td>
				<td><? echo $this->_format_report_value($avail_data['tot_time_perc']) ?> %</td>
			</tr>
			<tr id="pdf-hide">
				<td colspan="4" style="padding: 7px 0px 0px 0px; border: 0px; background-color: transparent"><?php echo $testbutton ?></td>
			</tr>
		</table>
	</fieldset>
	<?php echo isset($pie) ? $pie : '' ?>
</div>
