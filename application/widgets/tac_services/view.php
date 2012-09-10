<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table>
	<colgroup>
		<col style="width: 20%" />
		<col style="width: 20%" />
		<col style="width: 20%" />
		<col style="width: 20%" />
		<col style="width: 20%" />
	</colgroup>
	<tr>
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_CRITICAL, $current_status->services_critical.' '._('Critical')) ?></th>
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_WARNING, $current_status->services_warning.' '._('Warning'))?></th>
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_UNKNOWN, $current_status->services_unknown.' '._('Unknown')) ?></th>
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_OK, $current_status->services_ok.' '._('OK')) ?></th>
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_PENDING, $current_status->services_pending.' '._('Pending')) ?></th>
	</tr>
	<tr>
		<td style="padding:0px; white-space:normal;" class="white">
			<table>
					<?php if (count($services_critical) > 0) { foreach ($services_critical as $url => $title) { ?>
					<tr>
						<td class="dark">
							<?php
								$icon = explode(' ',$title);
								echo html::image($this->add_path('icons/16x16/'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-critical' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'.png'),$icon[1]);
							?>
						</td>
						<td <?php echo ($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'class="status-critical"' : ''; ?> style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-critical.png'),_('Critical')) ?></td>
						<td><?php echo html::anchor($default_links['critical'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td style="padding:0px; white-space:normal;" class="white" >
			<table>
					<?php	if (count($services_warning) > 0) { foreach ($services_warning as $url => $title) { ?>
					<tr>
						<td class="dark">
							<?php
								$icon = explode(' ',$title);
								echo html::image($this->add_path('icons/16x16/'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-warning' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'.png'),$icon[1]);
							?>
						</td>
						<td <?php echo ($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'class="status-warning"' : ''; ?> style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-warning.png'),_('Warning')) ?></td>
						<td><?php echo html::anchor($default_links['warning'], _('N/A') )?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td style="padding:0px; white-space:normal;" class="white">
			<table>
					<?php	if (count($services_unknown) > 0) { foreach ($services_unknown as $url => $title) { ?>
					<tr>
						<td class="dark">
							<?php
								$icon = explode(' ',$title);
								echo html::image($this->add_path('icons/16x16/'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-unknown' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'.png'),$icon[1]);
							?>
						</td>
						<td <?php echo ($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'class="status-unknown"' : ''; ?> style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-unknown.png'),_('Unknown')) ?></td>
						<td><?php echo html::anchor($default_links['unknown'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td style="padding:0px; white-space:normal;" class="white">
			<table>
					<?php	if ($current_status->services_ok > 0) { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-ok.png'),_('OK')) ?></td>
						<td class="status-ok" style="white-space:normal"><?php echo html::anchor('status/service/all?servicestatustypes=1', html::specialchars($current_status->services_ok.' '._('OK'))) ?></td>
					</tr>
					<?php }	if (count($services_ok_disabled) > 0) { foreach ($services_ok_disabled as $url => $title) { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-disabled.png'),_('Disabled')) ?></td>
						<td style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } if (count($services_ok_disabled) == 0 && $current_status->services_ok == 0) { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-ok.png'),_('OK')) ?></td>
						<td><?php echo html::anchor($default_links['ok'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td style="padding:0px; white-space:normal;" class="white">
			<table>
					<?php	if (count($services_pending) > 0) {	foreach ($services_pending as $url => $title) { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-pending.png'),_('Pending')) ?></td>
						<td style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-pending.png'),_('Not pending')) ?></td>
						<td><?php echo html::anchor($default_links['pending'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
	</tr>
</table>
