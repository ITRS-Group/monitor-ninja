<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
	if ($service_filter_status_show !== false) {
		echo _('Showing services in state: ');
			$j = 0; foreach($options['service_filter_status'] as $key => $value) {
			if ($value == 1) {
				echo ($j > 0) ? ', ' : '';
				echo '<strong>'.Reports_Model::$service_states[$key].'</strong>';
				$j++;

			}
		}
	}
	?>
<?php
	$sg_no = 0;
	$prev_host = false;
	$prev_group = false;
	$prev_hostname = false;
	foreach ($multiple_states as $data) {
		echo '<div class="state_services">';
		for ($i=0;$i<$data['nr_of_items'];$i++) {
			if (isset($data['ok'][$i])) {
				$condition = (!empty($data['groupname'])) ? $data['groupname']!= $prev_group : $data['HOST_NAME'][$i]!= $prev_host;

				if ($condition) {
					$prev_host = $data['HOST_NAME'][$i];
					$prev_group = $data['groupname'];

					# skip first iteration
					if ($i != 0) { ?>
				</table>
			</div>
			<div class="state_services">
				<?php } ?>
		<table summary="<?php echo _('State breakdown for host services') ?>" class="multiple_services" style="margin-top: 15px" border="1">
			<tr>
				<th class="headerNone left">
				<?php
				echo help::render('servicegroup_breakdown').' ';
				if(!empty($data['groupname'])) {
					echo $data['groupname'];
				} else {
					echo '<strong>'._('Services on host') .'</strong>: ';
					echo '<a href="'.urlencode($data['host_link'][$i]).'">';
					if (!$options['use_alias']) {
						echo $data['HOST_NAME'][$i];
					} else {
						echo $this->_get_host_alias($data['HOST_NAME'][$i]).' '.$data['HOST_NAME'][$i].')';
					}
				}
				echo '</a>';
				?>
				</th>
				<th class="headerNone" style="width: 80px"><?php echo _('OK') ?></th>
				<th class="headerNone" style="width: 80px"><?php echo _('Warning') ?></th>
				<th class="headerNone" style="width: 80px"><?php echo _('Unknown') ?></th>
				<th class="headerNone" style="width: 80px"><?php echo _('Critical') ?></th>
				<th class="headerNone" style="width: 80px"><?php echo _('Undetermined') ?></th>
				</tr>
		<?php }
				if (!$hide_host && !empty($data['groupname']) && ($data['HOST_NAME'][$i]!= $prev_hostname || $data['groupname']!= $prev_groupname) && $sg_no == 0) { ?>
			<tr class="even">
			<?php if (!$options['use_alias'] && $sg_no == 0) { ?>
				<td colspan="6" class="multiple label"><strong><?php echo _('Services on host') ?></strong>: <?php echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?></td>
			<?php } elseif ($sg_no == 0) { ?>
				<td colspan="6" class="multiple label"><strong><?php echo _('Services on host') ?></strong>: <?php echo $this->_get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>'; ?>)</td>
			<?php } ?>
			</tr>
			<?php $prev_hostname = $data['HOST_NAME'][$i]; $prev_groupname = $data['groupname'];
				}
				$no = 0;
				if (($data['ok'][$i] != 0 && $options['service_filter_status'][Reports_Model::SERVICE_OK] == true) ||
					 ($data['warning'][$i] != 0 && $options['service_filter_status'][Reports_Model::SERVICE_WARNING] == true) ||
					 ($data['unknown'][$i] != 0 && $options['service_filter_status'][Reports_Model::SERVICE_UNKNOWN] == true) ||
					 ($data['critical'][$i] != 0 && $options['service_filter_status'][Reports_Model::SERVICE_CRITICAL] == true) ||
					 ($data['undetermined'][$i] != 0 && $options['service_filter_status'][Reports_Model::SERVICE_PENDING] == true)) {
					$no++;?>
			<tr class="<?php echo ($i%2==0 ? 'even' : 'odd') ?>">
				<td class="label">
					<a href="<?php echo str_replace('&','&amp;',$data['service_link'][$i]); ?>"><?php echo $data['SERVICE_DESCRIPTION'][$i]; ?></a>
				</td>
				<td class="data"><?php echo reports::format_report_value($data['ok'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['ok'][$i]) > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => _('OK'), 'title' => _('OK'),'style' => 'height: 12px; width: 12px'));
					if (isset($data['counted_as_ok'][$i]) && $data['counted_as_ok'][$i] > 0) {
						echo " (" . reports::format_report_value($data['counted_as_ok'][$i]) ."% in other states)";
					}?></td>
				<td class="data"><?php echo reports::format_report_value($data['warning'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['warning'][$i]) > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => _('Warning'), 'title' => _('Warning'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['unknown'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['unknown'][$i]) > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => _('Unknown'), 'title' => _('Unknown'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['critical'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['critical'][$i]) > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => _('Critical'), 'title' => _('Critical'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['undetermined'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['undetermined'][$i]) > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => _('Undetermined'), 'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')) ?></td>
			</tr>
		<?php	}
			}
		} # end for

		$sg_no = $sg_no + $no;

		if (!empty($data['groupname'])) {
			if ($options['use_average']==0 && $sg_no == 0) { ?>
			<tr class="<?php echo (++$i%2==0 ? 'even' : 'odd') ?>">
				<td><?php echo _('Average') ?></td>
				<td class="data"><?php echo $data['average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => _('Ok'), 'title' => _('Ok'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => _('Warning'), 'title' => _('Warning'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => _('Unknown'), 'title' => _('Unknown'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => _('Critical'), 'title' => _('Critical'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => _('Undetermined'), 'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')) ?></td>
			</tr>
	<?php }

			if ($sg_no == 0) { ?>
			<tr class="<?php echo (++$i%2==0 ? 'even' : 'odd') ?>">
				<td><?php if ($options['use_average']==0) { ?><?php echo _('Group availability (SLA)') ?> <?php } else { ?><?php echo _('Average') ?><?php } ?></td>
				<td class="data_green"><?php echo $data['group_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_ok'] > 0 ? '' : 'not-').'ok.png'),
							array( 'alt' => _('Ok'), 'title' => _('Ok'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data_red"><?php echo $data['group_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_warning'] > 0 ? '' : 'not-').'warning.png'),
							array( 'alt' => _('Warning'), 'title' => _('Warning'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data_red"><?php echo $data['group_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_unknown'] > 0 ? '' : 'not-').'unknown.png'),
							array( 'alt' => _('Unknown'), 'title' => _('Unknown'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data_red"><?php echo $data['group_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_critical'] > 0 ? '' : 'not-').'critical.png'),
							array( 'alt' => _('Critical'), 'title' => _('Critical'),'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data_red"><?php echo $data['group_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => _('Undetermined'), 'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')) ?></td>
			</tr>
	<?php }
		}

		if ($sg_no > 0 && $no == 0) { ?>
			<tr class="even">
				<td colspan="6">
				<?php echo _('No service in this group in state: ');
					$j = 0;
					foreach($options['service_filter_status'] as $key => $value) {
						if ($value == 1) {
							echo ($j > 0) ? _(', ') : '';
							echo '<strong>'.$key.'</strong>';
							$j++;

						}
					} ?>
				</td>
			</tr>
<?php } ?>
	</table>
</div>

<br />

<?php
		$sg_no = 0;
	} # end foreach  ?>
<div class="state_services">
	<table summary="<?php echo _('State breakdown for host services') ?>" class="multiple_services" style="margin-bottom: 15px">
		<tr>
			<th class="headerNone left"><?php echo help::render('average_and_sla').' '._('Average and Group availability for all selected services') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('OK') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Warning') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Unknown') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Critical') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Undetermined') ?></th>
		</tr>
		<?php if ($options['use_average']==0) { ?>
		<tr class="<?php echo (++$i%2 == 0) ? 'even' : 'odd'; ?>">
			<td><?php echo _('Average');?></td>
			<td class="data"><?php echo $data['average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_ok'] > 0 ? '' : 'not-').'ok.png'),
						array( 'alt' => _('OK'), 'title' => _('OK'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data"><?php echo $data['average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_warning'] > 0 ? '' : 'not-').'warning.png'),
						array( 'alt' => _('Warning'), 'title' => _('Warning'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data"><?php echo $data['average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
						array( 'alt' => _('Unknown'), 'title' => _('Unknown'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data"><?php echo $data['average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_critical'] > 0 ? '' : 'not-').'critical.png'),
						array( 'alt' => _('Critical'), 'title' => _('Critical'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
						array( 'alt' => _('Undetermined'), 'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')) ?></td>
		</tr>
		<?php } ?>
		<tr class="<?php echo (++$i%2 == 0) ? 'even' : 'odd'; ?>">
			<td><?php if ($options['use_average']==0) { ?><?php echo _('Group availability (SLA)') ?> <?php } else { ?><?php echo _('Average') ?><?php } ?></td>
			<td class="data_green"><?php echo $data['group_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_ok'] > 0 ? '' : 'not-').'ok.png'),
						array( 'alt' => _('Ok'), 'title' => _('Ok'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data_red"><?php echo $data['group_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_warning'] > 0 ? '' : 'not-').'warning.png'),
						array( 'alt' => _('Warning'), 'title' => _('Warning'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data_red"><?php echo $data['group_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_unknown'] > 0 ? '' : 'not-').'unknown.png'),
						array( 'alt' => _('Unknown'), 'title' => _('Unknown'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data_red"><?php echo $data['group_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_critical'] > 0 ? '' : 'not-').'critical.png'),
						array( 'alt' => _('Critical'), 'title' => _('Critical'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data_red"><?php echo $data['group_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_undetermined'] > 0 ? '' : 'not-').'pending.png'),
						array( 'alt' => _('Undetermined'), 'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')) ?></td>
		</tr>
	</table>
</div>
