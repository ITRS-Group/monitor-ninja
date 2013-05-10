<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table>
	<colgroup>
		<col style="width: 25%" />
		<col style="width: 25%" />
		<col style="width: 25%" />
		<col style="width: 25%" />
	</colgroup>
	<tr>
		<th><?php echo html::anchor('status/host/all?hoststatustypes='.nagstat::HOST_DOWN , $current_status->hst->down.' '._('Down')) ?></th>
		<th><?php echo html::anchor('status/host/all?hoststatustypes='.nagstat::HOST_UNREACHABLE , $current_status->hst->unreachable.' '._('Unreachable')) ?></th>
		<th><?php echo html::anchor('status/host/all?hoststatustypes='.nagstat::HOST_UP, $current_status->hst->up.' '._('Up') )?></th>
		<th><?php echo html::anchor('status/host/all?hoststatustypes='.nagstat::HOST_PENDING, $current_status->hst->pending.' '._('Pending')) ?></th>
	</tr>
	<tr>
		<td>
			<table class="no_border">
					<?php if (count($hosts_down) > 0) { foreach ($hosts_down as $url => $title) { ?>
					<tr>
						<td class="icon dark">
						<?php
							$icon = explode(' ',$title);
							echo html::image($this->add_path('icons/16x16/'.(($icon[1] == 'Unhandled') ? 'shield-critical' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'.png'),$icon[1]);
						?>
						</td>
						<td<?php echo $icon[1] == 'Unhandled' ? ' class="status-down"' : ''; ?>><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="icon dark"><?php 
							echo '<span class="icon-16 x16-shield-not-critical" title="'._('Down').'"></span>';
						?></td>
						<td><?php echo html::anchor($default_links['down'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td>
			<table class="no_border">
					<?php if (count($hosts_unreachable) > 0) { foreach ($hosts_unreachable as $url => $title) { ?>
					<tr>
						<td class="icon dark">
							<?php
								$icon = explode(' ',$title);
								echo '<span class="icon-16 x16-'.(($icon[1] == 'Unhandled') ? 'shield-unreachable' : strtolower($icon[1]).($icon[1] == 'Scheduled' ? '-downtime' : '')).'" title="'.$icon[1].'"></span>';
							?>
						</td>
						<td<?php echo $icon[1] == 'Unhandled' ? ' class="status-unreachable"' : ''; ?>><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="icon dark"><?php 
							echo '<span class="icon-16 x16-shield-not-unreachable" title="'._('Unreachable').'"></span>'; 
						?></td>
						<td><?php echo html::anchor($default_links['unreachable'], _('N/A')) ?></td>
						</tr>
					<?php } ?>
			</table>
		</td>
		<td>
			<table class="no_border">
					<?php	if ($current_status->hst->up > 0) { ?>
					<tr>
						<td class="icon dark"><?php echo '<span class="icon-16 x16-shield-ok" title="'._('Up').'"></span>';  ?></td>
						<td class="status-up"><?php echo html::anchor('status/host/all/1/', html::specialchars($current_status->hst->up.' '._('Up'))) ?></td>
					</tr>
					<?php } if (count($hosts_up_disabled) > 0) { foreach ($hosts_up_disabled as $url => $title) { ?>
						<tr>
						<td class="icon dark"><?php echo '<span class="icon-16 x16-shield-disabled" title="'._('Disabled').'"></span>';  ?></td>
						<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } if (count($hosts_up_disabled) == 0 && $current_status->hst->up == 0) { ?>
					<tr>
						<td class="icon dark"><?php echo '<span class="icon-16 x16-shield-not-up" title="'._('Up').'"></span>';  ?></td>
						<td><?php echo html::anchor($default_links['up'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td>
			<table class="no_border">
					<?php if (count($hosts_pending) > 0) { foreach ($hosts_pending as $url => $title) { ?>
					<tr>
						<td class="icon dark"><?php echo '<span class="icon-16 x16-shield-pending" title="'._('Pending').'"></span>';  ?></td>
						<td><?php echo html::anchor($url, html::specialchars($title)) ?></td>
					</tr>
					<?php } } else { ?>
					<tr>
						<td class="icon dark"><?php echo '<span class="icon-16 x16-shield-not-pending" title="'._('Critical').'"></span>';  ?></td>
						<td><?php echo html::anchor($default_links['pending'], _('N/A')) ?></td>
					</tr>
					<?php } ?>
			</table>
		</td>
	</tr>
</table>
