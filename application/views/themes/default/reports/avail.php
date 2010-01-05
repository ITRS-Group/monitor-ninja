<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="state_breakdown">
	<h1 onclick="show_hide('avail',this)"><?php echo str_replace(': ', ' '.$this->translate->_('for').' '.$source.': ', $header_string); ?></h1>
	<div class="icon-help" onclick="general_help('avail')"></div>
	<fieldset id="avail">
		<table summary="Result table">
			<colgroup>
				<col class="col_label" />
				<col class="col_reason" />
				<col style="width: 157px" />
				<col style="width: 157px" />
			</colgroup>
			<tr>
				<th>&nbsp;</th>
				<th class="left"><?php echo $label_type_reason ?></th>
				<th><?php echo $label_time ?></th>
				<th><?php echo $label_tot_time ?></th>
			</tr>
			<?php foreach ($avail_data['var_types'] as $var_type) { ?>
			<tr class="border-top">
				<td class="label <?php echo strtolower($var_type); ?>-left" rowspan="3"><?php echo ucfirst(strtolower($state_values[$var_type])); ?></td>
				<td class="left"><?php echo $label_unscheduled ?></td>
				<td><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_UNSCHEDULED']) ?></td>
				<td class="border-right"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) ?> %</td>
			</tr>
			<tr>
				<td class="left"><?php echo $label_scheduled ?></td>
				<td><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_SCHEDULED']) ?></td>
				<td class="border-right"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) ?> %</td>
			</tr>
			<tr class="total <?php echo strtolower($var_type); ?>">
				<td class="left"><?php echo $label_total ?></td>
				<td><?php echo time::to_string($avail_data['values']['KNOWN_TIME_' . $var_type]) ?></td>
				<td class="border-right"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) ?> %</td>
			</tr>
			<tr class="no-border">
				<td colspan="4"></td>
			</tr>
			<?php } ?>
			<tr class="border-top">
				<td class="label undetermined-left" rowspan="3"><?php echo $label_undetermined ?></td>
				<td class="left"><?php echo $label_not_running ?></td>
				<td><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NOT_RUNNING']) ?></td>
				<td class="border-right"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) ?> %</td>
			</tr>
			<tr>
				<td class="left"><?php echo $label_insufficient_data ?></td>
				<td><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NO_DATA']) ?></td>
				<td class="border-right"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NO_DATA']) ?> %</td>
			</tr>
			<tr class="total undetermined">
				<td class="left"><?php echo $label_total ?></td>
				<td><?php echo time::to_string($avail_data['values']['TOTAL_TIME_UNDETERMINED']) ?></td>
				<td class="border-right"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']!=0 ? $avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED'] : $this->_format_report_value(0)) ?> %</td>
			</tr>
			<tr class="no-border">
				<td colspan="4"></td>
			</tr>
			<tr class="border-top total">
				<td class="label all-left"><?php echo $label_all ?></td>
				<td class="left"><?php echo $label_total ?></td>
				<td><? echo time::to_string($avail_data['tot_time']) ?></td>
				<td class="border-right"><? echo $this->_format_report_value($avail_data['tot_time_perc']) ?> %</td>
			</tr>
			<tr id="pdf-hide">
				<td colspan="4" style="padding: 7px 0px 0px 0px; border: 0px; background-color: transparent"><?php echo $testbutton ?></td>
			</tr>
		</table>
	</fieldset>
	<?php echo isset($pie) ? $pie : '' ?>
</div>
