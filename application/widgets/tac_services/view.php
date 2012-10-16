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
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_CRITICAL, $current_status->svc->critical.' '._('Critical')) ?></th>
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_WARNING, $current_status->svc->warning.' '._('Warning'))?></th>
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_UNKNOWN, $current_status->svc->unknown.' '._('Unknown')) ?></th>
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_OK, $current_status->svc->ok.' '._('OK')) ?></th>
		<th><?php echo html::anchor('status/service/all?servicestatustypes='.nagstat::SERVICE_PENDING, $current_status->svc->pending.' '._('Pending')) ?></th>
	</tr>
	<tr>
		<td>
			<table>
					<?php if (count($services_critical) > 0) { foreach ($services_critical as $url => $title) { ?>
					<tr>
						<td class="icon dark">
							<?php
								$icon = explode(' ',$title);
								echo '<span class="icon-16 x16-'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-critical' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'" title="'.$icon[1].'"></span>';
							?>
						</td>
						<td <?php echo ($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'class="status-critical"' : ''; ?> style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="icon dark">
							<span class="icon-16 x16-shield-not-critical" title="<?php echo _('Critical'); ?>"></span>
							</td>
						<td><?php echo html::anchor($default_links['critical'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td >
			<table>
					<?php	if (count($services_warning) > 0) { foreach ($services_warning as $url => $title) { ?>
					<tr>
						<td class="icon dark">
							<?php
								$icon = explode(' ',$title);
								echo '<span class="icon-16 x16-'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-warning' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'" title="'.$icon[1].'"></span>';
							?>
						</td>
						<td <?php echo ($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'class="status-warning"' : ''; ?> style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="icon dark">
							<span class="icon-16 x16-shield-not-warning" title="<?php echo _('Warning'); ?>"></span>
						</td>
						<td><?php echo html::anchor($default_links['warning'], _('N/A') )?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td>
			<table>
					<?php	if (count($services_unknown) > 0) { foreach ($services_unknown as $url => $title) { ?>
					<tr>
						<td class="icon dark">
							<?php
								$icon = explode(' ',$title);
								echo '<span class="icon-16 x16-'.(($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'shield-unknown' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'" title="'.$icon[1].'"></span>';
							?>
						</td>
						<td <?php echo ($icon[1] == 'Unhandled' || $icon[1] == 'on') ? 'class="status-unknown"' : ''; ?> style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>

						<td class="icon dark">
							<span class="icon-16 x16-shield-not-unknown" title="<?php echo _('Unknown'); ?>"></span>
						</td>
						<td><?php echo html::anchor($default_links['unknown'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td>
			<table>
					<?php	if ($current_status->svc->ok > 0) { ?>
					<tr>
						<td class="icon dark">
							<span class="icon-16 x16-shield-ok" title="<?php echo _('Ok'); ?>"></span>
						</td>
						<td class="status-ok" style="white-space:normal"><?php echo html::anchor('status/service/all?servicestatustypes=1', html::specialchars($current_status->svc->ok.' '._('OK'))) ?></td>
					</tr>
					<?php }	if (count($services_ok_disabled) > 0) { foreach ($services_ok_disabled as $url => $title) { ?>
					<tr>
						<td class="icon dark">
							<span class="icon-16 x16-shield-disabled" title="<?php echo _('Disabled'); ?>"></span>
						</td>
						<td style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } if (count($services_ok_disabled) == 0 && $current_status->svc->ok == 0) { ?>
					<tr>
						<td class="dark">
							<span class="icon-16 x16-shield-not-ok" title="<?php echo _('Ok'); ?>"></span>
						</td>
						<td><?php echo html::anchor($default_links['ok'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td>
			<table>
					<?php	if (count($services_pending) > 0) {	foreach ($services_pending as $url => $title) { ?>
					<tr>
						<td class="icon dark">
							<span class="icon-16 x16-shield-pending" title="<?php echo _('Pending'); ?>"></span>
						</td>
						<td style="white-space:normal"><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="icon dark">
							<span class="icon-16 x16-shield-not-pending" title="<?php echo _('Not Pending'); ?>"></span>
						</td>
						<td><?php echo html::anchor($default_links['pending'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
	</tr>
</table>
