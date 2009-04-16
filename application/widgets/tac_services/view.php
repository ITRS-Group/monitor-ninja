<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm w98 left" id="widget-tac_services">
	<div class="widget-header"><?php echo $title ?></div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table summary="<?php echo $title; ?>">
			<tr>
			<?php	foreach ($header_links as $url => $title) { ?>
				<td colspan="2"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
			<?php	} ?>
			</tr>
			<tr>
				<?php foreach ($services_critical as $url => $title) { ?>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/32x32/shield_critical.png',$this->translate->_('Critical')) ?></td>
					<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
				<?php	} foreach ($services_warning as $url => $title) { ?>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/32x32/shield_warning.png',$this->translate->_('Warning')) ?></td>
					<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
				<?php	} foreach ($services_unknown as $url => $title) { ?>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield_unknown.png',$this->translate->_('Unknown')) ?></td>
					<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
				<?php	} foreach ($services_ok_disabled as $url => $title) { ?>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield-disabled.png',$this->translate->_('Disabled')) ?></td>
					<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
				<?php	} foreach ($services_pending_disabled as $url => $title) { ?>
					<td class="dark"><?php echo html::image('/application/views/themes/default/images/icons/12x12/shield_pending.png',$this->translate->_('Pending')) ?></td>
					<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
				<?php	} ?>
			</tr>
		</table>
	</div>
</div>