<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<div class="host_breakdown wide" style="margin-top: 15px;">
<?php foreach ($multiple_states as $data) { ?>
		<table summary="<?php echo $t->_('Host state breakdown') ?>" id="multiple_hosts" border="1">
			<tr>
				<th class="headerNone left" style="<?php echo (!$create_pdf) ? 'width: 90%;' : ''; ?>"><?php echo (!empty($data['groupname']) ? str_replace('Hostgroup:','',$data['groupname']) : 'Selected hosts'); ?></th>
				<th class="headerNone"><?php echo $t->_('Up') ?></th>
				<th class="headerNone"><?php echo $t->_('Unreachable') ?></th>
				<th class="headerNone"><?php echo $t->_('Down') ?></th>
				<th class="headerNone"><?php echo $t->_('Undetermined') ?></th>
			</tr>
			<?php for ($i=0;$i<$data['nr_of_items'];$i++): ?>
			<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'?>">
			<?php if (!$use_alias) { ?>
				<td><?php echo $create_pdf != false ? wordwrap($data['HOST_NAME'][$i],30,'<br />',true) : '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . wordwrap($data['HOST_NAME'][$i],30,'<br />',true) . '</a>' ?></td>
				<?php } else { ?>
				<td><?php echo $this->_get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo $create_pdf != false ? wordwrap($data['HOST_NAME'][$i],30,'<br />',true) :'<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . wordwrap($data['HOST_NAME'][$i],30,'<br />',true) . '</a>' ?>)</td>
				<?php } ?>
				<td class="data">
					<?php echo reports::format_report_value($data['up'][$i]) ?> %
					<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['up'][$i]) > 0 ? '' : 'not-').'up.png'),
											array( 'alt' => $t->_('Up'), 'title' => $t->_('Up'), 'style' => 'height: 12px; width: 12px')); ?>
				</td>
				<td class="data">
					<?php echo reports::format_report_value($data['unreachable'][$i]) ?> %
					<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['unreachable'][$i]) > 0 ? '' : 'not-').'unreachable.png'),
										array( 'alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['down'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['down'][$i]) > 0 ? '' : 'not-').'down.png'),
							array( 'alt' => $t->_('Down'), 'title' => $t->_('Down'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['undetermined'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['undetermined'][$i]) > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'), 'style' => 'height: 12px; width: 12px')) ?></td>
			</tr>
			<?php endfor; if ($use_average==0): ?>
			<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'; $i++?>">
				<td><?php echo $t->_('Average'); ?></td>
				<td class="data"><?php echo $data['average_up'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_up'] > 0 ? '' : 'not-').'up.png'),
							array( 'alt' => $t->_('Up'), 'title' => $t->_('Up'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['average_unreachable'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unreachable'] > 0 ? '' : 'not-').'unreachable.png'),
							array( 'alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['average_down'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_down'] > 0 ? '' : 'not-').'down.png'),
							array( 'alt' => $t->_('Down'), 'title' => $t->_('Down'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'), 'style' => 'height: 12px; width: 12px')) ?></td>
			</tr>
			<?php endif; ?>
			<tr class="group-average <?php echo ($i%2 == 0) ? 'even' : 'odd'?>">
				<td><?php echo ($use_average==0) ? $t->_('Group availability (SLA)') : $t->_('Average'); ?></td>
				<td class="data"><?php echo $data['group_average_up'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_up'] > 0 ? '' : 'not-').'up.png'),
							array( 'alt' => $t->_('Up'), 'title' => $t->_('Up'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['group_average_unreachable'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_unreachable'] > 0 ? '' : 'not-').'unreachable.png'),
							array( 'alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['group_average_down'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_down'] > 0 ? '' : 'not-').'down.png'),
							array( 'alt' => $t->_('Down'), 'title' => $t->_('Down'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td class="data"><?php echo $data['group_average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'), 'style' => 'height: 12px; width: 12px')) ?></td>
			</tr>
			<?php if (!$create_pdf) { ?>
			<tr id="pdf-hide">
				<td colspan="5" class="testcase-button"><?php echo $this->_build_testcase_form($data[';testcase;']); ?></td>
			</tr>
			<?php } ?>
		</table>
		<?php if ($create_pdf) { ?><br /><?php } ?>
<?php } ?>
</div>