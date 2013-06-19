<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php foreach ($multiple_states as $data) {
	if ($data['groupname']) {
		if ($options['use_alias'])
			$groupname = $this->_get_alias('hostgroups', $data['groupname']).' ('.$data['groupname'].')';
		else
			$groupname = $data['groupname'];
	} else {
		$groupname = false; # Because capitalization
	}
?>
<div class="state_services report-block">
	<table summary="<?php echo _('Host state breakdown') ?>" id="multiple_host" class="report-block">
		<tr>
			<th><?php echo help::render('hostgroup_breakdown').' '.($groupname?:_('Selected hosts')); ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Up') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Unreachable') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Down') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Undetermined') ?></th>
		</tr>
		<?php for ($i=0;$i<$data['nr_of_items'];$i++) {
			if ($options['use_alias'])
				$name = $this->_get_alias('hosts', $data['HOST_NAME'][$i]).' ('.$data['HOST_NAME'][$i].')';
			else
				$name = $data['HOST_NAME'][$i];
		?>

		<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'?>">
			<td><?php echo '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $name . '</a>' ?></td>
			<td class="data"><?php echo reports::format_report_value($data['up'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['up'][$i]) > 0 ? '' : 'not-').'up.png'),
				array( 'alt' => _('Up'), 'title' => _('Up'), 'style' => 'height: 12px; width: 12px'));
				if (isset($data['counted_as_up'][$i]) && $data['counted_as_up'][$i] > 0) {
					echo " (" . reports::format_report_value($data['counted_as_up'][$i]) ."% in other states)";
				}?></td>
			<td class="data"><?php echo reports::format_report_value($data['unreachable'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['unreachable'][$i]) > 0 ? '' : 'not-').'unreachable.png'),
						array( 'alt' => _('Unreachable'), 'title' => _('Unreachable'), 'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data"><?php echo reports::format_report_value($data['down'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['down'][$i]) > 0 ? '' : 'not-').'down.png'),
						array( 'alt' => _('Down'), 'title' => _('Down'), 'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="data"><?php echo reports::format_report_value($data['undetermined'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['undetermined'][$i]) > 0 ? '' : 'not-').'pending.png'),
						array( 'alt' => _('Undetermined'), 'title' => _('Undetermined'), 'style' => 'height: 12px; width: 12px')) ?></td>
		</tr>
		<?php } ?>
	</table>
</div>
<div class="state_services report-block">
	<table summary="<?php echo _('Host state breakdown') ?>" id="multiple_host" class="report-block">
		<tr>
			<th><?php echo help::render('average_and_sla').' '.sprintf(_('Summary for %s'), $groupname?:_('selected hosts')); ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Up') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Unreachable') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Down') ?></th>
			<th class="headerNone" style="width: 80px"><?php echo _('Undetermined') ?></th>
		</tr>
		<?php ?>
		<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'?>">
			<td><?php echo $options->get_value('sla_mode') ?></td>
			<td class="summary up <?php echo ($data['group_up']>0?'nonzero':'') ?>"><?php echo $data['group_up'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_up'] > 0 ? '' : 'not-').'up.png'),
						array( 'alt' => _('Up'), 'title' => _('Up'), 'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary unreachable <?php echo ($data['group_unreachable']>0?'nonzero':'') ?>"><?php echo $data['group_unreachable'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_unreachable'] > 0 ? '' : 'not-').'unreachable.png'),
						array( 'alt' => _('Unreachable'), 'title' => _('Unreachable'), 'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary down <?php echo ($data['group_down']>0?'nonzero':'') ?>"><?php echo $data['group_down'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_down'] > 0 ? '' : 'not-').'down.png'),
						array( 'alt' => _('Down'), 'title' => _('Down'), 'style' => 'height: 12px; width: 12px')) ?></td>
			<td class="summary undetermined <?php echo ($data['group_undetermined']?'nonzero':'') ?>"><?php echo $data['group_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_undetermined'] > 0 ? '' : 'not-').'pending.png'),
						array( 'alt' => _('Undetermined'), 'title' => _('Undetermined'), 'style' => 'height: 12px; width: 12px')) ?></td>
		</tr>
	</table>
</div>
<?php } ?>
