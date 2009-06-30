<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm" id="widget-<?php echo $widget_id ?>">
<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox" style="background-color: #ffffff; padding: 15px; float: right; margin-top: -1px; border: 1px solid #e9e9e0; right: 0px; width: 200px">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider" style="z-index:1000"></div>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
<?php } ?>
		<table>
			<colgroup>
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
			</colgroup>
			<tr>
				<th onclick="location.href='<?php echo $cmd_flap_link ?>'" class="<?php echo $cmd_flap_status ?>"><cite><?php echo $flap_detect_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_flap_status) ?></em></th>
				<th onclick="location.href='<?php echo $cmd_notification_link ?>'" class="<?php echo $cmd_notification_status ?>"><cite><?php echo $notifications_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_notification_status) ?></em></th>
				<th onclick="location.href='<?php echo $cmd_event_link ?>'" class="<?php echo $cmd_event_status ?>"><cite><?php echo $eventhandler_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_event_status) ?></em></th>
				<th onclick="location.href='<?php echo $cmd_activecheck_link ?>'" class="<?php echo $cmd_activecheck_status ?>"><cite><?php echo $activechecks_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_activecheck_status) ?></em></th>
				<th onclick="location.href='<?php echo $cmd_passivecheck_link ?>'" class="<?php echo $cmd_passivecheck_status ?>"><cite><?php echo $passivechecks_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_passivecheck_status) ?></em></th>
			</tr>
			<tr>
				<td class="white">
				<table>
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
									<td style="padding: 6.5px"><?php echo $na_str ?></td>
								</tr>
						<?php	} ?>
					</table>
				</td>
				<td class="white">
					<table>
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
								<tr>
									<td style="padding: 6.5px"><?php echo $na_str ?></td>
								</tr>
							<?php	} ?>
					</table>
				</td>
				<td class="white">
					<table>
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
							<tr>
								<td style="padding: 6.5px"><?php echo $na_str ?></td>
							</tr>
						<?php	} ?>
					</table>
				</td>

				<td class="white">
					<table>
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
							<tr>
								<td style="padding: 6.5px"><?php echo $na_str ?></td>
							</tr>
						<?php	} ?>
					</table>
				</td>
				<td class="white">
					<table>
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
							<tr>
								<td style="padding: 6.5px"><?php echo $na_str ?></td>
							</tr>
						<?php	} ?>
					</table>
				</td>
			</tr>
		</table>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<?php } ?>