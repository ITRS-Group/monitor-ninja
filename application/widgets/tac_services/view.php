<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm w98 left" id="widget-<?php echo $widget_id ?>">
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
		<table summary="<?php echo $title; ?>">
			<colgroup>
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
			</colgroup>
			<tr>
				<th><?php echo $this->translate->_('Critical') ?></th>
				<th><?php echo $this->translate->_('Warning') ?></th>
				<th><?php echo $this->translate->_('Unknown') ?></th>
				<th><?php echo $this->translate->_('OK') ?></th>
				<th><?php echo $this->translate->_('Pending') ?></th>
			</tr>
			<tr>
				<td style="padding:0px;" class="white">
					<table>
							<?php if (count($services_critical) > 0) { foreach ($services_critical as $url => $title) { ?>
							<tr>
								<td class="dark">
									<?php
										$icon = explode(' ',$title);
										echo html::image('/application/views/themes/default/icons/16x16/'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-critical' : strtolower($icon[1])).'.png',$icon[1]);
									?>
								</td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-not-critical.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo html::anchor($default_links['critical'], $this->translate->_('N/A')) ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td style="padding:0px;" class="white">
					<table>
							<?php	if (count($services_warning) > 0) { foreach ($services_warning as $url => $title) { ?>
							<tr>
								<td class="dark">
									<?php
										$icon = explode(' ',$title);
										echo html::image('/application/views/themes/default/icons/16x16/'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-warning' : strtolower($icon[1])).'.png',$icon[1]);
									?>
								</td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-not-warning.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo html::anchor($default_links['warning'], $this->translate->_('N/A') )?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td style="padding:0px;" class="white">
					<table>
							<?php	if (count($services_unknown) > 0) { foreach ($services_unknown as $url => $title) { ?>
							<tr>
								<td class="dark">
									<?php
										$icon = explode(' ',$title);
										echo html::image('/application/views/themes/default/icons/16x16/'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-unknown' : strtolower($icon[1])).'.png',$icon[1]);
									?>
								</td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-not-unknown.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo html::anchor($default_links['unknown'], $this->translate->_('N/A')) ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td style="padding:0px;" class="white">
					<table>
							<?php	if ($current_status->services_ok > 0) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-ok.png',$this->translate->_('OK')) ?></td>
								<td><?php echo html::anchor('status/service/all/0/', html::specialchars($current_status->services_ok.' '.$this->translate->_('OK'))) ?></td>
							</tr>
							<?php }	if (count($services_ok_disabled) > 0) { foreach ($services_ok_disabled as $url => $title) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } if (count($services_ok_disabled) == 0 && $current_status->services_ok == 0) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-not-ok.png',$this->translate->_('OK')) ?></td>
								<td><?php echo html::anchor($default_links['ok'], $this->translate->_('N/A')) ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td style="padding:0px;" class="white">
					<table>
							<?php	if (count($services_pending_disabled) > 0) {	foreach ($services_pending_disabled as $url => $title) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-pending.png',$this->translate->_('Pending')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-not-pending.png',$this->translate->_('Not pending')) ?></td>
								<td><?php echo html::anchor($default_links['pending'], $this->translate->_('N/A')) ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
			</tr>
		</table>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<?php } ?>