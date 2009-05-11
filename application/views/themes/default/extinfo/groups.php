
<table border="0" width="100%">
	<tr>
		<td align="center" valign="center" width="33%">
			<div class='data'>
				<?php echo $label_grouptype ?>
			</div>
			<div class='dataTitle'>
				<?php echo $group_alias ?>
			</div>
			<div class='dataTitle'>
				(<?php echo $groupname ?>)
			</div>
		</td>
	</tr>
</table>
<br />
<div align="center">
	<table border="0" width="100%">
		<tr>
			<td align="center" valign="top" class='stateInfoPanel'></td>
			<td align="center" valign="top" class='stateInfoPanel' rowspan="2">
				<div class='dataTitle'>
				<?php echo $label_grouptype ?> <?php echo $label_commands ?>
				</div>

				<table border="1" cellspacing="0" cellpadding="0" class='command'>
					<tr>
						<td>
							<table border="0" cellspacing="0" cellpadding="0" class='command'>
								<tr class='command'>
									<td>
										<img src='/monitor/images/downtime.gif' border="0" alt=
										'<?php echo $label_schedule_downtime_hosts." ".$label_grouptype ?>' title=
										'<?php echo $label_schedule_downtime_hosts." ".$label_grouptype ?>' />
									</td>
									<td class='command'>
										<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_schedule_downtime_hosts.'&'.strtolower($label_grouptype).'='.$groupname, $label_schedule_downtime_hosts." ".$label_grouptype); ?>
									</td>
								</tr>
								<tr class='command'>
									<td>
										<img src='/monitor/images/downtime.gif' border="0" alt=
										'<?php echo $label_schedule_downtime_services." ".$label_grouptype ?>' title=
										'<?php echo $label_schedule_downtime_services." ".$label_grouptype ?>' />
									</td>
									<td class='command'>
										<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_schedule_downtime_services.'&'.strtolower($label_grouptype).'='.$groupname, $label_schedule_downtime_services." ".$label_grouptype); ?>
									</td>
								</tr>
								<tr class='command'>
									<td>
										<img src='/monitor/images/notify.gif' border="0" alt=
										'<?php echo $label_enable." ".$label_notifications_hosts." ".$label_grouptype ?>' title=
										'<?php echo $label_enable." ".$label_notifications_hosts." ".$label_grouptype ?>' />
									</td>
									<td class='command'>
										<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_enable_notifications_hosts.'&'.strtolower($label_grouptype).'='.$groupname, $label_enable." ".$label_notifications_hosts." ".$label_grouptype); ?>
									</td>
								</tr>
								<tr class='command'>
									<td>
										<img src='/monitor/images/ndisabled.gif' border="0" alt=
										'<?php echo $label_disable." ".$label_notifications_hosts." ".$label_grouptype ?>' title=
										'<?php echo $label_disable." ".$label_notifications_hosts." ".$label_grouptype ?>' />
									</td>
									<td class='command'>
										<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_disable_notifications_hosts.'&'.strtolower($label_grouptype).'='.$groupname, $label_disable." ".$label_notifications_hosts." ".$label_grouptype); ?>
									</td>
								</tr>
								<tr class='command'>
									<td>
										<img src='/monitor/images/notify.gif' border="0" alt=
										'<?php echo $label_enable." ".$label_notifications_services." ".$label_grouptype ?>' title=
										'<?php echo $label_enable." ".$label_notifications_services." ".$label_grouptype ?>' />
									</td>
									<td class='command'>
										<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_enable_notifications_services.'&'.strtolower($label_grouptype).'='.$groupname, $label_enable." ".$label_notifications_services." ".$label_grouptype); ?>
									</td>
								</tr>
								<tr class='command'>
									<td>
										<img src='/monitor/images/ndisabled.gif' border="0" alt=
										'<?php echo $label_disable." ".$label_notifications_services." ".$label_grouptype ?>' title=
										'<?php echo $label_disable." ".$label_notifications_services." ".$label_grouptype ?>' />
									</td>
									<td class='command'>
										<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_disable_notifications_services.'&'.strtolower($label_grouptype).'='.$groupname, $label_notifications_services." ".$label_grouptype); ?>
									</td>
								</tr>
								<tr class='command'>
									<td>
										<img src='/monitor/images/enabled.gif' border="0" alt=
										'<?php echo $label_enable." ".$label_active_checks." ".$label_grouptype ?>' title=
										'<?php echo $label_enable." ".$label_active_checks." ".$label_grouptype ?>' />
									</td>
									<td class='command'>
										<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_enable_active_checks.'&'.strtolower($label_grouptype).'='.$groupname, $label_enable." ".$label_active_checks." ".$label_grouptype); ?>
								</td>
								</tr>
									<tr class='command'>
									<td>
										<img src='/monitor/images/disabled.gif' border="0" alt=
										'<?php echo $label_disable." ".$label_active_checks." ".$label_grouptype ?>' title=
										'<?php echo $label_disable." ".$label_active_checks." ".$label_grouptype ?>' />
									</td>
									<td class='command'>
										<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_disable_active_checks.'&'.strtolower($label_grouptype).'='.$groupname, $label_disable." ".$label_active_checks." ".$label_grouptype); ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>

		</tr>
	</table>
</div>
