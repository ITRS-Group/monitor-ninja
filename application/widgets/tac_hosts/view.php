<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm w98 left" id="widget-tac_hosts">
	<div class="widget-header"><?php echo $this->translate->_('Host overview') ?></div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table>
			<tr>
			<?php	foreach ($header_links as $url => $title) { ?>
				<td colspan="2"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
			<?php	} ?>
			</tr>
			<tr>
				<?php foreach ($hosts_down as $url => $title) { ?>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/32x32/shield_critical.png',$this->translate->_('Critical')) ?></td>
					<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
				<?php	} foreach ($hosts_unreachable as $url => $title) { ?>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/32x32/shield_unreachable.png',$this->translate->_('Unreachable')) ?></td>
					<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
				<?php	} foreach ($hosts_up_disabled as $url => $title) { ?>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
					<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
				<?php	} foreach ($hosts_pending_disabled as $url => $title) { ?>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield_pending.png',$this->translate->_('Pending')) ?></td>
					<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
				<?php	} ?>
			</tr>
		</table>
	</div>
</div>