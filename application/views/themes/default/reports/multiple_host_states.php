<?php defined('SYSPATH') OR die('No direct access allowed.');
foreach ($multiple_states as $data) { ?>
<div class="host_breakdown wide">
	<h1 onclick="show_hide('host<?php echo str_replace(' ','_',$data['groupname']);?>',this)"><?php echo (!empty($data['groupname']) ? $data['groupname'] : 'Hosts state breakdown'); ?></h1>
	<div class="icon-help" onclick="general_help('multiple_host_states')"></div>
	<fieldset id="host<?php echo str_replace(' ','_',$data['groupname']);?>">
			<table summary="Host state breakdown" style="width: 100%">
			<colgroup>
				<col style="width: 80%" />
				<col style="width: auto" />
				<col class="col_space" />
				<col style="width: auto" />
				<col class="col_space" />
				<col style="width: auto" />
				<col class="col_space" />
				<col style="width: auto" />
			</colgroup>
			<tr>
				<th class="null">Time:</th>
				<th class="up">Up</th>
				<th class="null"></th>
				<th class="unreachable">Unreachable</th>
				<th class="null"></th>
				<th class="down">Down</th>
				<th class="null"></th>
				<th class="undetermined">Undetermined</th>
			</tr>
			<?php for ($i=0;$i<$data['nr_of_items'];$i++) { ?>
			<tr>
			<?php if (!$use_alias) { ?>
				<td class="label"><?php echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . wordwrap($data['HOST_NAME'][$i],30,'<br />',true) . '</a>' ?></td>
				<?php } else { ?>
				<td class="label"><?php echo $this->_get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . wordwrap($data['HOST_NAME'][$i],30,'<br />',true) . '</a>' ?>)</td>
				<?php } ?>
				<td style="white-space:nowrap"><?php echo $this->_format_report_value($data['up'][$i]) ?> %</td>
				<td class="null"></td>
				<td><?php echo $this->_format_report_value($data['unreachable'][$i]) ?> %</td>
				<td class="null"></td>
				<td style="white-space:nowrap"><?php echo $this->_format_report_value($data['down'][$i]) ?> %</td>
				<td class="null"></td>
				<td class="border-right"><?php echo $this->_format_report_value($data['undetermined'][$i]) ?> %</td>
			</tr>
			<?php	} if ($use_average==0) { ?>
			<tr>
				<td class="label">Average</td>
				<td style="white-space:nowrap"><?php echo $data['average_up'] ?> %</td>
				<td class="null"></td>
				<td><?php echo $data['average_unreachable'] ?> %</td>
				<td class="null"></td>
				<td style="white-space:nowrap"><?php echo $data['average_down'] ?> %</td>
				<td class="null"></td>
				<td class="border-right"><?php echo $data['average_undetermined'] ?> %</td>
			</tr>
			<?php } ?>
			<tr class="group-average">
				<td class="label"><?php echo ($use_average==0) ? 'Group availability (SLA)' :'Average'; ?></td>
				<td class="up" style="white-space:nowrap"><?php echo $data['group_average_up'] ?> %</td>
				<td class="null"></td>
				<td class="unreachable"><?php echo $data['group_average_unreachable'] ?> %</td>
				<td class="null"></td>
				<td class="down" style="white-space:nowrap"><?php echo $data['group_average_down'] ?> %</td>
				<td class="null"></td>
				<td class="undetermined border-right"><?php echo $data['group_average_undetermined'] ?> %</td>
			</tr>
			<tr id="pdf-hide">
				<td colspan="8" style="background: #ffffff;border-bottom: 0px;padding: 7px 0px 0px 0px"><?php echo $this->_build_testcase_form($data[';testcase;']); ?></td>
			</tr>
		</table>
	</fieldset>
</div>
<?php } ?>