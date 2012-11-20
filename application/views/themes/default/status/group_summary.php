<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
<div id="page_links">
		<em class="page-links-label"><?php echo _('View').', '.$label_view_for.':'; ?></em>		
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
  <div class="clear"></div>
	
	<hr />

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
  <div class="clear"> </div>
</div>

<div id="status_group-summary">
	<?php if (!empty($group_details)) { ?>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<div class="clear"> </div>
	<table id="group_summary_table">
		<thead>
			<tr>
				<th colspan="2"><?php echo $label_group_name ?></th>
				<?php if( $grouptype == 'host' ) { ?>
				<th class="no-sort"><?php echo _('Host Status Summary') ?></th>
				<?php } ?>
				<th class="no-sort"><?php echo _('Service Status Summary') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $i=0; foreach ($group_details as $details) { $i++; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even'?>">
				<td class="bl" style="white-space: normal">
					<?php echo html::anchor('status/'.$grouptype.'group/?group='.urlencode($details['name']).'&style=overview', security::xss_clean($details['alias'] . " (".$details['name'].")")) ?><br />
				</td>
				<td class="icon">
					<?php
					if (nacoma::link()===true)
						echo nacoma::link('configuration/configure/?type='.$grouptype.'group&name='.urlencode($details['name']), 'icons/16x16/nacoma.png', sprintf(_('Configure this %sgroup'), $grouptype));
					echo html::anchor('extinfo/details/?'.$grouptype.'group='.urlencode($details['name']),html::image($this->add_path('icons/16x16/extended-information.gif'), array('alt' => _('View Extended Information for this group'), 'title' => _('View Extended Information for this group'))), array('class' => 'image-link'));
					?>
				</td>
				<?php if( $grouptype == 'host' ) { ?>
				<td style="line-height: 20px; white-space: normal">
					<?php
						if ($details['hosts_up'] > 0) {
							# @@@FIXME: host_properties?
							echo html::image($this->add_path('icons/12x12/shield-up.png'), array('alt' => _('UP'), 'title' => _('UP'), 'class' => 'status-default'));
							echo html::anchor('status/'.$grouptype.'group/?group='.urlencode($details['name']).'&hoststatustypes='.nagstat::HOST_UP.'&hostprops=0&style=detail', $details['hosts_up'].' '._('Up'), array('class' => 'status-up')).'<br />';
						}
						if($details['hosts_down']> 0) {
							# @@@FIXME: host_properties?
							echo html::image($this->add_path('icons/12x12/shield-down.png'), array('alt' => _('DOWN'), 'title' => _('DOWN'), 'class' => 'status-default'));
							echo html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops=0', $details['hosts_down'].' '._('DOWN'), array('class' => 'status-down')).': ';
							$c = 0;
							if ($details['hosts_down_and_unhandled'] > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED), $details['hosts_down_and_unhandled'].' '._('Unhandled'));
								$c++;
							}
							if ($details['hosts_down_and_scheduled'] > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME, $details['hosts_down_and_scheduled'].' '._('Scheduled'));
								$c++;
							}
							if ($details['hosts_down_and_ack'] > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED, $details['hosts_down_and_ack'].' '._('Acknowledged'));
								$c++;
							}
							if ($details['hosts_down_and_disabled_active'] > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details['hosts_down_and_disabled_active'].' '._('Disabled'));
								$c++;
							}
							echo '<br />';
						}

						if($details['hosts_unreachable'] > 0){
							# @@@FIXME: host_properties?
							echo html::image($this->add_path('icons/12x12/shield-unreachable.png'), array('alt' => _('UNREACHABLE'), 'title' => _('UNREACHABLE'), 'class' => 'status-default'));
							echo html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops=0', $details['hosts_unreachable'].' '._('UNREACHABLE'), array('class' => 'status-unreachable')).': ';
							$c = 0;
							if ($details['hosts_unreachable_and_unhandled'] > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED), $details['hosts_unreachable_and_unhandled'].' '._('Unhandled'));
								$c++;
							}
							if ($details['hosts_unreachable_and_scheduled'] > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME, $details['hosts_unreachable_and_scheduled'].' '._('Scheduled'));
								$c++;
							}
							if ($details['hosts_unreachable_and_ack'] > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED, $details['hosts_unreachable_and_ack'].' '._('Acknowledged'));
								$c++;
							}
							if ($details['hosts_unreachable_and_disabled_active'] > 0) {
								echo ($c != 0 ? ', ' : '').html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details['hosts_unreachable_and_disabled_active'].' '._('Disabled'));
								$c++;
							}
							echo '<br />';
						}

						if($details['hosts_pending'] > 0) {
							# @@@FIXME: host_properties?
							echo html::image($this->add_path('icons/12x12/shield-pending.png'), array('alt' => _('PENDING'), 'title' => _('PENDING'), 'class' => 'status-default'));
							echo html::anchor('status/'.$grouptype.'/?group='.urlencode($details['name']).'&group_type='.$grouptype.'group&style=detail&hoststatustypes='.nagstat::HOST_PENDING.'&hostprops=0', $details['hosts_pending'].' '._('PENDING'), array('class' => 'status-pending'));
						} ?>
					</td>
					<?php } ?>

					<td style="line-height: 20px; white-space: normal">
						<?php
							if (!isset($details['services_ok'])) {
								echo _('No matching services');
								continue;
							}
							if ($details['services_ok'] > 0) {
								echo html::image($this->add_path('icons/12x12/shield-ok.png'), array('alt' => _('OK'), 'title' => _('OK'), 'class' => 'status-default'));
								echo html::anchor('status/service/?name='.urlencode($details['name']).'&servicestatustypes='.nagstat::SERVICE_OK.'&hoststatustypes='.$hoststatustypes.'&serviceprops='.$serviceproperties.'&hostproperties='.$hostproperties.'&style=detail&group_type='.$grouptype.'group', $details['services_ok'].' '._('OK'), array('class' => 'status-ok')).'<br />';
							}

							if ($details['services_warning'] > 0) {
								echo html::image($this->add_path('icons/12x12/shield-warning.png'), array('alt' => _('WARNING'), 'title' => _('WARNING'), 'class' => 'status-default'));
								echo html::anchor('status/service/?name='.urlencode($details['name']).'&servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.$hoststatustypes.'&serviceprops='.$serviceproperties.'&hostproperties='.$hostproperties.'&group_type='.$grouptype.'group', $details['services_warning'].' '._('WARNING'), array('class' => 'status-warning')).': ';

								$c = 0;
								if ($details['services_warning_and_unhandled'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceprops='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED).'&group_type='.$grouptype.'group', $details['services_warning_and_unhandled'].' '._('Unhandled'));
									$c++;
								}
								if ($details['services_warning_on_down_host'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&group_type='.$grouptype.'group', $details['services_warning_on_down_host'].' '._('on Problem Hosts'));
									$c++;
								}
								if ($details['services_warning_and_scheduled'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceprops='.nagstat::SERVICE_SCHEDULED_DOWNTIME.'&group_type='.$grouptype.'group', $details['services_warning_and_scheduled'].' '._('Scheduled'));
									$c++;
								}
								if ($details['services_warning_and_ack'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceprops='.nagstat::SERVICE_STATE_ACKNOWLEDGED.'&group_type='.$grouptype.'group', $details['services_warning_and_ack'].' '._('Acknowledged'));
									$c++;
								}
								if ($details['services_warning_and_disabled_active'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED.'&group_type='.$grouptype.'group', $details['services_warning_and_disabled_active'].' '._('Disabled'));
									$c++;
								}
								echo '<br />';
							}

							if ($details['services_unknown'] > 0) {
								echo html::image($this->add_path('icons/12x12/shield-unknown.png'), array('alt' => _('UNKNOWN'), 'title' => _('UNKNOWN'), 'class' => 'status-default'));
								echo html::anchor('status/service/?name='.urlencode($details['name']).'&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.$hoststatustypes.'&serviceprops='.$serviceproperties.'&hostproperties='.$hostproperties.'&group_type='.$grouptype.'group', $details['services_unknown'].' '._('UNKNOWN'), array('class' => 'status-unknown')).': ';

								$c = 0;
								if ($details['services_unknown_and_unhandled'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceprops='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED).'&group_type='.$grouptype.'group', $details['services_unknown_and_unhandled'].' '._('Unhandled'));
									$c++;
								}
								if ($details['services_unknown_on_down_host'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&group_type='.$grouptype.'group', $details['services_unknown_on_down_host'].' '._('on Problem Hosts'));
									$c++;
								}
								if ($details['services_unknown_and_scheduled'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceprops='.nagstat::SERVICE_SCHEDULED_DOWNTIME.'&group_type='.$grouptype.'group', $details['services_unknown_and_scheduled'].' '._('Scheduled'));
									$c++;
								}
								if ($details['services_unknown_and_ack'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceprops='.nagstat::SERVICE_STATE_ACKNOWLEDGED.'&group_type='.$grouptype.'group', $details['services_unknown_and_ack'].' '._('Acknowledged'));
									$c++;
								}
								if ($details['services_unknown_and_disabled_active'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED.'&group_type='.$grouptype.'group', $details['services_unknown_and_disabled_active'].' '._('Disabled'));
									$c++;
								}
								echo '<br />';
							}

							if ($details['services_critical'] > 0) {
								echo html::image($this->add_path('icons/12x12/shield-critical.png'), array('alt' => _('CRITICAL'), 'title' => _('CRITICAL'), 'class' => 'status-default'));
								echo html::anchor('status/service/?name='.urlencode($details['name']).'&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.$hoststatustypes.'&serviceprops='.$serviceproperties.'&hostproperties='.$hostproperties.'&group_type='.$grouptype.'group', $details['services_critical'].' '._('CRITICAL'), array('class' => 'status-critical')).': ';

								$c = 0;
								if ($details['services_critical_and_unhandled'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceprops='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED).'&group_type='.$grouptype.'group', $details['services_critical_and_unhandled'].' '._('Unhandled'));
									$c++;
								}
								if ($details['services_critical_on_down_host'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&group_type='.$grouptype.'group', $details['services_critical_on_down_host'].' '._('on Problem Hosts'));
									$c++;
								}
								if ($details['services_critical_and_scheduled'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceprops='.nagstat::SERVICE_SCHEDULED_DOWNTIME.'&group_type='.$grouptype.'group', $details['services_critical_and_scheduled'].' '._('Scheduled'));
									$c++;
								}
								if ($details['services_critical_and_ack'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceprops='.nagstat::SERVICE_STATE_ACKNOWLEDGED.'&group_type='.$grouptype.'group', $details['services_critical_and_ack'].' '._('Acknowledged'));
									$c++;
								}
								if ($details['services_critical_and_disabled_active'] > 0) {
									echo ($c != 0 ? ', ' : '').html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceprops='.nagstat::SERVICE_CHECKS_DISABLED.'&group_type='.$grouptype.'group', $details['services_critical_and_disabled_active'].' '._('Disabled'));
									$c++;
								}
								echo '<br />';
							}

							if ($details['services_pending'] > 0) {
								echo html::image($this->add_path('icons/12x12/shield-pending.png'), array('alt' => _('PENDING'), 'title' => _('PENDING'), 'class' => 'status-default'));
								echo html::anchor('status/service/?name='.urlencode($details['name']).'&style=detail&servicestatustypes='.nagstat::SERVICE_PENDING.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties.'&group_type='.$grouptype.'group', $details['services_pending'].' '._('PENDING'), array('class' => 'status-pending'));
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
				<th class="no-sort"><?php echo _('Host Status Summary') ?></th>
				<th class="no-sort"><?php echo _('Service Status Summary') ?></th>
			</tr>
		</thead>
		<tbody>
		<tr class="even">
			<td colspan="3"><?php echo _('No data found') ?></td>
		</tr>
		</tbody>
	</table><?php
		} ?>
</div>
