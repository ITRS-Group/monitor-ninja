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
	</tr>
	<?php } ?>
</table>
<?php } else { ?>
	<?php echo $label_no_data ?>
<?php } ?>
