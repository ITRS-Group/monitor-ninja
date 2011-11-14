<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm" id="widget-<?php echo $widget_id ?>">
<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<fieldset>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider"></div>
		</fieldset>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
<?php } ?>
		<table id="mmm">
			<colgroup>
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
			</colgroup>
			<tr>
				<th onclick="location.href='<?php echo $cmd_flap_link ?>'" class="<?php echo $cmd_flap_status ?>"><cite><?php echo $flap_detect_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_flap_status) ?> &nbsp;&nbsp;</em></th>
				<th onclick="location.href='<?php echo $cmd_notification_link ?>'" class="<?php echo $cmd_notification_status ?>"><cite><?php echo $notifications_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_notification_status) ?> &nbsp;&nbsp;</em></th>
				<th onclick="location.href='<?php echo $cmd_event_link ?>'" class="<?php echo $cmd_event_status ?>"><cite><?php echo $eventhandler_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_event_status) ?> &nbsp;&nbsp;</em></th>
				<th onclick="location.href='<?php echo $cmd_activecheck_link ?>'" class="<?php echo $cmd_activecheck_status ?>"><cite><?php echo $activechecks_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_activecheck_status) ?> &nbsp;&nbsp;</em></th>
				<th onclick="location.href='<?php echo $cmd_passivecheck_link ?>'" class="<?php echo $cmd_passivecheck_status ?>"><cite><?php echo $passivechecks_header_label ?></cite> <em><?php echo str_replace('_monfeat','',$cmd_passivecheck_status) ?> &nbsp;&nbsp;</em></th>
			</tr>
			<tr>
				<td class="white">
				<table>
					<?php	if ($enable_flap_detection) { ?>
						<?php if ($flap_disabled_services > 0) {?>
						<tr>
							<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
							<td style="white-space: normal">
								<?php echo $flap_disabled_services.' '.($flap_disabled_services==1 ? $lable_service_singular : $lable_service_plural).' '.$lable_disabled ?>
							</td>
						</tr>
						<?php } else { ?>
						<tr>
							<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
							<td style="white-space: normal"><?php echo $lable_all_services.' '.$lable_enabled ?></td>
						</tr>
						<?php } if ($flapping_services > 0) {?>
						<tr>
							<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
							<td style="white-space: normal"><?php echo html::anchor('/status/service/?service_props='.nagstat::SERVICE_IS_FLAPPING  ,$flapping_services.' '.($flapping_services==1 ? $lable_service_singular : $lable_service_plural).' '.$lable_flapping) ?></td>
						</tr>
						<?php } else { ?>
						<tr>
							<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
							<td style="white-space: normal"><?php echo $lable_no_services.' '.$lable_flapping ?></td>
						</tr>
						<?php } if ($flap_disabled_hosts > 0) {?>
						<tr>
							<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
							<td style="white-space: normal"><?php echo $flap_disabled_hosts.' '.($flap_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural).' '.$lable_disabled ?></td>
						</tr>
						<?php } else { ?>
						<tr>
							<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
							<td style="white-space: normal"><?php echo $lable_all_hosts.' '.$lable_enabled ?></td>
						</tr>
						<?php } if ($flapping_hosts > 0) {?>
						<tr>
							<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
							<td style="white-space: normal"><?php echo html::anchor('/status/host/?hostprops='.nagstat::HOST_IS_FLAPPING ,$flapping_hosts.' '.($flapping_hosts==1 ? $lable_host_singular : $lable_host_plural).' '.$lable_flapping) ?></td>
						</tr>
						<?php } else { ?>
							<tr>
								<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
								<td style="white-space: normal"><?php echo $lable_no_hosts.' '.$lable_flapping ?></td>
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
						<?php	if ($enable_notifications) { ?>
							<?php if ($notification_disabled_services > 0) { ?>
							<tr>
								<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),'') ?></td>
								<td style="white-space: normal"><?php echo html::anchor('/status/service/?service_props='.nagstat::SERVICE_NOTIFICATIONS_DISABLED, $notification_disabled_services.' '.($notification_disabled_services==1 ? $lable_service_singular : $lable_service_plural).' '.$lable_disabled) ?></td>
							</tr>
							<?php	} else { ?>
							<tr>
								<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
								<td style="white-space: normal"><?php echo $lable_all_services.' '.$lable_enabled ?></td>
							</tr>
							<?php	} ?>
							<?php if ($notification_disabled_hosts > 0) { ?>
							<tr>
								<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),'') ?></td>
								<td style="white-space: normal"><?php echo html::anchor('/status/host/?hostprops='.nagstat::HOST_NOTIFICATIONS_DISABLED, $notification_disabled_hosts.' '.($notification_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural).' '.$lable_disabled) ?></td>
							</tr>
							<?php	} else { ?>
							<tr>
								<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
								<td style="white-space: normal"><?php echo $lable_all_hosts.' '.$lable_enabled ?></td>
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
						<?php	if ($enable_event_handlers) { ?>
								<?php if ($event_handler_disabled_svcs > 0) { ?>
								<tr>
									<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
									<td style="white-space: normal"><?php echo html::anchor('/status/service/?service_props='.nagstat::SERVICE_EVENT_HANDLER_DISABLED, $event_handler_disabled_svcs.' '.($event_handler_disabled_svcs==1 ? $lable_service_singular : $lable_service_plural).' '.$lable_disabled) ?></td>
								</tr>
								<?php } else { ?>
									<tr>
										<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
										<td style="white-space: normal"><?php echo $lable_all_services.' '.$lable_enabled ?></td>
									</tr>
								<?php } ?>
								<?php if ($event_handler_disabled_hosts > 0) { ?>
								<tr>
									<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
									<td style="white-space: normal"><?php echo html::anchor('/status/host/?hostprops='.nagstat::HOST_EVENT_HANDLER_DISABLED, $event_handler_disabled_hosts.' '.($event_handler_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural).' '.$lable_disabled) ?></td>
								</tr>
								<?php } else { ?>
									<tr>
										<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
										<td style="white-space: normal"><?php echo $lable_all_hosts.' '.$lable_enabled ?></td>
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
						<?php	if ($execute_service_checks) { ?>
							<?php if ($active_checks_disabled_svcs > 0) { ?>
								<tr>
									<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
									<td style="white-space: normal"><?php echo html::anchor('/status/service/?service_props='.nagstat::SERVICE_CHECKS_DISABLED, $active_checks_disabled_svcs.' '.($active_checks_disabled_svcs==1 ? $lable_service_singular : $lable_service_plural).' '.$lable_disabled) ?></td>
								</tr>
							<?php } else { ?>
								<tr>
									<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
									<td style="white-space: normal"><?php echo $lable_all_services.' '.$lable_enabled ?></td>
								</tr>
							<?php } ?>
							<?php if ($active_checks_disabled_hosts > 0) { ?>
								<tr>
									<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
									<td style="white-space: normal"><?php echo html::anchor('/status/host/?hostprops='.nagstat::HOST_CHECKS_DISABLED, $active_checks_disabled_hosts.' '.($active_checks_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural).' '.$lable_disabled) ?></td>
								</tr>
							<?php } else { ?>
								<tr>
									<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
									<td style="white-space: normal"><?php echo $lable_all_hosts.' '.$lable_enabled ?></td>
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
							<?php	if ($accept_passive_service_checks) { ?>
								<?php if ($passive_checks_disabled_svcs > 0) { ?>
									<tr>
										<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
										<td style="white-space: normal"><?php echo html::anchor('/status/service/?service_props='.nagstat::SERVICE_PASSIVE_CHECKS_DISABLED, $passive_checks_disabled_svcs.' '.($passive_checks_disabled_svcs==1 ? $lable_service_singular : $lable_service_plural).' '.$lable_disabled) ?></td>
									</tr>
								<?php } else { ?>
									<tr>
										<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
										<td style="white-space: normal"><?php echo $lable_all_services.' '.$lable_enabled ?></td>
									</tr>
								<?php } ?>
								<?php if ($passive_checks_disabled_hosts > 0) { ?>
									<tr>
										<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-disabled.png'),$this->translate->_('Disabled')) ?></td>
										<td style="white-space: normal"><?php echo html::anchor('/status/host/?hostprops='.nagstat::HOST_PASSIVE_CHECKS_DISABLED, $passive_checks_disabled_hosts.' '.($passive_checks_disabled_hosts==1 ? $lable_host_singular : $lable_host_plural).' '.$lable_disabled) ?></td>
									</tr>
								<?php } else { ?>
									<tr>
										<td class="dark"><?php echo html::image($this->add_path('icons/12x12/shield-ok.png'),$this->translate->_('Enabled')) ?></td>
										<td style="white-space: normal"><?php echo $lable_all_hosts.' '.$lable_enabled ?></td>
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
