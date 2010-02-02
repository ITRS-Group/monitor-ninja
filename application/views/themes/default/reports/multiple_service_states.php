<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="state_services">
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
		<table summary="<?php echo $this->translate->_('State breakdown for host services') ?>" class="multiple_services" style="margin-top: 15px" border="1">
			<tr>
				<th class="headerNone" style="text-align: left">
				<?php
				if(!empty($data['groupname'])) {
					echo $data['groupname'];
				} else {
					echo $this->translate->_('Services on host') .': ';
					if (!$create_pdf)
						echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">';
					if (!$use_alias) {
						echo $data['HOST_NAME'][$i];
					 } else {
						echo $this->_get_host_alias($data['HOST_NAME'][$i]).' '.$data['HOST_NAME'][$i].')';
						}
					}
					if (!$create_pdf)
						echo '</a>';
				?>
				</th>
				<th class="headerNone"><?php echo $this->translate->_('OK') ?></th>
				<th class="headerNone"><?php echo $this->translate->_('Warning') ?></th>
				<th class="headerNone"><?php echo $this->translate->_('Unknown') ?></th>
				<th class="headerNone"><?php echo $this->translate->_('Critical') ?></th>
				<th class="headerNone"><?php echo $this->translate->_('Undetermined') ?></th>
				</tr>
		<?php } ?>
			<?php if (!$hide_host && !empty($data['groupname']) && ($data['HOST_NAME'][$i]!= $prev_hostname || $data['groupname']!= $prev_groupname)) { ?>
			<tr class="even">
			<?php if (!$use_alias) { ?>
				<td colspan="10" class="multiple label"><?php echo $this->translate->_('Services on host') ?>: <?php echo $create_pdf != false ? $data['HOST_NAME'][$i] :'<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?></td>
			<?php } else { ?>
				<td colspan="10" class="multiple label"><?php echo $this->translate->_('Services on host') ?>: <?php echo get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo $create_pdf != false ? $data['HOST_NAME'][$i] : '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?>)</td>
			<?php } ?>
			</tr>
			<?php $prev_hostname = $data['HOST_NAME'][$i]; $prev_groupname = $data['groupname']; } ?>
			<tr class="<?php echo ($i%2==0 ? 'even' : 'odd') ?>">
				<td class="label">
					<?php if ($create_pdf) { ?>
						<?php echo wordwrap($data['SERVICE_DESCRIPTION'][$i],25,'<br />',true) ?>
					<?php } else { ?>
					<a href="<?php echo str_replace('&','&amp;',$data['service_link'][$i]); ?>"><?php echo wordwrap($data['SERVICE_DESCRIPTION'][$i],25,'<br />',true); ?></a>
					<?php } ?>
				</td>
				<td class="data"><?php echo $this->_format_report_value($data['ok'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['ok'][$i]) > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $this->translate->_('OK'), 'title' => $this->translate->_('OK'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $this->_format_report_value($data['warning'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['warning'][$i]) > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $this->translate->_('Warning'), 'title' => $this->translate->_('Warning'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $this->_format_report_value($data['unknown'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['unknown'][$i]) > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $this->translate->_('Unknown'), 'title' => $this->translate->_('Unknown'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $this->_format_report_value($data['critical'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['critical'][$i]) > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $this->translate->_('Critical'), 'title' => $this->translate->_('Critical'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $this->_format_report_value($data['undetermined'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($this->_format_report_value($data['undetermined'][$i]) > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $this->translate->_('Undetermined'), 'title' => $this->translate->_('Undetermined'),'style' => 'margin-bottom: -1px')) ?></td>
			</tr>
			<?php	} } ?>

			<?php if (!empty($data['groupname'])) {
					if ($use_average==0) { ?>
			<tr>
				<td><?php echo $this->translate->_('Average') ?></td>
				<td class="data"><?php echo $data['average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $this->translate->_('Ok'), 'title' => $this->translate->_('Ok'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $this->translate->_('Warning'), 'title' => $this->translate->_('Warning'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $this->translate->_('Unknown'), 'title' => $this->translate->_('Unknown'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $this->translate->_('Critical'), 'title' => $this->translate->_('Critical'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $this->translate->_('Undetermined'), 'title' => $this->translate->_('Undetermined'),'style' => 'margin-bottom: -1px')) ?></td>
			</tr>
			<?php 	} ?>
			<tr class="group-average">
				<td><?php if ($use_average==0) { ?><?php echo $this->translate->_('Group availability (SLA)') ?> <?php } else { ?><?php echo $this->translate->_('Average') ?><?php } ?></td>
				<td class="data"><?php echo $data['group_average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $this->translate->_('Ok'), 'title' => $this->translate->_('Ok'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $this->translate->_('Warning'), 'title' => $this->translate->_('Warning'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $this->translate->_('Unknown'), 'title' => $this->translate->_('Unknown'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $this->translate->_('Critical'), 'title' => $this->translate->_('Critical'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $this->translate->_('Undetermined'), 'title' => $this->translate->_('Undetermined'),'style' => 'margin-bottom: -1px')) ?></td>
			</tr>
			<?php } ?>
		</table>
</div>

<br />
<div class="state_services">
<?php }  ?>
<?php if (empty($data['groupname'])) { ?>
	<table summary="<?php echo $this->translate->_('State breakdown for host services') ?>" class="multiple_services" border="1">
		<tr>
			<th class="headerNone" style="text-align: left"><?php echo $this->translate->_('Average and Group availability for all selected services') ?></th>
			<th class="headerNone"><?php echo $this->translate->_('OK') ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Warning') ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Unknown') ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Critical') ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Undetermined') ?></th>
		</tr>
		<?php if ($use_average==0) { ?>
		<tr class="even">
			<td>Average</td>
			<td class="data"><?php echo $data['average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $this->translate->_('OK'), 'title' => $this->translate->_('OK'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $this->translate->_('Warning'), 'title' => $this->translate->_('Warning'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $this->translate->_('Unknown'), 'title' => $this->translate->_('Unknown'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $this->translate->_('Critical'), 'title' => $this->translate->_('Critical'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $this->translate->_('Undetermined'), 'title' => $this->translate->_('Undetermined'),'style' => 'margin-bottom: -1px')) ?></td>
		</tr>
		<?php } ?>
		<tr class="odd">
				<td><?php if ($use_average==0) { ?><?php echo $this->translate->_('Group availability (SLA)') ?> <?php } else { ?><?php echo $this->translate->_('Average') ?><?php } ?></td>
				<td class="data"><?php echo $data['group_average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $this->translate->_('Ok'), 'title' => $this->translate->_('Ok'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $this->translate->_('Warning'), 'title' => $this->translate->_('Warning'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $this->translate->_('Unknown'), 'title' => $this->translate->_('Unknown'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $this->translate->_('Critical'), 'title' => $this->translate->_('Critical'),'style' => 'margin-bottom: -1px')) ?></td>
				<td class="data"><?php echo $data['group_average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $this->translate->_('Undetermined'), 'title' => $this->translate->_('Undetermined'),'style' => 'margin-bottom: -1px')) ?></td>
			</tr>
		<?php if (!$create_pdf) { ?>
		<tr id="pdf-hide">
			<td colspan="6"><?php echo $this->_build_testcase_form($data[';testcase;']); ?></td>
		</tr>
		<?php } ?>
	</table>
<?php } ?>
</div>
