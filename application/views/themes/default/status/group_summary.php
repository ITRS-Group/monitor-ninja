<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
	if (!empty($widgets)) {
		foreach ($widgets as $widget) {
			echo $widget;
		}
	}
?>
<div class="widget left w32" id="page_links">
	<ul>
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

<div id="filters" class="left">
<?php
if (isset($filters) && !empty($filters)) {
	echo $filters;
}
?>
</div>

<div class="widget left w98" id="status_group-summary">
	<?php if (!empty($group_details)) { ?>
	<table id="group_summary_table">
	<thead>
		<tr>
			<th><?php echo $label_group_name ?></th>
			<th class="no-sort"><?php echo $label_host_summary ?></th>
			<th class="no-sort"><?php echo $label_service_summary ?></th>
		</tr>
		</thead>
		<tbody>
		<?php $i=0; foreach ($group_details as $details) { $i++; ?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even'?>">
			<td class="bl">
				<?php echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=overview', $details->group_alias) ?>
				(<?php echo html::anchor('extinfo/details/'.$grouptype.'group/'.$details->groupname, $details->groupname) ?>)
			</td>
			<td style="line-height: 20px">
				<?php
					if ($details->hosts_up > 0) {
						# @@@FIXME: host_properties?
						echo html::image('application/views/themes/default/icons/12x12/shield-up.png', array('alt' => $label_up, 'title' => $label_up, 'style' => 'margin-bottom: -2px'));
						echo ' &nbsp;'.html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?hoststatustypes='.nagstat::HOST_UP.'&hostprops=0', $details->hosts_up.' '.$label_up).'<br />';
					}

					if($details->hosts_down > 0) {
						# @@@FIXME: host_properties?
						echo html::image('application/views/themes/default/icons/12x12/shield-down.png', array('alt' => $label_down, 'title' => $label_down, 'style' => 'margin-bottom: -2px'));
						echo ' &nbsp;'.html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_DOWN, $details->hosts_down.' '.$label_down).': ';

						if ($details->hosts_down_unacknowledged > 0) {
							echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED), $details->hosts_down_unacknowledged.' '.$label_unhandled);
						}
						if ($details->hosts_down_scheduled > 0) {
							echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME, $details->hosts_down_scheduled.' '.$label_scheduled);
						}
						if ($details->hosts_down_acknowledged > 0) {
							echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED, $details->hosts_down_acknowledged.' '.$label_acknowledged);
						}
						if ($details->hosts_down_disabled > 0) {
							echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details->hosts_down_disabled.' '.$label_disabled);
						}
						echo '<br />';
					}

					if($details->hosts_unreachable > 0){
						# @@@FIXME: host_properties?
						echo html::image('application/views/themes/default/icons/12x12/shield-unreachable.png', array('alt' => $label_unreachable, 'title' => $label_unreachable, 'style' => 'margin-bottom: -2px'));
						echo ' &nbsp;'.html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_DOWN, $details->hosts_unreachable.' '.$label_unreachable).': ';

						if ($details->hosts_unreachable_unacknowledged > 0) {
							echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED), $details->hosts_unreachable_unacknowledged.' '.$label_unhandled);
						}
						if ($details->hosts_unreachable_scheduled > 0) {
							echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME, $details->hosts_unreachable_scheduled.' '.$label_scheduled);
						}
						if ($details->hosts_unreachable_acknowledged > 0) {
							echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED, $details->hosts_unreachable_acknowledged.' '.$label_acknowledged);
						}
						if ($details->hosts_unreachable_disabled > 0) {
							echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details->hosts_unreachable_disabled.' '.$label_disabled);
						}
						echo '<br />';
					}

					if($details->hosts_pending > 0) {
						# @@@FIXME: host_properties?
						echo html::image('application/views/themes/default/icons/12x12/shield-pending.png', array('alt' => $label_pending, 'title' => $label_pending, 'style' => 'margin-bottom: -2px')).' &nbsp;';
						echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_PENDING.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details->hosts_pending.' '.$label_pending);
					} ?>
			</td>

			<td style="line-height: 20px">
				<?php
					$service_data = false;
					$service_data = $details->service_data;
					if ($service_data->services_ok > 0) {
						echo html::image('application/views/themes/default/icons/12x12/shield-ok.png', array('alt' => $label_ok, 'title' => $label_ok, 'style' => 'margin-bottom: -2px')).' &nbsp;';
						echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?servicestatustypes='.nagstat::SERVICE_OK.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_ok.' '.$label_ok).'<br />';
					}

					if ($service_data->services_warning > 0) {
						echo html::image('application/views/themes/default/icons/12x12/shield-warning.png', array('alt' => $label_warning, 'title' => $label_warning, 'style' => 'margin-bottom: -2px')).' &nbsp;';
						echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_warning.' '.$label_warning).': ';
						$c = 0;
						if ($service_data->services_warning_unacknowledged > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceproperties='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED), $service_data->services_warning_unacknowledged.' '.$label_unhandled);
							$c++;
						}
						if ($service_data->services_warning_host_problem > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), $service_data->services_warning_host_problem.' '.$label_on_problem_hosts);
							$c++;
						}
						if ($service_data->services_warning_scheduled > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceproperties='.nagstat::SERVICE_SCHEDULED_DOWNTIME, $service_data->services_warning_scheduled.' '.$label_scheduled);
							$c++;
						}
						if ($service_data->services_warning_acknowledged > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceproperties='.nagstat::SERVICE_STATE_ACKNOWLEDGED, $service_data->services_warning_acknowledged.' '.$label_acknowledged);
							$c++;
						}
						if ($service_data->services_warning_disabled > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceproperties='.nagstat::SERVICE_CHECKS_DISABLED, $service_data->services_warning_disabled.' '.$label_disabled);
							$c++;
						}
						echo '<br />';
					}

					if ($service_data->services_unknown > 0) {
						echo html::image('application/views/themes/default/icons/12x12/shield-unknown.png', array('alt' => $label_unknown, 'title' => $label_unknown, 'style' => 'margin-bottom: -2px')).' &nbsp;';
						echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_unknown.' '.$label_unknown).': ';

						$c = 0;
						if ($service_data->services_unknown_unacknowledged > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceproperties='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED), $service_data->services_unknown_unacknowledged.' '.$label_unhandled);
							$c++;
						}
						if ($service_data->services_unknown_host_problem > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), $service_data->services_unknown_host_problem.' '.$label_on_problem_hosts);
							$c++;
						}
						if ($service_data->services_unknown_scheduled > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceproperties='.nagstat::SERVICE_SCHEDULED_DOWNTIME, $service_data->services_unknown_scheduled.' '.$label_scheduled);
							$c++;
						}
						if ($service_data->services_unknown_acknowledged > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceproperties='.nagstat::SERVICE_STATE_ACKNOWLEDGED, $service_data->services_unknown_acknowledged.' '.$label_acknowledged);
							$c++;
						}
						if ($service_data->services_unknown_disabled > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceproperties='.nagstat::SERVICE_CHECKS_DISABLED, $service_data->services_unknown_disabled.' '.$label_disabled);
							$c++;
						}
						echo '<br />';
					}

					if ($service_data->services_critical > 0) {
						echo html::image('application/views/themes/default/icons/12x12/shield-critical.png', array('alt' => $label_critical, 'title' => $label_critical, 'style' => 'margin-bottom: -2px')).' &nbsp;';
						echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_critical.' '.$label_critical).': ';

						$c = 0;
						if ($service_data->services_critical_unacknowledged > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceproperties='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED), $service_data->services_critical_unacknowledged.' '.$label_unhandled);
							$c++;
						}
						if ($service_data->services_critical_host_problem > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), $service_data->services_critical_host_problem.' '.$label_on_problem_hosts);
							$c++;
						}
						if ($service_data->services_critical_scheduled > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceproperties='.nagstat::SERVICE_SCHEDULED_DOWNTIME, $service_data->services_critical_scheduled.' '.$label_scheduled);
							$c++;
						}
						if ($service_data->services_critical_acknowledged > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceproperties='.nagstat::SERVICE_STATE_ACKNOWLEDGED, $service_data->services_critical_acknowledged.' '.$label_acknowledged);
							$c++;
						}
						if ($service_data->services_critical_disabled > 0) {
							echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceproperties='.nagstat::SERVICE_CHECKS_DISABLED, $service_data->services_critical_disabled.' '.$label_disabled);
							$c++;
						}
						echo '<br />';
					}

					if ($service_data->services_pending > 0) {
						echo html::image('application/views/themes/default/icons/12x12/shield-pedning.png', array('alt' => $label_pending, 'title' => $label_pending, 'style' => 'margin-bottom: -2px')).' &nbsp;';
						echo '<a href="%s?'.$grouptype.'group=%s&style=detail&servicestatustypes=%d&hoststatustypes=%d&serviceprops=%lu&hostprops=%lu">%d PENDING</a>';
						echo html::anchor('status/'.$grouptype.'group/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_PENDING.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_pending.' '.$label_pending);
					}

					if (($service_data->services_ok + $service_data->services_warning + $service_data->services_unknown + $service_data->services_critical + $service_data->services_pending) == 0) {
						echo $label_no_servicedata;
					} ?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
	</table>
	<?php }
		else {
			echo $label_no_data;
		}
	?>
</div>