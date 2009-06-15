<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm left w98" id="widget-tac_monfeat">
	<div class="widget-header"><span><?php echo $title ?></span></div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table>
			<colgroup>
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
			</colgroup>
			<tr>
				<th><?php echo $flap_detect_header_label ?></th>
				<th><?php echo $notifications_header_label ?></th>
				<th><?php echo $eventhandler_header_label ?></th>
				<th><?php echo $activechecks_header_label ?></th>
				<th><?php echo $passivechecks_header_label ?></th>
			</tr>
			<tr>
				<td class="white">
				<table>
					<!--<tr>
						<td rowspan="5" class="white"><?php echo $cmd_flap_link ?></td>
					</tr>-->
					<?php	if ($enable_flap_detection !== false) { ?>
						<?php if ($flap_disabled_services > 0) {?>
						<tr>
							<td>
								<?php echo $flap_disabled_services ?> <?php echo $flap_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?>
							</td>
						</tr>
						<?php } else { ?>
						<tr>
							<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
							<td><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td>
						</tr>
						<?php } if ($flapping_services > 0) {?>
						<tr>
							<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
							<td><?php echo $flapping_services ?> <?php echo $flapping_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_flapping ?></td>
						</tr>
						<?php } else { ?>
						<tr>
							<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
							<td><?php echo $lable_no_services ?> <?php echo $lable_flapping ?></td>
						</tr>
						<?php } if ($flap_disabled_hosts > 0) {?>
						<tr>
							<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
							<td><?php echo $flap_disabled_hosts ?> <?php echo $flap_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td>
						</tr>
						<?php } else { ?>
						<tr>
							<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
							<td><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td>
						</tr>
						<?php } if ($flapping_hosts > 0) {?>
						<tr>
							<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
							<td><?php echo $flapping_hosts ?> <?php echo $flapping_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_flapping ?></td>
						</tr>
						<?php } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
								<td><?php echo $lable_no_hosts ?> <?php echo $lable_flapping ?></td>
							</tr>
						<?php } } else { ?>
							<tr>
								<td><?php echo $na_str ?></td>
							</tr>
						<?php	} ?>
					</table>
				</td>
				<td class="white">
					<table>
						<!--<tr>
							<td rowspan="5" class="white"><?php echo $cmd_notification_link ?></td>
						</tr>-->
							<?php	if ($enable_notifications !== false) { ?>
									<?php if ($notification_disabled_services > 0) { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png','') ?></td>
										<td><?php echo $notification_disabled_services ?> <?php echo $notification_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?></td>
									</tr>
									<?php	} else { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
										<td><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td>
									</tr>
									<?php	} ?>
									<?php if ($notification_disabled_hosts > 0) { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png','') ?></td>
										<td><?php echo $notification_disabled_hosts ?> <?php echo $notification_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td>
									</tr>
									<?php	} else { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
										<td><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td>
									</tr>
									<?php	} ?>

							<?php	} else { ?>
								<tr><td><?php echo $na_str ?></td></tr>
							<?php	} ?>
					</table>
				</td>
				<td class="white">
					<table>
						<!--<tr>
							<td class="white" rowspan="3"><?php echo $cmd_event_link ?></td>
						</tr>-->
						<?php	if ($enable_event_handlers !== false) { ?>
								<?php if ($event_handler_disabled_services > 0) { ?>
								<tr>
									<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
									<td><?php echo $event_handler_disabled_services ?> <?php echo $event_handler_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?></td>
								</tr>
								<?php } else { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
										<td><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td>
									</tr>
								<?php } ?>
								<?php if ($event_handler_disabled_hosts > 0) { ?>
								<tr>
									<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
									<td><?php echo $event_handler_disabled_hosts ?> <?php echo $event_handler_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td>
								</tr>
								<?php } else { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
										<td><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td>
									</tr>
								<?php } ?>
						<?php	} else { ?>
							<tr><td><?php echo $na_str ?></td></tr>
						<?php	} ?>
					</table>
				</td>

				<td class="white">
					<table>
						<!--<tr>
							<td class="white" rowspan="3"><?php echo $cmd_activecheck_link ?></td>
						</tr>-->
						<?php	if ($execute_service_checks !== false) { ?>
							<?php if ($active_checks_disabled_services > 0) { ?>
								<tr>
									<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
									<td><?php echo $active_checks_disabled_services ?> <?php echo $active_checks_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?></td>
								</tr>
							<?php } else { ?>
								<tr>
									<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
									<td><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td>
								</tr>
							<?php } ?>
							<?php if ($active_checks_disabled_hosts > 0) { ?>
								<tr>
									<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
									<td><?php echo $active_checks_disabled_hosts ?> <?php echo $active_checks_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td>
								</tr>
							<?php } else { ?>
								<tr>
									<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
									<td><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td>
								</tr>
							<?php } ?>
						<?php	} else { ?>
							<tr><td><?php echo $na_str ?></td></tr>
						<?php	} ?>
					</table>
				</td>
				<td class="white">
					<table>
						<!--<tr>
							<td class="white" rowspan="3"><?php echo $cmd_passivecheck_link ?></td>
						</tr>-->
							<?php	if ($accept_passive_service_checks !== false) { ?>
								<?php if ($passive_checks_disabled_services > 0) { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
										<td><?php echo $passive_checks_disabled_services ?> <?php echo $passive_checks_disabled_services==1 ? $lable_service_singular : $lable_service_plural ?> <?php echo $lable_disabled ?></td>
									</tr>
								<?php } else { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
										<td><?php echo $lable_all_services ?> <?php echo $lable_enabled ?></td>
									</tr>
								<?php } ?>
								<?php if ($passive_checks_disabled_hosts > 0) { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
										<td><?php echo $passive_checks_disabled_hosts ?> <?php echo $passive_checks_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural ?> <?php echo $lable_disabled ?></td>
									</tr>
								<?php } else { ?>
									<tr>
										<td class="dark"><?php echo html::image('/application/views/themes/default/icons/12x12/shield-ok.png',$this->translate->_('Enabled')) ?></td>
										<td><?php echo $lable_all_hosts ?> <?php echo $lable_enabled ?></td>
									</tr>
								<?php } ?>
						<?php	} else { ?>
							<tr><td><?php echo $na_str ?></td></tr>
						<?php	} ?>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>