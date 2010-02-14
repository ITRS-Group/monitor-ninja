<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<br />

<?php
	if (isset($trend_links) && !empty($trend_links)) { ?>
<div id="trend_links">
	<ul>
	<?php	foreach ($trend_links as $name => $link) { ?>
		<li><a href="<?php echo $link ?>"><?php echo $name ?></a></li>
	<?php	} ?>
	</ul>
</div>
<?php
	} ?>

<div id="trends_state_breakdown" style="margin-top: 15px">
	<table>
		<tr>
			<th>&nbsp;</th>
			<th><?php echo $label_time ?></th>
			<th><?php echo $label_tot_time ?></th>
		</tr>
		<?php $no_types = count($avail_data['var_types'] ); $i = 0; foreach ($avail_data['var_types'] as $var_type) { $i++; ?>
		<tr class="even" >
			<th style="border-top: 0px; vertical-align: bottom; width: 110px">
					<?php echo ucfirst(strtolower($state_values[$var_type])); ?>
			</th>
			<td><?php echo time::to_string($avail_data['values']['KNOWN_TIME_' . $var_type]) ?></td>
			<td><?php echo reports::format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_KNOWN_TIME_' . $var_type]) > 0 ? '' : 'not-').strtolower($state_values[$var_type]).'.png'),
				array('alt' => strtolower($state_values[$var_type]),'title' => strtolower($state_values[$var_type]),'style' => 'height: 12px; width: 12px')); ?>
			</td>
		</tr>
		<?php } ?>
		<tr class="even">
			<th style="text-align: left; vertical-align: bottom; border-top: 0px">
				<?php echo $label_undetermined ?>
			</th>
			<td><?php echo time::to_string($avail_data['values']['TOTAL_TIME_UNDETERMINED']) ?></td>
			<td><?php echo reports::format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']!=0 ? $avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED'] : reports::format_report_value(0)) ?> %
			<?php
				echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($avail_data['values']['PERCENT_TOTAL_TIME_UNDETERMINED']) > 0 ? '' : 'not-').'pending.png'),
				array('alt' => $t->_('Undetermined'),'title' => $t->_('Undetermined'),'style' => 'height: 12px; width: 12px')); ?>
			</td>
		</tr>
		<tr class="even">
			<th style="text-align: left; border-top: 0px"><?php echo $label_all ?></th>
			<td><? echo time::to_string($avail_data['tot_time']) ?></td>
			<td><? echo reports::format_report_value($avail_data['tot_time_perc']) ?> %</td>
		</tr>
	</table>
</div>