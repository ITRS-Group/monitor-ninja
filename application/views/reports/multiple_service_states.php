<?php defined('SYSPATH') OR die('No direct access allowed.');

foreach ($multiple_states as $data) {
	$previous_hostname = false;
?>
<div class="state_services report-block">
	<table summary="<?php echo _('State breakdown for services') ?>" class="multiple_services" border="1">
		<tr>
			<th>
			<?php
			echo help::render('servicegroup_breakdown').' ';
			if(!empty($data['groupname'])) {
				echo $data['groupname'];
			} else {
				echo _('Selected services');
			}
			?>
			</th>
			<th class="headerNone" style="width: 80px"><?php echo _('OK') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Warning') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Unknown') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Critical') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Undetermined') ?></th>
		</tr>
	<?php
	for ($i=0;$i<$data['nr_of_items'];$i++) { ?>
<?php
			if (!$hide_host && $previous_hostname != $data['HOST_NAME'][$i]) { ?>
		<tr class="even">
		<?php if (!$options['use_alias']) { ?>
			<td colspan="6" class="multiple label"><strong><?php echo _('Services on host') ?></strong>: <?php echo '<a href="'.$data['host_link'][$i].'">' . $data['HOST_NAME'][$i] . '</a>'; ?></td>
		<?php } else { ?>
			<td colspan="6" class="multiple label"><strong><?php echo _('Services on host') ?></strong>: <?php echo $this->_get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo '<a href="'.$data['host_link'][$i].'">' . $data['HOST_NAME'][$i] . '</a>'; ?>)</td>
<?php }
			$previous_hostname = $data['HOST_NAME'][$i];
?>
		</tr>
<?php
		}?>
		<tr class="<?php echo ($i%2==0 ? 'even' : 'odd') ?>">
			<td class="label">
				<a href="<?php echo $data['service_link'][$i]; ?>"><?php echo $data['SERVICE_DESCRIPTION'][$i]; ?></a>
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
<?php
	} # end for
?>
	</table>
</div>
<div class="state_services report-block">
	<table summary="<?php echo _('State breakdown for services') ?>" class="multiple_services" border="1">
		<tr>
			<th><?php echo help::render('average_and_sla').' '.sprintf(_('Average and Group availability for %s'), $data['groupname'] ?: _('selected services')) ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('OK') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Warning') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Unknown') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Critical') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Undetermined') ?></th>
		</tr>
		<tr class="<?php echo (++$i%2 == 0) ? 'even' : 'odd'; ?>">
			<td><?php echo _('Average');?></td>
			<td class="summary ok <?php echo ($data['average_ok']>0?'nonzero':'') ?>"><?php echo $data['average_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_ok'] > 0 ? '' : 'not-').'ok.png'),
						array( 'alt' => _('OK'), 'title' => _('OK'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary warning <?php echo ($data['average_warning']>0?'nonzero':'') ?>"><?php echo $data['average_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_warning'] > 0 ? '' : 'not-').'warning.png'),
						array( 'alt' => _('Warning'), 'title' => _('Warning'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary unknown <?php echo ($data['average_unknown']>0?'nonzero':'') ?>"><?php echo $data['average_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unknown'] > 0 ? '' : 'not-').'unknown.png'),
						array( 'alt' => _('Unknown'), 'title' => _('Unknown'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary critical <?php echo ($data['average_critical']>0?'nonzero':'') ?>"><?php echo $data['average_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_critical'] > 0 ? '' : 'not-').'critical.png'),
						array( 'alt' => _('Critical'), 'title' => _('Critical'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary undetermined <?php echo ($data['average_undetermined']>0?'nonzero':'') ?>"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
						array( 'alt' => _('Undetermined'), 'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')) ?></td>
		</tr>
		<?php if (!$options['use_average']) { ?>
		<tr class="<?php echo (++$i%2 == 0) ? 'even' : 'odd'; ?>">
			<td><?php echo _('Group availability (SLA)') ?></td>
			<td class="summary ok <?php echo ($data['group_ok']>0?'nonzero':'') ?>"><?php echo $data['group_ok'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_ok'] > 0 ? '' : 'not-').'ok.png'),
						array( 'alt' => _('Ok'), 'title' => _('Ok'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary warning <?php echo ($data['group_warning']>0?'nonzero':'') ?>"><?php echo $data['group_warning'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_warning'] > 0 ? '' : 'not-').'warning.png'),
						array( 'alt' => _('Warning'), 'title' => _('Warning'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary unknown <?php echo ($data['group_unknown']>0?'nonzero':'') ?>"><?php echo $data['group_unknown'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_unknown'] > 0 ? '' : 'not-').'unknown.png'),
						array( 'alt' => _('Unknown'), 'title' => _('Unknown'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary critical <?php echo ($data['group_critical']>0?'nonzero':'') ?>"><?php echo $data['group_critical'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_critical'] > 0 ? '' : 'not-').'critical.png'),
						array( 'alt' => _('Critical'), 'title' => _('Critical'),'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary undetermined <?php echo ($data['group_undetermined']>0?'nonzero':'') ?>"><?php echo $data['group_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_undetermined'] > 0 ? '' : 'not-').'pending.png'),
						array( 'alt' => _('Undetermined'), 'title' => _('Undetermined'),'style' => 'height: 12px; width: 12px')) ?></td>
		</tr>
		<?php } ?>
	</table>
</div>
<?php
} # end foreach  ?>
