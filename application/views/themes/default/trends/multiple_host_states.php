<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<br />
<?php
	echo html::anchor(Kohana::config('reports.reports_link').'/generate?type=avail&report_type='.$report_type.$selected_objects.$get_vars, html::image($this->add_path('icons/16x16/availability.png'), array('title' => $this->translate->_('View corresponding Availability report'))), array('style' => 'border: 0px; margin-right: 5px; margin-bottom: -4px; display: block; float: left'));
	echo html::anchor(Kohana::config('reports.reports_link').'/generate?type=avail&report_type='.$report_type.$selected_objects.$get_vars, $this->translate->_('View corresponding Availability report'));
?>
<div class="host_breakdown wide" style="margin-top: 15px;">
<?php foreach ($multiple_states as $data) { ?>
		<table summary="<?php echo $t->_('Host state breakdown') ?>" id="multiple_hosts" border="1">
			<tr>
				<th class="headerNone left" style="<?php echo (!$create_pdf) ? 'width: 70%;' : ''; ?>"><?php echo (!empty($data['groupname']) ? str_replace('Hostgroup:','',$data['groupname']) : 'Selected hosts'); ?></th>
				<th class="headerNone left" style="width: 60px"><?php echo $t->_('Actions') ?></th>
				<th class="headerNone"><?php echo $t->_('Up') ?></th>
				<th class="headerNone"><?php echo $t->_('Unreachable') ?></th>
				<th class="headerNone"><?php echo $t->_('Down') ?></th>
				<th class="headerNone"><?php echo $t->_('Undetermined') ?></th>
			</tr>
			<?php for ($i=0;$i<$data['nr_of_items'];$i++): ?>
			<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'?>">
			<?php if (!$use_alias) {
				$host_link = str_replace('&','&amp;',$data['host_link'][$i]);
				$host_link = str_replace(Kohana::config('reports.reports_link').'/generate?type=avail', 'trends/generate?1', $host_link);?>
				<td><?php echo $create_pdf != false ? $data['HOST_NAME'][$i] : '<a href="'.$host_link.'">' . $data['HOST_NAME'][$i] . '</a>' ?></td>
				<?php } else { ?>
				<td><?php echo $this->_get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo $create_pdf != false ? $data['HOST_NAME'][$i] :'<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>' ?>)</td>
				<?php } ?>
				<td class="data">
					<?php echo html::anchor('status/service?name='.$data['HOST_NAME'][$i], html::image($this->add_path('icons/16x16/service-details.gif'), array('title' => $this->translate->_('Service details for this Host'))), array('style' => 'border: 0px')) ?>
					<?php echo html::anchor(Kohana::config('reports.reports_link').'/generate?type=avail&host_name[]='.$data['HOST_NAME'][$i].$get_vars, html::image($this->add_path('icons/16x16/availability.png'), array('title' => $this->translate->_('Availability report for this Host'))), array('style' => 'border: 0px')) ?>
					<?php echo html::anchor('showlog/alert_history/'.$data['HOST_NAME'][$i], html::image($this->add_path('icons/16x16/alert-history.png'), array('title' => $this->translate->_('Alert History for this Host'))), array('style' => 'border: 0px')) ?>
					<?php echo html::anchor('notifications/host/'.$data['HOST_NAME'][$i], html::image($this->add_path('icons/16x16/notify.png'), array('title' => $this->translate->_('Notifications for this Host'))), array('style' => 'border: 0px')) ?>
					<?php echo html::anchor('histogram/host/'.$data['HOST_NAME'][$i], html::image($this->add_path('icons/16x16/histogram.png'), array('title' => $this->translate->_('Alert Histogram for this Host'))), array('style' => 'border: 0px')) ?>
				</td>
				<td class="data">
					<?php echo reports::format_report_value($data['up'][$i]) ?> %
					<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['up'][$i]) > 0 ? '' : 'not-').'up.png'),
											array( 'alt' => $t->_('Up'), 'title' => $t->_('Up'), 'style' => 'height: 12px; width: 11px')); ?>
				</td>
				<td class="data">
					<?php echo reports::format_report_value($data['unreachable'][$i]) ?> %
					<?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['unreachable'][$i]) > 0 ? '' : 'not-').'unreachable.png'),
										array( 'alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable'), 'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['down'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['down'][$i]) > 0 ? '' : 'not-').'down.png'),
							array( 'alt' => $t->_('Down'), 'title' => $t->_('Down'), 'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo reports::format_report_value($data['undetermined'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['undetermined'][$i]) > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'), 'style' => 'height: 12px; width: 11px')) ?></td>
			</tr>
			<?php endfor; if ($use_average==0): ?>
			<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'; $i++?>">
				<td colspan="2"><?php echo $t->_('Average'); ?></td>
				<td class="data"><?php echo $data['average_up'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_up'] > 0 ? '' : 'not-').'up.png'),
							array( 'alt' => $t->_('Up'), 'title' => $t->_('Up'), 'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_unreachable'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unreachable'] > 0 ? '' : 'not-').'unreachable.png'),
							array( 'alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable'), 'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_down'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_down'] > 0 ? '' : 'not-').'down.png'),
							array( 'alt' => $t->_('Down'), 'title' => $t->_('Down'), 'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'), 'style' => 'height: 12px; width: 11px')) ?></td>
			</tr>
			<?php endif; ?>
			<tr class="group-average <?php echo ($i%2 == 0) ? 'even' : 'odd'?>">
				<td colspan="2"><?php echo ($use_average==0) ? $t->_('Group availability (SLA)') : $t->_('Average'); ?></td>
				<td class="data"><?php echo $data['group_average_up'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_up'] > 0 ? '' : 'not-').'up.png'),
							array( 'alt' => $t->_('Up'), 'title' => $t->_('Up'), 'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_unreachable'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_unreachable'] > 0 ? '' : 'not-').'unreachable.png'),
							array( 'alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable'), 'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_down'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_down'] > 0 ? '' : 'not-').'down.png'),
							array( 'alt' => $t->_('Down'), 'title' => $t->_('Down'), 'style' => 'height: 12px; width: 11px')) ?></td>
				<td class="data"><?php echo $data['group_average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'), 'style' => 'height: 12px; width: 11px')) ?></td>
			</tr>
		</table>
<?php } ?>
</div>