<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm" id="widget-tac_monfeat">
	<div class="widget-header">
		<strong><?php echo $title ?></strong>
	</div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table class='tac' width=516 cellspacing=0 cellpadding=0 border=1>
			<tr>
				<td class="featureHeader" width=135><?php echo $flap_detect_header_label ?></td>
				<td class="featureHeader" width=135><?php echo $notifications_header_label ?></td>
				<td class="featureHeader" width=135><?php echo $eventhandler_header_label ?></td>
				<td class="featureHeader" width=135><?php echo $activechecks_header_label ?></td>
				<td class="featureHeader" width=135><?php echo $passivechecks_header_label ?></td>
			</tr>
			<tr>
				<td valign=top>
					<table border=0 width=135 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=top>
								<?php echo $cmd_flap_link ?>
							</td>
							<Td width=10>&nbsp;</td>
							<?php	if ($enable_flap_detection !== false) { ?>
							<Td valign=top width=100% class='featureEnabledFlapDetection'>
								<table border=0 width=100%>
								<?php if ($flap_disabled_services > 0) {?>
									<tr>
										<td width=100% class='featureItemDisabledServiceFlapDetection'>
											<?php echo $flap_disabled_services ?> <?php echo $flap_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?>
										</td>
									</tr>
									<?php } else { ?>
									<tr><td width=100% class='featureItemEnabledServiceFlapDetection'><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td></tr>
									<?php } ?>
									<?php if ($flapping_services > 0) {?>
									<tr><td width=100% class='featureItemServicesFlapping'><?php echo $flapping_services ?> <?php echo $flapping_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_flapping ?></td></tr>
									<?php } else { ?>
									<tr><td width=100% class='featureItemServicesNotFlapping'><?php echo $lable_no_services ?> <?php echo $lable_flapping ?></td></tr>
									<?php } ?>
									<?php if ($flap_disabled_hosts > 0) {?>
									<tr><td width=100% class='featureItemDisabledHostFlapDetection'><?php echo $flap_disabled_hosts ?> <?php echo $flap_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td></tr>
									<?php } else { ?>
									<tr><td width=100% class='featureItemEnabledHostFlapDetection'><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td></tr>
									<?php } ?>
									<?php if ($flapping_hosts > 0) {?>
									<tr><td width=100% class='featureItemHostsFlapping'><?php echo $flapping_hosts ?> <?php echo $flapping_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_flapping ?></td></tr>
									<?php } else { ?>
									<tr><td width=100% class='featureItemHostsNotFlapping'><?php echo $lable_no_hosts ?> <?php echo $lable_flapping ?></td></tr>
									<?php } ?>
								</table>
							</td>
						<?php	} else { ?>
							<td valign=center width=100%% class='featureDisabledFlapDetection'><?php echo $na_str ?></td>
						<?php	} ?>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border=0 width=135 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=top>
								<?php echo $cmd_notification_link ?>
							</td>
							<Td width=10>&nbsp;</td>
							<?php	if ($enable_notifications !== false) { ?>
							<Td valign=top width=100% class='featureEnabledNotifications'>
								<table border=0 width=100%>
									<?php if ($notification_disabled_services > 0) { ?>
									<tr><td width=100% class='featureItemDisabledServiceNotifications'><?php echo $notification_disabled_services ?> <?php echo $notification_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?></td></tr>
									<?php	} else { ?>
									<tr><td width=100% class='featureItemEnabledHostNotifications'><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td></tr>
									<?php	} ?>
									<?php if ($notification_disabled_hosts > 0) { ?>
									<tr><td width=100% class='featureItemDisabledHostNotifications'><?php echo $notification_disabled_hosts ?> <?php echo $notification_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td></tr>
									<?php	} else { ?>
									<tr><td width=100% class='featureItemEnabledHostNotifications'><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td></tr>
									<?php	} ?>
								</table>
							</td>
							<?php	} else { ?>
							<td valign=center width=100%% class='featureDisabledNotifications'><?php echo $na_str ?></td>
							<?php	} ?>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border=0 width=135 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=top>
								<?php echo $cmd_event_link ?>
							</td>
							<Td width=10>&nbsp;</td>
							<?php	if ($enable_event_handlers !== false) { ?>
							<Td valign=top width=100% class='featureEnabledHandlers'>
								<table border=0 width=100%>
								<?php if ($event_handler_disabled_services > 0) { ?>
								<tr><td width=100% class='featureItemDisabledServiceHandlers'><?php echo $event_handler_disabled_services ?> <?php echo $event_handler_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?></td></tr>
								<?php } else { ?>
									<tr><td width=100% class='featureItemEnabledServiceHandlers'><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td></tr>
								<?php } ?>
								<?php if ($event_handler_disabled_hosts > 0) { ?>
								<tr><td width=100% class='featureItemDisabledHostHandlers'><?php echo $event_handler_disabled_hosts ?> <?php echo $event_handler_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td></tr>
								<?php } else { ?>
									<tr><td width=100% class='featureItemEnabledHostHandlers'><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td></tr>
								<?php } ?>
								</table>
							</td>
						<?php	} else { ?>
							<td valign=center width=100%% class='featureDisabledHandlers'><?php echo $na_str ?></td>
						<?php	} ?>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border=0 width=135 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=top>
								<?php echo $cmd_activecheck_link ?>
							</td>
							<Td width=10>&nbsp;</td>
							<?php	if ($execute_service_checks !== false) { ?>
							<Td valign=top width=100% class='featureEnabledActiveChecks'>
								<table border=0 width=100%>
								<?php if ($active_checks_disabled_services > 0) { ?>
									<tr><td width=100% class='featureItemDisabledActiveServiceChecks'><?php echo $active_checks_disabled_services ?> <?php echo $active_checks_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?></td></tr>
								<?php } else { ?>
									<tr><td width=100% class='featureItemEnabledActiveServiceChecks'><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td></tr>
								<?php } ?>
								<?php if ($active_checks_disabled_hosts > 0) { ?>
									<tr><td width=100% class='featureItemDisabledActiveHostChecks'><?php echo $active_checks_disabled_hosts ?> <?php echo $active_checks_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td></tr>
								<?php } else { ?>
									<tr><td width=100% class='featureItemEnabledActiveHostChecks'><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td></tr>
								<?php } ?>
								</table>
							</td>
						<?php	} else { ?>
							<td valign=center width=100%% class='featureDisabledActiveChecks'><?php echo $na_str ?></td>
						<?php	} ?>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border=0 width=135 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=top>
								<?php echo $cmd_passivecheck_link ?>
							</td>
							<Td width=10>&nbsp;</td>
							<?php	if ($accept_passive_service_checks !== false) { ?>
							<Td valign=top width=100% class='featureEnabledPassiveChecks'>
								<table border=0 width=100%>
								<?php if ($passive_checks_disabled_services > 0) { ?>
									<tr><td width=100% class='featureItemDisabledPassiveServiceChecks'><?php echo $passive_checks_disabled_services ?> <?php echo $passive_checks_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?></td></tr>
								<?php } else { ?>
									<tr><td width=100% class='featureItemEnabledPassiveServiceChecks'><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td></tr>
								<?php } ?>
								<?php if ($passive_checks_disabled_hosts > 0) { ?>
									<tr><td width=100% class='featureItemDisabledPassiveHostChecks'><?php echo $passive_checks_disabled_hosts ?> <?php echo $passive_checks_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td></tr>
								<?php } else { ?>
									<tr><td width=100% class='featureItemEnabledPassiveHostChecks'><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td></tr>
								<?php } ?>
								</table>
							</td>
						<?php	} else { ?>
							<td valign=center width=100%% class='featureDisabledPassiveChecks'><?php echo $na_str ?></td>
						<?php	} ?>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>

