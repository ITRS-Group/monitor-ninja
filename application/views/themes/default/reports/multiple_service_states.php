<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="state_services"><?php
	$prev_host = false;
	$prev_group = false;
	$prev_hostname = false;
	$j = 0; foreach ($multiple_states as $data) {
	for ($i=0;$i<$data['nr_of_items'];$i++) { if (isset($data['ok'][$i])) {
	$condition = (!empty($data['groupname'])) ? $data['groupname']!= $prev_group : $data['HOST_NAME'][$i]!= $prev_host;

	if ($condition) {
		$j++;
		$prev_host = $data['HOST_NAME'][$i];
		$prev_group = $data['groupname'];

		if ($j != 1) { ?>
	</table>
	</fieldset>
</div>
<div class="state_services">
	<?php } ?>
	<?php	if(!empty($data['groupname'])) { ?>
	<h1 onclick="show_hide('services',this)">
		<?php echo $data['groupname']; ?>
	</h1>
	<?php } else { ?>
	<h1 onclick="show_hide('services',this)">
		Services on host: <a href="<?php echo str_replace('&','&amp;',$data['host_link'][$i]) ?>">
	<?php if (!$use_alias) { ?>
		<?php echo $data['HOST_NAME'][$i]; ?></a>
	<?php } else { ?>
		<?php echo $this->_get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo $data['HOST_NAME'][$i]; ?>)
	<?php } ?>
		</a>
	</h1>
	<?php } ?>
	<div class="icon-help" onclick="general_help('multiple_service_states')"></div>
	<fieldset id="services">
		<table summary="State breakdown for host services">
			<colgroup>
				<col class="col_label" />
				<col class="auto" />
				<col class="col_space_sml" />
				<col class="auto" />
				<col class="col_space_sml" />
				<col class="auto" />
				<col class="col_space_sml" />
				<col class="auto" />
				<col class="col_space_sml" />
				<col class="auto" />
			</colgroup>
			<tr>
				<th class="null"></th>
				<th class="ok">Time <br />OK</th>
				<th class="null"></th>
				<th class="warning">Time <br />Warning</th>
				<th class="null"></th>
				<th class="unknown">Time <br />Unknown</th>
				<th class="null"></th>
				<th class="critical">Time <br />Critical</th>
				<th class="null"></th>
				<th class="undetermined">Time <br />Undetermined</th>
			</tr>
<?php } ?>
			<?php if (!$hide_host && !empty($data['groupname']) && ($data['HOST_NAME'][$i]!= $prev_hostname || $data['groupname']!= $prev_groupname)) { ?>
			<tr>
			<?php if (!$use_alias) { ?>
				<td colspan="10" class="multiple label">Services on host: <?php echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?></td>
			<?php } else { ?>
				<td colspan="10" class="multiple label">Services on host: <?php echo get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?>)</td>
			<?php } ?>
			</tr>
			<?php $prev_hostname = $data['HOST_NAME'][$i]; $prev_groupname = $data['groupname']; } ?>
			<tr>
				<td class="label"><a href="<?php echo str_replace('&','&amp;',$data['service_link'][$i]); ?>"><?php echo wordwrap($data['SERVICE_DESCRIPTION'][$i],25,'<br />',true); ?></a></td>
				<td><?php echo $this->_format_report_value($data['ok'][$i]) ?> %</td>
				<td class="null"></td>
				<td><?php echo $this->_format_report_value($data['warning'][$i]) ?> %</td>
				<td class="null"></td>
				<td><?php echo $this->_format_report_value($data['unknown'][$i]) ?> %</td>
				<td class="null"></td>
				<td><?php echo $this->_format_report_value($data['critical'][$i]) ?> %</td>
				<td class="null"></td>
				<td class="border-right"><?php echo $this->_format_report_value($data['undetermined'][$i]) ?> %</td>
			</tr>
			<?php	} } ?>

			<?php if (!empty($data['groupname'])) {
					if ($use_average==0) { ?>
			<tr>
				<td class="label">Average</td>
				<td><?php echo $data['average_ok'] ?> %</td>
				<td class="null"></td>
				<td><?php echo $data['average_warning'] ?> %</td>
				<td class="null"></td>
				<td><?php echo $data['average_unknown'] ?> %</td>
				<td class="null"></td>
				<td><?php echo $data['average_critical'] ?> %</td>
				<td class="null"></td>
				<td class="border-right"><?php echo $data['average_undetermined'] ?> %</td>
			</tr>
			<?php 	} ?>
			<tr class="group-average">
				<td class="label"><?php if ($use_average==0) { ?>Group availability (SLA) <?php } else { ?>Average<?php } ?></td>
				<td class="ok"><?php echo $data['group_average_ok'] ?> %</td>
				<td class="null"></td>
				<td class="warning"><?php echo $data['group_average_warning'] ?> %</td>
				<td class="null"></td>
				<td class="unknown"><?php echo $data['group_average_unknown'] ?> %</td>
				<td class="null"></td>
				<td class="critical"><?php echo $data['group_average_critical'] ?> %</td>
				<td class="null"></td>
				<td class="undetermined border-right"><?php echo $data['group_average_undetermined'] ?> %</td>
			</tr>
			<?php } ?>
		</table>
	</fieldset>
</div>
<div class="state_services">
<?php }  ?>
<?php if (empty($data['groupname'])) { ?>
	<h1 onclick="show_hide('services_avg',this)">
		Average and Group availability for all selected services
	</h1>
	<div class="icon-help" onclick="general_help('average_sla_info')"></div>
	<fieldset id="services_avg">
		<table summary="State breakdown for host services">
			<colgroup>
				<col class="col_label" />
				<col class="auto" />
				<col class="col_space_sml" />
				<col class="auto" />
				<col class="col_space_sml" />
				<col class="auto" />
				<col class="col_space_sml" />
				<col class="auto" />
				<col class="col_space_sml" />
				<col class="auto" />
			</colgroup>
			<tr>
				<th class="null"></th>
				<th class="ok">Time <br />OK</th>
				<th class="null"></th>
				<th class="warning">Time <br />Warning</th>
				<th class="null"></th>
				<th class="unknown">Time <br />Unknown</th>
				<th class="null"></th>
				<th class="critical">Time <br />Critical</th>
				<th class="null"></th>
				<th class="undetermined">Time <br />Undetermined</th>
			</tr>

			<?php if ($use_average==0) { ?>
			<tr>
				<td class="label">Average</td>
				<td><?php echo $data['average_ok'] ?> %</td>
				<td class="null"></td>
				<td><?php echo $data['average_warning'] ?> %</td>
				<td class="null"></td>
				<td><?php echo $data['average_unknown'] ?> %</td>
				<td class="null"></td>
				<td><?php echo $data['average_critical'] ?> %</td>
				<td class="null"></td>
				<td class="border-right"><?php echo $data['average_undetermined'] ?> %</td>
			</tr>
			<?php } ?>
			<tr class="group-average">
				<td class="label"><?php if ($use_average==0) { ?>Group availability (SLA) <?php } else { ?>Average<?php } ?></td>
				<td class="ok"><?php echo $data['group_average_ok'] ?> %</td>
				<td class="null"></td>
				<td class="warning"><?php echo $data['group_average_warning'] ?> %</td>
				<td class="null"></td>
				<td class="unknown"><?php echo $data['group_average_unknown'] ?> %</td>
				<td class="null"></td>
				<td class="critical"><?php echo $data['group_average_critical'] ?> %</td>
				<td class="null"></td>
				<td class="undetermined border-right"><?php echo $data['group_average_undetermined'] ?> %</td>
			</tr>
			<tr id="pdf-hide">
				<td colspan="10" style="background: #ffffff;border-bottom: 0px;padding: 7px 0px 0px 0px"><?php echo $this->_build_testcase_form($data[';testcase;']); ?></td>
			</tr>
		</table>
	</fieldset>
<?php } ?>
</div>
