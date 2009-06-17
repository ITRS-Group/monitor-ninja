<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm w98 left" id="widget-tac_hosts">
	<div class="widget-header"><span><?php echo $this->translate->_('Hosts') ?></span></div>
	<div class="widget-editbox"></div>
	<div class="widget-content">
		<table>
			<colgroup>
				<col style="width: 25%" />
				<col style="width: 25%" />
				<col style="width: 25%" />
				<col style="width: 25%" />
			</colgroup>
			<tr>
				<th><?php echo $this->translate->_('Down') ?></th>
				<th><?php echo $this->translate->_('Unreachable') ?></th>
				<th><?php echo $this->translate->_('Up') ?></th>
				<th><?php echo $this->translate->_('Pending') ?></th>
			</tr>
			<tr>
				<td class="white">
					<table>
							<?php if (count($hosts_down) > 0) { foreach ($hosts_down as $url => $title) { ?>
							<tr>
								<td class="dark">
								<?php
									$icon = explode(' ',$title);
									echo html::image('/application/views/themes/default/icons/16x16/'.(($icon[1] == 'Unhandled') ? 'shield-critical' : strtolower($icon[1])).'.png',$icon[1]);
								?>
								</td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-not-critical.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo html::anchor($default_links['down'], $this->translate->_('N/A')) ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td class="white">
					<table>
							<?php if (count($hosts_unreachable) > 0) { foreach ($hosts_unreachable as $url => $title) { ?>
							<tr>
								<td class="dark">
									<?php
										$icon = explode(' ',$title);
										echo html::image('/application/views/themes/default/icons/16x16/'.(($icon[1] == 'Unhandled') ? 'shield-unreachable' : strtolower($icon[1])).'.png',$icon[1]);
									?>
								</td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-not-unreachable.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo html::anchor($default_links['unreachable'], $this->translate->_('N/A')) ?></td>
								</tr>
							<?php } ?>
					</table>
				</td>
				<td class="white">
					<table>
							<?php	if ($current_status->hosts_up > 0) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-ok.png',$this->translate->_('Up')) ?></td>
								<td><?php echo html::anchor('status/host/all/0/', html::specialchars($current_status->hosts_up.' '.$this->translate->_('Up'))) ?></td>
							</tr>
							<?php } if (count($hosts_up_disabled) > 0) { foreach ($hosts_up_disabled as $url => $title) { ?>
								<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } if (count($hosts_up_disabled) == 0 && $current_status->hosts_up == 0) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-not-up.png',$this->translate->_('Up')) ?></td>
								<td><?php echo html::anchor($default_links['up'], $this->translate->_('N/A')) ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
				<td class="white">
					<table>
							<?php if (count($hosts_pending_disabled) > 0) { foreach ($hosts_pending_disabled as $url => $title) { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-pending.png',$this->translate->_('Pending')) ?></td>
								<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-not-pending.png',$this->translate->_('Critical')) ?></td>
								<td><?php echo html::anchor($default_links['pending'], $this->translate->_('N/A')) ?></td>
							</tr>
							<?php } ?>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>