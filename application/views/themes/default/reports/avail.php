<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<?php if ($create_pdf) echo '<p>&nbsp;</p>';?>
<div id="state_breakdown" style="margin-top: 15px">
	<?php $i=1; $bg_color = ($i%2 == 0) ? '#ffffff' : '#f2f2f2'; ?>
	<table summary="<?php echo $t->_('Result table') ?>"<?php echo ($create_pdf) ? ' style="border: 1px solid #cdcdcd" cellpadding="5"' : '';?>>
		<!--<caption><?php echo str_replace(': ', ' '.$t->_('for').' '.$source.': ', $header_string); ?></caption>-->
		<tr>
			<th <?php echo ($create_pdf) ? 'style="width: 110px; font-weight: bold; font-size: 0.9em; background-color: '.$bg_color.'"' : 'class="headerNone left"'; ?>><?php echo (!$create_pdf) ? help::render('availability') : ''; ?></th>
			<th <?php echo ($create_pdf) ? 'style="width: 354px; font-weight: bold; font-size: 0.9em; background-color: '.$bg_color.'"' : 'class="headerNone left"'; ?>><?php echo $label_type_reason ?></th>
			<th <?php echo ($create_pdf) ? 'style="width: 110px; font-weight: bold; font-size: 0.9em; text-align:right; background-color: '.$bg_color.'"' : 'class="headerNone"'; ?>><?php echo $label_time ?></th>
			<th <?php echo ($create_pdf) ? 'style="width: 110px; font-weight: bold; font-size: 0.9em; text-align:right; background-color: '.$bg_color.'"' : 'class="headerNone"'; ?>><?php echo $label_tot_time ?></th>
			<?php if (!$create_pdf) { ?><th class="headerNone left"><?php echo $t->_('Status overview') ?></th><?php } ?>
		</tr>
		<?php $no_types = count($avail_data['var_types'] ); $i = 0; foreach ($avail_data['var_types'] as $var_type) { $i++; ?>
		<tr class="even" >
			<th <?php echo ($create_pdf) ? 'style=" font-weight: bold;width: 110px; font-size: 0.9em; background-color: '.$bg_color.'"' : 'class="headerNone left"style="border-top: 0px; vertical-align: bottom; width: 110px"'; ?> rowspan="3">
					<?php echo ucfirst(strtolower($state_values[$var_type])); ?>
			</th>
			<td <?php echo ($create_pdf) ? 'style="width: 354px; font-size: 0.9em; ' : ''; ?>><?php echo $label_unscheduled ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right;' : 'class="data" style="width: 80px"'; ?>><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_UNSCHEDULED']) ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right;' : 'class="data" style="width: 80px"'; ?>><?php echo reports::format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_UNSCHEDULED']) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'height: 12px; width: 12px')); ?>
			</td>
			<?php if (!$create_pdf) { ?>
			<?php if ($i == 1 && isset($pie)) { ?>
			<td rowspan="<?php echo $no_types*3+4; ?>" style="width: 200px; vertical-align: top">
				<?php echo $pie ?>
			</td>
			<?php } } ?>
		</tr>
		<tr class="even">
			<td <?php echo ($create_pdf) ? 'style="width: 354px; font-size: 0.9em;' : 'style="border-left: 0px"'; ?>><?php echo $label_scheduled ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right;' : 'class="data"'; ?>><?php echo time::to_string($avail_data['values']['TIME_' . $var_type .'_SCHEDULED']) ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right;' : 'class="data"'; ?>><?php echo reports::format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TIME_' . $var_type .'_SCHEDULED']) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'height: 12px; width: 12px')); ?>
			</td>
		</tr>
		<tr class="dark">
			<td <?php echo ($create_pdf) ? 'style="width: 354px; font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo $label_total ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo time::to_string($avail_data['values']['KNOWN_TIME_' . $var_type]) ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo reports::format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'height: 12px; width: 12px')); ?>
			</td>
		</tr>
		<?php } ?>
		<tr class="even">
			<th <?php echo ($create_pdf) ? 'style=" font-weight: bold;width: 110px; font-size: 0.9em; background-color: '.$bg_color.'"' : 'class="headerNone left" style="vertical-align: bottom; border-top: 0px"'; ?> rowspan="3">
				<?php echo $label_undetermined ?>
			</th>
			<td <?php echo ($create_pdf) ? 'style="width: 354px; font-size: 0.9em;' : ''; ?>><?php echo $label_not_running ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right;' : 'class="data"'; ?>><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NOT_RUNNING']) ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right;' : 'class="data"'; ?>><?php echo reports::format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NOT_RUNNING']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => $t->_('Undetermined'),'title' => $t->_('Undetermined'),'style' => 'height: 12px; width: 12px')); ?> </td>
		</tr>
		<tr class="even">
			<td <?php echo ($create_pdf) ? 'style="width: 354px; font-size: 0.9em;' : 'style="border-left: 0px"'; ?>><?php echo $label_insufficient_data ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right;' : 'class="data"'; ?>><?php echo time::to_string($avail_data['values']['TIME_UNDETERMINED_NO_DATA']) ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right;' : 'class="data"'; ?>><?php echo reports::format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NO_DATA']) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TIME_UNDETERMINED_NO_DATA']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => $t->_('Undetermined'),'title' => $t->_('Undetermined'),'style' => 'height: 12px; width: 12px')); ?></td>
		</tr>
		<tr class="dark total undetermined">
			<td <?php echo ($create_pdf) ? 'style="width: 354px; font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo $label_total ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo time::to_string($avail_data['values']['TOTAL_TIME_UNDETERMINED']) ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo reports::format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']!=0 ? $avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED'] : reports::format_report_value(0)) ?> %
			<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => $t->_('Undetermined'),'title' => $t->_('Undetermined'),'style' => 'height: 12px; width: 12px')); ?></td>
		</tr>
		<tr class="even total">
			<th <?php echo ($create_pdf) ? 'style=" font-weight: bold;width: 110px; font-size: 0.9em; background-color: '.$bg_color.'"' : 'class="headerNone left" style="border-top: 0px"'; ?>><?php echo $label_all ?></th>
			<td <?php echo ($create_pdf) ? 'style="width: 354px; font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo $label_total ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo time::to_string($avail_data['tot_time']) ?></td>
			<td <?php echo ($create_pdf) ? 'style="width: 110px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo reports::format_report_value($avail_data['tot_time_perc']) ?> %</td>
		</tr>
		<?php if (!$create_pdf) { ?>
		<tr id="pdf-hide">
			<td colspan="5" style="padding: 7px 0px 0px 0px; border: 0px; background-color: transparent"><?php echo $testbutton ?></td>
		</tr>
		<?php } ?>
	</table>
</div>
