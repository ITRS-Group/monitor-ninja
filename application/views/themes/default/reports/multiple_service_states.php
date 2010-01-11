<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="state_services">
<!--<h1 style="margin-left:0px"><?php echo (!empty($data['servicename']) ? 'Servicegroup breakdown' : 'Service state breakdown'); ?></h1>
<p style="margin-top: -12px; margin-bottom: 0px">Reporting period: last year (2009-01-01 00:00 to 2010-01-01 00:00)</p>-->
<?php
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

</div>
<div class="state_services">

	<?php } ?>


		<table summary="State breakdown for host services" class="multiple_services" style="margin-top: 15px">

			<tr>
				<th class="headerNone" style="width: 90%; text-align: left">
				<?php
				if(!empty($data['groupname'])) {
					echo $data['groupname'];
				} else {
					echo 'Services on host: <a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">';
					if (!$use_alias) {
							echo $data['HOST_NAME'][$i].'</a>';
					 } else {
						echo $this->_get_host_alias($data['HOST_NAME'][$i]).' '.$data['HOST_NAME'][$i].')';
						}
					}
				?>
				</th>
				<th class="headerNone">OK</th>
				<th class="headerNone">Warning</th>
				<th class="headerNone">Unknown</th>
				<th class="headerNone">Critical</th>
				<th class="headerNone">Undetermined</th>
			</tr>
		<?php } ?>
			<?php if (!$hide_host && !empty($data['groupname']) && ($data['HOST_NAME'][$i]!= $prev_hostname || $data['groupname']!= $prev_groupname)) { ?>
			<tr class="even">
			<?php if (!$use_alias) { ?>
				<td colspan="10" class="multiple label">Services on host: <?php echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?></td>
			<?php } else { ?>
				<td colspan="10" class="multiple label">Services on host: <?php echo get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?>)</td>
			<?php } ?>
			</tr>
			<?php $prev_hostname = $data['HOST_NAME'][$i]; $prev_groupname = $data['groupname']; } ?>
			<tr class="<?php echo ($i%2==0 ? 'even' : 'odd') ?>">
				<td class="label"><a href="<?php echo str_replace('&','&amp;',$data['service_link'][$i]); ?>"><?php echo wordwrap($data['SERVICE_DESCRIPTION'][$i],25,'<br />',true); ?></a></td>
				<td class="data"><?php echo $this->_format_report_value($data['ok'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['ok'][$i]) > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => 'Ok', 'title' => 'Ok','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $this->_format_report_value($data['warning'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['warning'][$i]) > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => 'Warning', 'title' => 'Warning','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $this->_format_report_value($data['unknown'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['unknown'][$i]) > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => 'Unknown', 'title' => 'Unknown','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $this->_format_report_value($data['critical'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['critical'][$i]) > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => 'Critical', 'title' => 'Critical','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $this->_format_report_value($data['undetermined'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['undetermined'][$i]) > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => 'Undetermined', 'title' => 'Undetermined','style' => 'margin-bottom: -1px')) ?></td>
			</tr>
			<?php	} } ?>

			<?php if (!empty($data['groupname'])) {
					if ($use_average==0) { ?>
			<tr>
				<td>Average</td>
				<td class="data"><?php echo $data['average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => 'Ok', 'title' => 'Ok','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => 'Warning', 'title' => 'Warning','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => 'Unknown', 'title' => 'Unknown','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => 'Critical', 'title' => 'Critical','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => 'Undetermined', 'title' => 'Undetermined','style' => 'margin-bottom: -1px')) ?></td>
			</tr>
			<?php 	} ?>
			<tr class="group-average">
				<td><?php if ($use_average==0) { ?>Group availability (SLA) <?php } else { ?>Average<?php } ?></td>
				<td class="data"><?php echo $data['group_average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => 'Ok', 'title' => 'Ok','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => 'Warning', 'title' => 'Warning','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => 'Unknown', 'title' => 'Unknown','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => 'Critical', 'title' => 'Critical','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => 'Undetermined', 'title' => 'Undetermined','style' => 'margin-bottom: -1px')) ?></td>
			</tr>
			<?php } ?>
		</table>
</div>

<br />
<div class="state_services">
<?php }  ?>
<?php if (empty($data['groupname'])) { ?>
	<table summary="State breakdown for host services" class="multiple_services">
		<tr>
			<th class="headerNone" style="width: 90%; text-align: left">Average and Group availability for all selected services</th>
			<th class="headerNone">OK</th>
			<th class="headerNone">Warning</th>
			<th class="headerNone">Unknown</th>
			<th class="headerNone">Critical</th>
			<th class="headerNone">Undetermined</th>
		</tr>
		<?php if ($use_average==0) { ?>
		<tr class="even">
			<td>Average</td>
			<td class="data"><?php echo $data['average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => 'Ok', 'title' => 'Ok','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => 'Warning', 'title' => 'Warning','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => 'Unknown', 'title' => 'Unknown','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => 'Critical', 'title' => 'Critical','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => 'Undetermined', 'title' => 'Undetermined','style' => 'margin-bottom: -1px')) ?></td>
		</tr>
		<?php } ?>
		<tr class="odd">
				<td><?php if ($use_average==0) { ?>Group availability (SLA) <?php } else { ?>Average<?php } ?></td>
				<td class="data"><?php echo $data['group_average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => 'Ok', 'title' => 'Ok','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => 'Warning', 'title' => 'Warning','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => 'Unknown', 'title' => 'Unknown','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => 'Critical', 'title' => 'Critical','style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => 'Undetermined', 'title' => 'Undetermined','style' => 'margin-bottom: -1px')) ?></td>
			</tr>
		<tr id="pdf-hide">
			<td colspan="6"><?php echo $this->_build_testcase_form($data[';testcase;']); ?></td>
		</tr>
	</table>
<?php } ?>
</div>
