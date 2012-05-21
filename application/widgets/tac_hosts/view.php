<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table>
	<colgroup>
		<col style="width: 25%" />
		<col style="width: 25%" />
		<col style="width: 25%" />
		<col style="width: 25%" />
	</colgroup>
	<tr>
		<th><?php echo html::anchor('status/host/all?hoststatustypes='.nagstat::HOST_DOWN , $current_status->hosts_down.' '._('Down')) ?></th>
		<th><?php echo html::anchor('status/host/all?hoststatustypes='.nagstat::HOST_UNREACHABLE , $current_status->hosts_unreachable.' '._('Unreachable')) ?></th>
		<th><?php echo html::anchor('status/host/all?hoststatustypes='.nagstat::HOST_UP, $current_status->hosts_up.' '._('Up') )?></th>
		<th><?php echo html::anchor('status/host/all?hoststatustypes='.nagstat::HOST_PENDING, $current_status->hosts_pending.' '._('Pending')) ?></th>
	</tr>
	<tr>
		<td class="white">
			<table>
					<?php if (count($hosts_down) > 0) { foreach ($hosts_down as $url => $title) { ?>
					<tr>
						<td class="dark">
						<?php
							$icon = explode(' ',$title);
							echo html::image($this->add_path('icons/16x16/'.(($icon[1] == 'Unhandled') ? 'shield-critical' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'.png'),$icon[1]);
						?>
						</td>
						<td<?php echo $icon[1] == 'Unhandled' ? ' class="status-down"' : ''; ?>><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-critical.png'),_('Down')) ?></td>
						<td><?php echo html::anchor($default_links['down'], _('N/A')) ?></td>
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
								echo html::image($this->add_path('icons/16x16/'.(($icon[1] == 'Unhandled') ? 'shield-unreachable' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'.png'),$icon[1]);
							?>
						</td>
						<td<?php echo $icon[1] == 'Unhandled' ? ' class="status-unreachable"' : ''; ?>><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-unreachable.png'),_('Unreachalbe')) ?></td>
						<td><?php echo html::anchor($default_links['unreachable'], _('N/A')) ?></td>
						</tr>
					<?php } ?>
			</table>
		</td>
		<td class="white">
			<table>
					<?php	if ($current_status->hosts_up > 0) { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-ok.png'),_('Up')) ?></td>
						<td class="status-up"><?php echo html::anchor('status/host/all/1/', html::specialchars($current_status->hosts_up.' '._('Up'))) ?></td>
					</tr>
					<?php } if (count($hosts_up_disabled) > 0) { foreach ($hosts_up_disabled as $url => $title) { ?>
						<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-disabled.png'),_('Disabled')) ?></td>
						<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } if (count($hosts_up_disabled) == 0 && $current_status->hosts_up == 0) { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-up.png'),_('Up')) ?></td>
						<td><?php echo html::anchor($default_links['up'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td class="white">
			<table>
					<?php if (count($hosts_pending) > 0) { foreach ($hosts_pending as $url => $title) { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-pending.png'),_('Pending')) ?></td>
						<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-pending.png'),_('Critical')) ?></td>
						<td><?php echo html::anchor($default_links['pending'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
	</tr>
</table>
