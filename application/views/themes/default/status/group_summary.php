<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div align=center class='statusTitle'><?php echo $lable_header ?></div>

<div align=center>
<?php
if (!empty($group_details)) {
?>
<table border=1 class='status'>
	<tr>
		<th class='status'><?php echo $label_group_name ?></th>
		<th class='status'><?php echo $label_host_summary ?></th>
		<th class='status'><?php echo $label_service_summary ?></th>
	</tr>
	<?php
	foreach ($group_details as $details) {
	?>
	<tr class='statusEven'>
		<td class='statusEven'>
			<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=overview', $details->group_alias) ?>
			(<?php echo html::anchor('extinfo/details/servicegroup/'.$details->groupname, $details->groupname) ?>)
		</td>
		<td class='statusEven' align=center valign=center>
			<table border='0'>
	<?php	if ($details->hosts_up > 0) { ?>
				<tr>
					<td class='miniStatusUP'>
						<?php # @@@FIXME: host_properties? ?>
						<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?hoststatustypes='.nagstat::HOST_UP.'&hostprops=0', $details->hosts_up.' '.$label_up); ?>
					</td>
				</tr>
	<?php 	}

			if($details->hosts_down > 0) { ?>
				<tr>
					<td class="miniStatusDOWN">
						<table border="0">
							<tr>
								<td class="miniStatusDOWN">
								<?php # @@@FIXME: host_properties? ?>
									<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_DOWN, $details->hosts_down.' '.$label_down) ?>
								</td>
								<td>
									<table border="0">
									<?php	if ($details->hosts_down_unacknowledged > 0) { ?>
										<tr>
											<td width=100%% class='hostImportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED), $details->hosts_down_unacknowledged.' '.$label_unhandled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php	if ($details->hosts_down_scheduled > 0) { ?>
										<tr>
											<td width=100%% class='hostUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME, $details->hosts_down_scheduled.' '.$label_scheduled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php	if ($details->hosts_down_acknowledged > 0) { ?>
										<tr>
											<td width=100%% class='hostUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED, $details->hosts_down_acknowledged.' '.$label_acknowledged) ?>
											</td>
										</tr>
									<?php } ?>
									<?php	if ($details->hosts_down_disabled > 0) { ?>
										<tr>
											<td width=100%% class='hostUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_DOWN.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details->hosts_down_disabled.' '.$label_disabled) ?>
											</td>
										</tr>
									<?php } ?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
	<?php 	}

			if($details->hosts_unreachable > 0){ ?>
				<tr>
					<td class="miniStatusUNREACHABLE">
						<table border="0">
							<tr>
								<td class="miniStatusUNREACHABLE">
								<?php # @@@FIXME: host_properties? ?>
									<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_DOWN, $details->hosts_unreachable.' '.$label_unreachable) ?>
								</td>
								<td>
									<table border="0">
									<?php	if ($details->hosts_unreachable_unacknowledged > 0) { ?>
										<tr>
											<td width=100%% class='hostImportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED|nagstat::HOST_CHECKS_ENABLED), $details->hosts_unreachable_unacknowledged.' '.$label_unhandled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php	if ($details->hosts_unreachable_scheduled > 0) { ?>
										<tr>
											<td width=100%% class='hostUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_SCHEDULED_DOWNTIME, $details->hosts_unreachable_scheduled.' '.$label_scheduled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php	if ($details->hosts_unreachable_acknowledged > 0) { ?>
										<tr>
											<td width=100%% class='hostUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_STATE_ACKNOWLEDGED, $details->hosts_unreachable_acknowledged.' '.$label_acknowledged) ?>
											</td>
										</tr>
									<?php } ?>
									<?php	if ($details->hosts_unreachable_disabled > 0) { ?>
										<tr>
											<td width=100%% class='hostUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_UNREACHABLE.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details->hosts_unreachable_disabled.' '.$label_disabled) ?>
											</td>
										</tr>
									<?php } ?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
	<?php 	}

			if($details->hosts_pending > 0) { ?>
				<tr>
					<td class="miniStatusPENDING">
						<?php # @@@FIXME: host_properties? ?>
						<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&hoststatustypes='.nagstat::HOST_PENDING.'&hostprops='.nagstat::HOST_CHECKS_DISABLED, $details->hosts_pending.' '.$label_pending) ?>
					</td>
				</tr>
			<?php } ?>
			</table>
		</td>
		<td>
			<table border="0">
	<?php
		$service_data = false;
		$service_data = $details->service_data;
			if ($service_data->services_ok > 0) { ?>
				<tr>
					<td class="miniStatusOK">
						<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?servicestatustypes='.nagstat::SERVICE_OK.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_ok.' '.$label_ok) ?>
					</td>
				</tr>
	<?php 	}
			if ($service_data->services_warning > 0) { ?>
				<tr>
					<td class="miniStatusWARNING">
						<table border="0">
							<tr>
								<td class="miniStatusWARNING">
									<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_warning.' '.$label_warning) ?>
								</td>
								<td>
									<table border="0">
									<?php if ($service_data->services_warning_unacknowledged > 0) { ?>
										<tr>
											<td width=100%% class='serviceImportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceproperties='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED), $service_data->services_warning_unacknowledged.' '.$label_unhandled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_warning_host_problem > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), $service_data->services_warning_host_problem.' '.$label_on_problem_hosts) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_warning_scheduled > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceproperties='.nagstat::SERVICE_SCHEDULED_DOWNTIME, $service_data->services_warning_scheduled.' '.$label_scheduled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_warning_acknowledged > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceproperties='.nagstat::SERVICE_STATE_ACKNOWLEDGED, $service_data->services_warning_acknowledged.' '.$label_acknowledged) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_warning_disabled > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_WARNING.'&serviceproperties='.nagstat::SERVICE_CHECKS_DISABLED, $service_data->services_warning_disabled.' '.$label_disabled) ?>
											</td>
										</tr>
									<?php } ?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
	<?php 	}

			if ($service_data->services_unknown > 0) { ?>
				<tr>
					<td class="miniStatusUNKNOWN">
						<table border="0">
							<tr>
								<td class="miniStatusUNKNOWN">
									<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_unknown.' '.$label_unknown) ?>
								</td>
								<td>
									<table border="0">
									<?php if ($service_data->services_unknown_unacknowledged > 0) { ?>
										<tr>
											<td width=100%% class='serviceImportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceproperties='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED), $service_data->services_unknown_unacknowledged.' '.$label_unhandled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_unknown_host_problem > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), $service_data->services_unknown_host_problem.' '.$label_on_problem_hosts) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_unknown_scheduled > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceproperties='.nagstat::SERVICE_SCHEDULED_DOWNTIME, $service_data->services_unknown_scheduled.' '.$label_scheduled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_unknown_acknowledged > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceproperties='.nagstat::SERVICE_STATE_ACKNOWLEDGED, $service_data->services_unknown_acknowledged.' '.$label_acknowledged) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_unknown_disabled > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&serviceproperties='.nagstat::SERVICE_CHECKS_DISABLED, $service_data->services_unknown_disabled.' '.$label_disabled) ?>
											</td>
										</tr>
									<?php } ?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
	<?php 	}

			if ($service_data->services_critical > 0) { ?>
				<tr>
					<td class="miniStatusCRITICAL">
						<table border="0">
							<tr>
								<td class="miniStatusCRITICAL">
									<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_critical.' '.$label_critical) ?>
								</td>
								<td>
									<table border="0">
									<?php if ($service_data->services_critical_unacknowledged > 0) { ?>
										<tr>
											<td width=100%% class='serviceImportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.(nagstat::HOST_UP|nagstat::HOST_PENDING).'&serviceproperties='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED|nagstat::SERVICE_CHECKS_ENABLED), $service_data->services_critical_unacknowledged.' '.$label_unhandled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_critical_host_problem > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), $service_data->services_critical_host_problem.' '.$label_on_problem_hosts) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_critical_scheduled > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceproperties='.nagstat::SERVICE_SCHEDULED_DOWNTIME, $service_data->services_critical_scheduled.' '.$label_scheduled) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_critical_acknowledged > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceproperties='.nagstat::SERVICE_STATE_ACKNOWLEDGED, $service_data->services_critical_acknowledged.' '.$label_acknowledged) ?>
											</td>
										</tr>
									<?php } ?>
									<?php if ($service_data->services_critical_disabled > 0) { ?>
										<tr>
											<td width=100%% class='serviceUnimportantProblem'>
												<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_CRITICAL.'&serviceproperties='.nagstat::SERVICE_CHECKS_DISABLED, $service_data->services_critical_disabled.' '.$label_disabled) ?>
											</td>
										</tr>
									<?php } ?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
	<?php 	}
			if ($service_data->services_pending > 0) { ?>
			<tr>
				<td class="miniStatusPENDING">
					<A HREF='%s?servicegroup=%s&style=detail&servicestatustypes=%d&hoststatustypes=%d&serviceprops=%lu&hostprops=%lu'>%d PENDING</A>
					<?php echo html::anchor('status/servicegroup/'.$details->groupname.'?style=detail&servicestatustypes='.nagstat::SERVICE_PENDING.'&hoststatustypes='.$hoststatustypes.'&serviceproperties='.$serviceproperties.'&hostproperties='.$hostproperties, $service_data->services_pending.' '.$label_pending) ?>

				</td>
			</tr>
		<?php 	} ?>
			</table>
			<?php if (($service_data->services_ok + $service_data->services_warning + $service_data->services_unknown + $service_data->services_critical + $service_data->services_pending) == 0) { ?>
				<?php echo $label_no_servicedata ?>
			<?php } ?>
		</td>
	</tr>
	<?php } ?>
</table>
<?php } else { ?>
	<?php echo $label_no_data ?>
<?php } ?>
