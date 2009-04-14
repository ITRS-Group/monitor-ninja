<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="top" class='commandPanel'>
            <div class='commandTitle'>
                <?php echo $lable_command_title ?>
            </div>
            <table border='1' cellpadding="0" cellspacing="0">
                <tr>
                    <td>
						<table border="0" cellspacing="0" cellpadding="0" class='command'>
							<?php # only for hosts!
								if ($type == 'host') {
								# @@@FIXME check if we are using statusmap? USE_STATUSMAP
							?>
							<tr class='command'>
								<td>
									<img src='/monitor/images/status3.gif' border="0" alt='<?php echo $lable_host_map ?>' title='<?php echo $lable_host_map ?>' />
								</td>
								<td class='command'>
									<a href='statusmap/host/<?php echo $host ?>'><?php echo $lable_host_map ?></a>
								</td>
							</tr>
						<?php 	} ?>

							<tr class='command'>
								<td>
									<img src='/monitor/images/disabled.gif' border="0" alt='<?php echo $lable_active_checks ?>'
										title='<?php echo $lable_active_checks ?>' />
								</td>
								<td class='command'>
									<?php echo $link_active_checks ?>
								</td>
							</tr>
							<tr class='data'>
								<td>
									<img src='/monitor/images/delay.gif' border="0" alt='<?php echo $lable_reschedule_check ?>' title='<?php echo $lable_reschedule_check ?>' />
								</td>
								<td class='command'>
									<?php echo $link_reschedule_check ?>
								</td>
							</tr>
							<tr class='command'>
								<td>
									<img src='/monitor/images/passiveonly.gif' border="0" alt='<?php echo $lable_submit_passive_checks ?>'
										title='<?php echo $lable_submit_passive_checks ?>' />
								</td>
								<td class='command'>
									<?php echo $link_submit_passive_check ?>
								</td>
							</tr>
							<tr class='command'>
								<td>
									<img src='/monitor/images/disabled.gif' border="0" alt='<?php echo $lable_stop_start_passive_checks ?>'
										title='<?php echo $lable_stop_start_passive_checks ?>' />
								</td>
								<td class='command'>
									<?php echo $link_stop_start_passive_check ?>
								</td>
							</tr>
							<tr class='command'>
								<td>
									<img src='/monitor/images/disabled.gif' border="0" alt='<?php echo $lable_obsessing ?>'
										title='<?php echo $lable_obsessing ?>' />
								</td>
								<td class='command'>
									<?php echo $link_obsessing ?>
								</td>
							</tr>
							<?php if ($show_ackinfo) { ?>
							<tr class='command'>
								<td>
									<img src='/monitor/images/ack.gif' border=0 alt='<?php echo $lable_acknowledge_problem ?>'
										title='<?php echo $lable_acknowledge_problem ?>'>
								</td>
								<td class='command'>
									<?php echo $link_acknowledge_problem ?>
								</td>
							</tr>
							<?php } ?>
							<tr class='command'>
								<td>
									<img src='/monitor/images/disabled.gif' border="0" alt='<?php echo $lable_notifications ?>'
										title='<?php echo $lable_notifications ?>' />
								</td>
								<td class='command'>
									<?php echo $link_notifications ?>
								</td>
							</tr>
							<tr class='command'>
								<td>
									<img src='/monitor/images/notify.gif' border="0" alt='<?php echo $lable_custom_notifications ?>'
										title='<?php echo $lable_custom_notifications ?>' />
								</td>
								<td class='command'>
									<?php echo $link_custom_notifications ?>
								</td>
							</tr>
					<?php 	if ($show_delay) {	?>
							<tr class='command'>
								<td>
									<img src="/monitor/images/delay.gif" border=0 alt="<?php echo $lable_delay_notification ?>"
										title="<?php echo $lable_delay_notification ?>">
								</td>
								<td class='command'>
									<?php echo $link_delay_notifications ?>
								</td>
							</tr>
					<?php 	} ?>
							<tr class='command'>
								<td>
									<img src='/monitor/images/downtime.gif' border="0" alt='<?php echo $lable_schedule_dt ?>'
										title='<?php echo $lable_schedule_dt ?>' />
								</td>
								<td class='command'>
									<?php echo $link_schedule_dt ?>
								</td>
							</tr>
					<?php 	if ($type == 'host') {?>
							<tr class='command'>
								<td>
									<img src='/monitor/images/disabled.gif' border="0" alt='<?php echo $lable_disable_service_notifications_on_host ?>'
										title='<?php echo $lable_disable_service_notifications_on_host ?>' />
								</td>
								<td class='command' nowrap="nowrap">
									<?php echo $link_disable_service_notifications_on_host ?>
								</td>
							</tr>
							<tr class='command'>
								<td>
									<img src='/monitor/images/enabled.gif' border="0" alt='<?php echo $lable_enable_service_notifications_on_host ?>'
										title='<?php echo $lable_enable_service_notifications_on_host ?>' />
								</td>
								<td class='command'>
									<?php echo $link_enable_service_notifications_on_host ?>
								</td>
							</tr>
							<tr class='command'>
								<td>
									<img src='/monitor/images/delay.gif' border="0" alt='<?php echo $lable_check_all_services ?>'
										title='<?php echo $lable_check_all_services ?>' />
								</td>
								<td class='command'>
									<?php echo $link_check_all_services ?>
								</td>
							</tr>
							<tr class='command'>
								<td>
									<img src='/monitor/images/disabled.gif' border="0" alt='<?php echo $lable_disable_servicechecks ?>'
										title='<?php echo $lable_disable_servicechecks ?>' />
								</td>
								<td class='command'>
									<?php echo $link_disable_servicechecks ?>
								</td>
							</tr>
							<tr class='command'>
								<td>
									<img src='/monitor/images/enabled.gif' border="0" alt='<?php echo $lable_enable_servicechecks ?>'
										title='<?php echo $lable_enable_servicechecks ?>' />
								</td>
								<td class='command'>
									<?php echo $link_enable_servicechecks ?>
								</td>
							</tr>
							<?php } ?>
							<tr class='command'>
								<td>
									<img src='/monitor/images/disabled.gif' border="0" alt='<?php echo $lable_enable_disable_event_handler ?>'
										title='<?php echo $lable_enable_disable_event_handler ?>' />
								</td>
								<td class='command'>
									<?php echo $link_enable_disable_event_handler ?>
								</td>
							</tr>
							<tr class='command'>
								<td>
									<img src='/monitor/images/disabled.gif' border="0" alt='<?php echo $lable_enable_disable_flapdetection ?>'
										title='<?php echo $lable_enable_disable_flapdetection ?>' />
								</td>
							<td class='command'>
								<?php echo $link_enable_disable_flapdetection ?>
							</td>
							</tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
