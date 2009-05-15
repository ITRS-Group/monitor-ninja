<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm w98 left" id="widget-tac_services">
	<div class="widget-header"><?php echo $title ?></div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table summary="<?php echo $title; ?>">
			<colgroup>
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
				<col style="width: 20%" />
			</colgroup>
			<tr>
				<th style="border-left: 1px solid #e9e9e0"><?php echo $this->translate->_('Critical') ?></th>
				<th style="border-left: 1px solid #e9e9e0"><?php echo $this->translate->_('Warning') ?></th>
				<th style="border-left: 1px solid #e9e9e0"><?php echo $this->translate->_('Unknown') ?></th>
				<th style="border-left: 1px solid #e9e9e0"><?php echo $this->translate->_('OK') ?></th>
				<th style="border-left: 1px solid #e9e9e0; border-right: 1px solid #e9e9e0"><?php echo $this->translate->_('Pending') ?></th>
			</tr>
			<tr>
				<td style="padding:0px;" class="white">
					<table style="border-spacing: 1px; background-color: #e9e9e0; margin-top: -1px">
							<?php if (count($services_critical) > 0) { foreach ($services_critical as $url => $title) { ?>
							<tr>
								<td class="dark">
									<?php
										$icon = explode(' ',$title);
										echo html::image('/application/views/themes/default/images/icons/16x16/'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-critical' : strtolower($icon[1])).'.png',$icon[1]);
									?>
								</td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-not-critical.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo $this->translate->_('N/A') ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td style="padding:0px;" class="white">
					<table style="border-spacing: 1px; background-color: #e9e9e0; margin-top: -1px">
							<?php	if (count($services_warning) > 0) { foreach ($services_warning as $url => $title) { ?>
							<tr>
								<td class="dark">
									<?php
										$icon = explode(' ',$title);
										echo html::image('/application/views/themes/default/images/icons/16x16/'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-warning' : strtolower($icon[1])).'.png',$icon[1]);
									?>
								</td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-not-warning.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo $this->translate->_('N/A') ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td style="padding:0px;" class="white">
					<table style="border-spacing: 1px; background-color: #e9e9e0; margin-top: -1px">
							<?php	if (count($services_unknown) > 0) { foreach ($services_unknown as $url => $title) { ?>
							<tr>
								<td class="dark">
									<?php
										$icon = explode(' ',$title);
										echo html::image('/application/views/themes/default/images/icons/16x16/'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-unknown' : strtolower($icon[1])).'.png',$icon[1]);
									?>
								</td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-not-unknown.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo $this->translate->_('N/A') ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td style="padding:0px;" class="white">
					<table style="border-spacing: 1px; background-color: #e9e9e0; margin-top: -1px">
							<?php	if ($current_status->services_ok > 0) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-ok.png',$this->translate->_('OK')) ?></td>
								<td><?php echo html::anchor('status/service/all/0/', html::specialchars($current_status->services_ok.' '.$this->translate->_('OK'))) ?></td>
							</tr>
							<?php }	if (count($services_ok_disabled) > 0) { foreach ($services_ok_disabled as $url => $title) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } if (count($services_ok_disabled) == 0 && $current_status->services_ok == 0) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-not-ok.png',$this->translate->_('OK')) ?></td>
								<td><?php echo $this->translate->_('N/A') ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td style="padding:0px;" class="white">
					<table style="border-spacing: 1px; background-color: #e9e9e0; margin-top: -1px">
							<?php	if (count($services_pending_disabled) > 0) {	foreach ($services_pending_disabled as $url => $title) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-pending.png',$this->translate->_('Pending')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-not-pending.png',$this->translate->_('Not pending')) ?></td>
								<td><?php echo $this->translate->_('N/A') ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>