<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
<div class="widget left w32" id="page_links">
		<ul>
		<li><?php echo $this->translate->_('View').', '.$label_view_for.':'; ?></li>
		<?php
		if (isset($page_links)) {
			foreach ($page_links as $label => $link) {
				?>
				<li><?php echo html::anchor($link, $label) ?></li>
				<?php
			}
		}
		?>
		</ul>
	</div>
	<?php
		if (!empty($widgets)) {
			foreach ($widgets as $widget) {
				echo $widget;
			}
		}
	?>

	<div id="filters" class="left">
	<?php
	if (isset($filters) && !empty($filters)) {
		echo $filters;
	}
	?>
	</div>
</div>

<div class="widget left w98" id="status_group-summary">
	<?php if (!empty($group_details)) { ?>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<table id="group_summary_table">
		<thead>
			<tr>
				<th colspan="2"><?php echo $label_group_name ?></th>
				<th class="no-sort"><?php echo $label_host_summary ?></th>
				<th class="no-sort"><?php echo $label_service_summary ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $i=0; foreach ($group_details as $details) { $i++; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even'?>">
				<td class="bl" style="white-space: normal">
					<?php echo html::anchor('status/'.$grouptype.'group/'.urlencode($details->groupname).'?style=overview', $details->alias) ?><br />
					(<?php echo html::anchor('extinfo/details/'.$grouptype.'group/'.urlencode($details->groupname), $details->groupname) ?>)
				</td>
				<td class="icon">
					<?php if (nacoma::link()===true)
						echo nacoma::link('configuration/configure/'.$grouptype.'group/'.urlencode($details->groupname), 'icons/16x16/nacoma.png', sprintf($this->translate->_('Configure this %sgroup'), $grouptype));
					?>
				</td>
				<td style="line-height: 20px; white-space: normal">
					<?php
						if ($details->hosts_up > 0) {
							# @@@FIXME: host_properties?
							echo html::image($this->add_path('icons/12x12/shield-up.png'), array('alt' => $label_up, 'title' => $label_up, 'class' => 'status-default'));
							echo html::anchor('status/'.$grouptype.'group/'.urlencode($details->groupname).'?hoststatustypes='.nagstat::HOST_UP.'&hostprops=0&style=detail', $details->hosts_up.' '.$label_up, array('class' => 'status-up')).'<br />';
						}
						if($details->hosts_down > 0) {
							# @@@FIXME: host_properties?
							echo html::image($this->add_path('icons/12x12/shield-down.png'), array('alt' => $label_down, 'title' => $label_down, 'class' => 'status-default'));
							echo html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops=0', $details->hosts_down.' '.$label_down, array('class' => 'status-down')).': ';
							$c = 0;
							if ($details->hosts_down_unhandled > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED), $details->hosts_down_unhandled.' '.$label_unhandled);
								$c++;
							}
							if ($details->hosts_down_scheduled > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME, $details->hosts_down_scheduled.' '.$label_scheduled);
								$c++;
							}
							if ($details->hosts_down_acknowledged > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED, $details->hosts_down_acknowledged.' '.$label_acknowledged);
								$c++;
							}
							if ($details->hosts_down_disabled > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details->hosts_down_disabled.' '.$label_disabled);
								$c++;
							}
							echo '<br />';
						}

						if($details->hosts_unreachable > 0){
							# @@@FIXME: host_properties?
							echo html::image($this->add_path('icons/12x12/shield-unreachable.png'), array('alt' => $label_unreachable, 'title' => $label_unreachable, 'class' => 'status-default'));
							echo html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops=0', $details->hosts_unreachable.' '.$label_unreachable, array('class' => 'status-unreachable')).': ';
							$c = 0;
							if ($details->hosts_unreachable_unhandled > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED), $details->hosts_unreachable_unhandled.' '.$label_unhandled);
								$c++;
							}
							if ($details->hosts_unreachable_scheduled > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME, $details->hosts_unreachable_scheduled.' '.$label_scheduled);
								$c++;
							}
							if ($details->hosts_unreachable_acknowledged > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED, $details->hosts_unreachable_acknowledged.' '.$label_acknowledged);
								$c++;
							}
							if ($details->hosts_unreachable_disabled > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details->hosts_unreachable_disabled.' '.$label_disabled);
								$c++;
							}
							echo '<br />';
						}

						if($details->hosts_pending > 0) {
							# @@@FIXME: host_properties?
							echo html::image($this->add_path('icons/12x12/shield-pending.png'), array('alt' => $label_pending, 'title' => $label_pending, 'class' => 'status-default'));
							echo html::anchor('status/'.$grouptype.'/'.urlencode($details->groupname).'?group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_PENDING.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details->hosts_pending.' '.$label_pending, array('class' => 'status-pending'));
						} ?>
					</td>

					<td style="line-height: 20px; white-space: normal">
						<?php
							if ($details->services_ok > 0) {
								echo html::image($this->add_path('icons/12x12/shield-ok.png'), array('alt' => $label_ok, 'title' => $label_ok, 'class' => 'status-default'));
								echo html::anchor('status/service/'.urlencode($details->groupname).'?servicestatustypes='.nagstat::SERVICE_OK.'&hoststatustypes='.$hoststatustypes.'&serviceprops='.$serviceproperties.'&hostproperties='.$hostproperties.'&style=detail&group_type='.$grouptype.'group', $details->services_ok.' '.$label_ok, array('class' => 'status-ok')).'<br />';
							}

							if ($details->services_warning > 0) {
								echo html::image($this->add_path('icons/12x12/shield-warning.png'), array('alt' => $label_warning, 'title' => $label_warning, 'class' => 'status-default'));
								echo html::anchor('status/service/'.urlencode($details->groupname).'?servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.$hoststatustypes.'&serviceprops='.$serviceproperties.'&hostproperties='.$hostproperties.'&group_type='.$grouptype.'group', $details->services_warning.' '.$label_warning, array('class' => 'status-warning')).': ';

								$c = 0;
								if ($details->services_warning_unhandled > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceprops='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED).'&group_type='.$grouptype.'group', $details->services_warning_unhandled.' '.$label_unhandled);
									$c++;
								}
								if ($details->services_warning_host_problem > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&group_type='.$grouptype.'group', $details->services_warning_host_problem.' '.$label_on_problem_hosts);
									$c++;
								}
								if ($details->services_warning_scheduled > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceprops='.nagstat::SERVICE_SCHEDULED_DOWNTIME.'&group_type='.$grouptype.'group', $details->services_warning_scheduled.' '.$label_scheduled);
									$c++;
								}
								if ($details->services_warning_acknowledged > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceprops='.nagstat::SERVICE_STATE_ACKNOWLEDGED.'&group_type='.$grouptype.'group', $details->services_warning_acknowledged.' '.$label_acknowledged);
									$c++;
								}
								if ($details->services_warning_disabled > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED.'&group_type='.$grouptype.'group', $details->services_warning_disabled.' '.$label_disabled);
									$c++;
								}
								echo '<br />';
							}

							if ($details->services_unknown > 0) {
								echo html::image($this->add_path('icons/12x12/shield-unknown.png'), array('alt' => $label_unknown, 'title' => $label_unknown, 'class' => 'status-default'));
								echo html::anchor('status/service/'.urlencode($details->groupname).'?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.$hoststatustypes.'&serviceprops='.$serviceproperties.'&hostproperties='.$hostproperties.'&group_type='.$grouptype.'group', $details->services_unknown.' '.$label_unknown, array('class' => 'status-unknown')).': ';

								$c = 0;
								if ($details->services_unknown_unhandled > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceprops='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED).'&group_type='.$grouptype.'group', $details->services_unknown_unhandled.' '.$label_unhandled);
									$c++;
								}
								if ($details->services_unknown_host_problem > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&group_type='.$grouptype.'group', $details->services_unknown_host_problem.' '.$label_on_problem_hosts);
									$c++;
								}
								if ($details->services_unknown_scheduled > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceprops='.nagstat::SERVICE_SCHEDULED_DOWNTIME.'&group_type='.$grouptype.'group', $details->services_unknown_scheduled.' '.$label_scheduled);
									$c++;
								}
								if ($details->services_unknown_acknowledged > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceprops='.nagstat::SERVICE_STATE_ACKNOWLEDGED.'&group_type='.$grouptype.'group', $details->services_unknown_acknowledged.' '.$label_acknowledged);
									$c++;
								}
								if ($details->services_unknown_disabled > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED.'&group_type='.$grouptype.'group', $details->services_unknown_disabled.' '.$label_disabled);
									$c++;
								}
								echo '<br />';
							}

							if ($details->services_critical > 0) {
								echo html::image($this->add_path('icons/12x12/shield-critical.png'), array('alt' => $label_critical, 'title' => $label_critical, 'class' => 'status-default'));
								echo html::anchor('status/service/'.urlencode($details->groupname).'?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.$hoststatustypes.'&serviceprops='.$serviceproperties.'&hostproperties='.$hostproperties.'&group_type='.$grouptype.'group', $details->services_critical.' '.$label_critical, array('class' => 'status-critical')).': ';

								$c = 0;
								if ($details->services_critical_unhandled > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceprops='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED).'&group_type='.$grouptype.'group', $details->services_critical_unhandled.' '.$label_unhandled);
									$c++;
								}
								if ($details->services_critical_host_problem > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&group_type='.$grouptype.'group', $details->services_critical_host_problem.' '.$label_on_problem_hosts);
									$c++;
								}
								if ($details->services_critical_scheduled > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceprops='.nagstat::SERVICE_SCHEDULED_DOWNTIME.'&group_type='.$grouptype.'group', $details->services_critical_scheduled.' '.$label_scheduled);
									$c++;
								}
								if ($details->services_critical_acknowledged > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceprops='.nagstat::SERVICE_STATE_ACKNOWLEDGED.'&group_type='.$grouptype.'group', $details->services_critical_acknowledged.' '.$label_acknowledged);
									$c++;
								}
								if ($details->services_critical_disabled > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED.'&group_type='.$grouptype.'group', $details->services_critical_disabled.' '.$label_disabled);
									$c++;
								}
								echo '<br />';
							}

							if ($details->services_pending > 0) {
								echo html::image($this->add_path('icons/12x12/shield-pending.png'), array('alt' => $label_pending, 'title' => $label_pending, 'class' => 'status-default'));
								echo html::anchor('status/service/'.urlencode($details->groupname).'?style=detail&servicestatustypes='.nagstat::SERVICE_PENDING.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties.'&group_type='.$grouptype.'group', $details->services_pending.' '.$label_pending, array('class' => 'status-pending'));
							}

							if (($details->services_ok + $details->services_warning + $details->services_unknown + $details->services_critical + $details->services_pending) == 0) {
								echo $label_no_servicedata;
							} ?>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<br /><br />
	<?php }
		else { ?>
	<table id="group_summary_table">
		<thead>
			<tr>
				<th><?php echo $label_group_name ?></th>
				<th class="no-sort"><?php echo $label_host_summary ?></th>
				<th class="no-sort"><?php echo $label_service_summary ?></th>
			</tr>
		</thead>
		<tbody>
		<tr class="even">
			<td colspan="3"><?php echo $label_no_data ?></td>
		</tr>
		</tbody>
	</table><?php
		} ?>
</div>
