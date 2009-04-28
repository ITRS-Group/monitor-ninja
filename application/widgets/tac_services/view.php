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
				<th><?php echo $this->translate->_('Critical') ?></th>
				<th><?php echo $this->translate->_('Warning') ?></th>
				<th><?php echo $this->translate->_('Unknown') ?></th>
				<th><?php echo $this->translate->_('OK') ?></th>
				<th><?php echo $this->translate->_('Pending') ?></th>
			</tr>
			<tr>
				<td style="padding:0px;">
					<table>
						<tr>
							<?php if (count($services_critical) > 0) { foreach ($services_critical as $url => $title) { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/32x32/shield_critical.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							<?php } } else { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield-not-critical.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo $this->translate->_('N/A') ?></td>
							<?php } ?>
						</tr>
					</table>
				</td>
				<td style="padding:0px;">
					<table>
						<tr>
							<?php	if (count($services_warning) > 0) { foreach ($services_warning as $url => $title) { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/32x32/shield_warning.png',$this->translate->_('Warning')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							<?php } } else { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield-not-warning.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo $this->translate->_('N/A') ?></td>
							<?php } ?>
						</tr>
					</table>
				</td>
				<td style="padding:0px;">
					<table>
						<tr>
							<?php	if (count($services_unknown) > 0) { foreach ($services_unknown as $url => $title) { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield_unknown.png',$this->translate->_('Unknown')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							<?php } } else { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield-not-unknown.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo $this->translate->_('N/A') ?></td>
							<?php } ?>
						</tr>
					</table>
				</td>
				<td style="padding:0px;">
					<table>
						<tr>
							<?php	if ($current_status->services_ok > 0) { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield-ok.png',$this->translate->_('OK')) ?></td>
								<td><?php echo html::anchor('status/service/all/0/', html::specialchars($current_status->services_ok.' '.$this->translate->_('OK'))) ?></td>
							<?php }	if (count($services_ok_disabled) > 0) { foreach ($services_ok_disabled as $url => $title) { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							<?php } } if (count($services_ok_disabled) == 0 && $current_status->services_ok == 0) { echo '<td>'.$this->translate->_('N/A').'</td>'; } ?>
						</tr>
					</table>
				</td>
				<td style="padding:0px;">
					<table>
						<tr>
							<?php	if (count($services_pending_disabled) > 0) {	foreach ($services_pending_disabled as $url => $title) { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield_pending.png',$this->translate->_('Pending')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							<?php } } else { ?>
								<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield-not-pending.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo $this->translate->_('N/A') ?></td>
							<?php } ?>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>