<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
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
		<table summary="<?php echo $t->_('State breakdown for host services') ?>" class="multiple_services" style="margin-top: 15px" border="1">
			<tr>
				<th class="headerNone left" style="width: 90%">
				<?php
				if(!empty($data['groupname'])) {
					echo $data['groupname'];
				} else {
					echo $t->_('Services on host') .': ';
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
					&nbsp; <?php
					if(empty($data['groupname'])) {
						echo html::anchor('trends/generate?host_name[]='.$data['HOST_NAME'][$i].$get_vars.'&report_type=hosts', html::image($this->add_path('icons/menu/trends.png'), array('title' => $this->translate->_('Trends for this host'))));
					} ?>
				</th>
				<th class="headerNone left"><?php echo $t->_('Actions') ?></th>
				<th class="headerNone"><?php echo $t->_('OK') ?></th>
				<th class="headerNone"><?php echo $t->_('Warning') ?></th>
				<th class="headerNone"><?php echo $t->_('Unknown') ?></th>
				<th class="headerNone"><?php echo $t->_('Critical') ?></th>
				<th class="headerNone"><?php echo $t->_('Undetermined') ?></th>
			</tr>
		<?php } ?>
			<?php if (!$hide_host && !empty($data['groupname']) && ($data['HOST_NAME'][$i]!= $prev_hostname || $data['groupname']!= $prev_groupname)) { ?>
			<tr class="even">
			<?php if (!$use_alias) { ?>
				<td colspan="10" class="multiple label"><?php echo $t->_('Services on host') ?>: <?php echo $create_pdf != false ? $data['HOST_NAME'][$i] :'<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?></td>
			<?php } else { ?>
				<td colspan="10" class="multiple label"><?php echo $t->_('Services on host') ?>: <?php echo get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo $create_pdf != false ? $data['HOST_NAME'][$i] : '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?>)</td>
			<?php } ?>
			</tr>
			<?php $prev_hostname = $data['HOST_NAME'][$i]; $prev_groupname = $data['groupname']; } ?>
			<tr class="<?php echo ($i%2==0 ? 'even' : 'odd') ?>">
				<td class="label">
					<?php if ($create_pdf) { ?>
						<?php echo $data['SERVICE_DESCRIPTION'][$i]; ?>
					<?php } else { ?>
					<a href="<?php echo str_replace('&','&amp;',$data['service_link'][$i]); ?>"><?php echo $data['SERVICE_DESCRIPTION'][$i]; ?></a>
					<?php } ?>
				</td>
				<td class="data">
					<?php echo html::anchor('reports/generate?type=avail&host_name[]='.$data['HOST_NAME'][$i].'&service_description[]=' . $data['HOST_NAME'][$i].";".$data['SERVICE_DESCRIPTION'][$i].$get_vars, html::image($this->add_path('icons/16x16/availability.png'), array('title' => $this->translate->_('Availability report for this service'))), array('style' => 'border: 0px')) ?>
					<?php echo html::anchor('showlog/alert_history/'.$data['HOST_NAME'][$i].";".$data['SERVICE_DESCRIPTION'][$i], html::image($this->add_path('icons/16x16/alert-history.png'), array('title' => $this->translate->_('Alert History for this Service'))), array('style' => 'border: 0px')) ?>
					<?php echo html::anchor('notifications/host/'.$data['HOST_NAME'][$i]."?service=".$data['SERVICE_DESCRIPTION'][$i], html::image($this->add_path('icons/16x16/notify.png'), array('title' => $this->translate->_('Notifications for this Service'))), array('style' => 'border: 0px')) ?>
					<?php echo html::anchor('trends/host/'.$data['HOST_NAME'][$i], html::image($this->add_path('icons/16x16/trends.png'), array('title' => $this->translate->_('Trends for this Host'))), array('style' => 'border: 0px')) ?>
					<?php echo html::anchor('histogram/host/'.$data['HOST_NAME'][$i], html::image($this->add_path('icons/16x16/histogram.png'), array('title' => $this->translate->_('Alert Histogram for this Host'))), array('style' => 'border: 0px')) ?>
				</td>
				<td class="data"><?php echo reports::format_report_value($data['ok'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['ok'][$i]) > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $t->_('OK'), 'title' => $t->_('OK'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['warning'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['warning'][$i]) > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $t->_('Warning'), 'title' => $t->_('Warning'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['unknown'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['unknown'][$i]) > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $t->_('Unknown'), 'title' => $t->_('Unknown'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['critical'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['critical'][$i]) > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $t->_('Critical'), 'title' => $t->_('Critical'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['undetermined'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['undetermined'][$i]) > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'),'style' => 'height: 12px; width: 11px')) ?></td>
			</tr>
			<?php	} } ?>

			<?php if (!empty($data['groupname'])) {
					if ($use_average==0) { ?>
			<tr class="<?php echo ($i%2 == 0 ? 'even' : 'odd'); ?>">
				<td colspan="2"><?php echo $t->_('Average') ?></td>
				<td class="data"><?php echo $data['average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $t->_('Ok'), 'title' => $t->_('Ok'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $t->_('Warning'), 'title' => $t->_('Warning'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $t->_('Unknown'), 'title' => $t->_('Unknown'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $t->_('Critical'), 'title' => $t->_('Critical'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'),'style' => 'height: 12px; width: 11px')) ?></td>
			</tr>
			<?php 	} ?>
			<tr class="<?php $i++; echo ($i%2 == 0 ? 'even' : 'odd'); ?>">
				<td colspan="2"><?php if ($use_average==0) { ?><?php echo $t->_('Group availability (SLA)') ?> <?php } else { ?><?php echo $t->_('Average') ?><?php } ?></td>
				<td class="data"><?php echo $data['group_average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $t->_('Ok'), 'title' => $t->_('Ok'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $t->_('Warning'), 'title' => $t->_('Warning'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $t->_('Unknown'), 'title' => $t->_('Unknown'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $t->_('Critical'), 'title' => $t->_('Critical'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'),'style' => 'height: 12px; width: 11px')) ?></td>
			</tr>
			<?php } ?>
		</table>
</div>

<br />
<div class="state_services">
<?php }  ?>
<?php if (empty($data['groupname'])) { ?>
	<table summary="<?php echo $t->_('State breakdown for host services') ?>" class="multiple_services" border="1">
		<tr>
			<th class="headerNone left" style="width: 90%"><?php echo $t->_('Average and Group availability for all selected services') ?></th>
			<th class="headerNone"><?php echo $t->_('OK') ?></th>
			<th class="headerNone"><?php echo $t->_('Warning') ?></th>
			<th class="headerNone"><?php echo $t->_('Unknown') ?></th>
			<th class="headerNone"><?php echo $t->_('Critical') ?></th>
			<th class="headerNone"><?php echo $t->_('Undetermined') ?></th>
		</tr>
		<?php if ($use_average==0) { ?>
		<tr class="even">
			<td><?php echo $t->_('Average');?></td>
			<td class="data"><?php echo $data['average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $t->_('OK'), 'title' => $t->_('OK'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $t->_('Warning'), 'title' => $t->_('Warning'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $t->_('Unknown'), 'title' => $t->_('Unknown'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $t->_('Critical'), 'title' => $t->_('Critical'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'),'style' => 'height: 12px; width: 11px')) ?></td>
		</tr>
		<?php } ?>
		<tr class="odd">
				<td><?php if ($use_average==0) { ?><?php echo $t->_('Group availability (SLA)') ?> <?php } else { ?><?php echo $t->_('Average') ?><?php } ?></td>
				<td class="data"><?php echo $data['group_average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => $t->_('Ok'), 'title' => $t->_('Ok'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => $t->_('Warning'), 'title' => $t->_('Warning'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => $t->_('Unknown'), 'title' => $t->_('Unknown'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => $t->_('Critical'), 'title' => $t->_('Critical'),'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'),'style' => 'height: 12px; width: 11px')) ?></td>
			</tr>
	</table>
<?php } ?>
</div>