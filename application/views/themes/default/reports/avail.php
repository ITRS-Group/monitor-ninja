<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="state_breakdown" style="margin-top: 15px">
	<!--<div class="icon-help" onclick="general_help('avail')"></div>-->
	<table summary="<?php echo $this->translate->_('Result table') ?>" border="1">
		<!--<caption><?php echo str_replace(': ', ' '.$this->translate->_('for').' '.$source.': ', $header_string); ?></caption>-->
		<tr>
			<th class="headerNone" style="text-align: left">&nbsp;</th>
			<th class="headerNone" style="text-align: left"><?php echo $label_type_reason ?></th>
			<th class="headerNone"><?php echo $label_time ?></th>
			<th class="headerNone"><?php echo $label_tot_time ?></th>
			<th class="headerNone" style="text-align: left"><?php echo $this->translate->_('Status overview') ?></th>
		</tr>
		<?php $no_types = count($avail_data['var_types'] ); $i = 0; foreach ($avail_data['var_types'] as $var_type) { $i++; ?>
		<tr class="even" >
			<th class="headerNone" rowspan="3"style="text-align:left; border-top: 0px; vertical-align: bottom; width: 110px">
					<?php echo ucfirst(strtolower($state_values[$var_type])); ?>
			</th>
			<td><?php echo $label_unscheduled ?></td>
			<td class="data" style="width: 80px"><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_UNSCHEDULED']) ?></td>
			<td class="data" style="width: 80px"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'margin-bottom: -1px')); ?>
			</td>
			<?php if ($i == 1 && isset($pie)) { ?>
			<td rowspan="<?php echo $no_types*3+4; ?>" style="width: 200px; vertical-align: top">
				<?php echo $pie ?>
			</td>
			<?php } ?>
		</tr>
		<tr class="even">
			<td style="border-left: 0px"><?php echo $label_scheduled ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_SCHEDULED']) ?></td>
			<td class="data"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'margin-bottom: -1px')); ?>
			</td>
		</tr>
		<tr class="dark">
			<td><?php echo $label_total ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['KNOWN_TIME_' . $var_type]) ?></td>
			<td class="data"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'margin-bottom: -1px')); ?>
			</td>
		</tr>

		<?php } ?>
		<tr class="even">
			<th class="headerNone" rowspan="3" style="text-align: left; vertical-align: bottom; border-top: 0px">
				<?php echo $label_undetermined ?>
			</th>
			<td><?php echo $label_not_running ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NOT_RUNNING']) ?></td>
			<td class="data"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => $this->translate->_('Undetermined'),'title' => $this->translate->_('Undetermined'),'style' => 'margin-bottom: -1px')); ?>
				</td>
		</tr>
		<tr class="even">
			<td style="border-left: 0px"><?php echo $label_insufficient_data ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NO_DATA']) ?></td>
			<td class="data"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NO_DATA']) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NO_DATA']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => $this->translate->_('Undetermined'),'title' => $this->translate->_('Undetermined'),'style' => 'margin-bottom: -1px')); ?>
			</td>
		</tr>
		<tr class="dark total undetermined">
			<td><?php echo $label_total ?></td>
			<td class="data"><?php echo time::to_string($avail_data['values']['TOTAL_TIME_UNDETERMINED']) ?></td>
			<td class="data"><?php echo $this->_format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']!=0 ? $avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED'] : $this->_format_report_value(0)) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => $this->translate->_('Undetermined'),'title' => $this->translate->_('Undetermined'),'style' => 'margin-bottom: -1px')); ?>
			</td>
		</tr>
		<tr class="even total">
			<th class="headerNone" style="text-align: left; border-top: 0px"><?php echo $label_all ?></th>
			<td><?php echo $label_total ?></td>
			<td class="data"><? echo time::to_string($avail_data['tot_time']) ?></td>
			<td class="data"><? echo $this->_format_report_value($avail_data['tot_time_perc']) ?> %</td>
		</tr>
		<?php if (!$create_pdf) { ?>
		<tr id="pdf-hide">
			<td colspan="5" style="padding: 7px 0px 0px 0px; border: 0px; background-color: transparent"><?php echo $testbutton ?></td>
		</tr>
		<?php } ?>
	</table>
</div>