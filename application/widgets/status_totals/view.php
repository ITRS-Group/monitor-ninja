<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm right" id="widget-status_totals" style="margin-left: 0%; margin-right: 1%;  width: 170px">
	<div class="widget-header" style="margin-top: -1px"><?php echo $service_title ?></div>
	<div class="widget-content">
		<div id="widget_status_totals_right">
			<table style="border-spacing: 1px; background-color: #dcdccd; margin-top: -1px">

					<?php foreach ($service_header as $row) { ?>
					<tr>
							<td class="status icon">
								<?php
									if ($row['lable'] > 0)
										echo html::image('application/views/themes/default/images/icons/12x12/shield-'.strtolower($row['status']).'.png',$row['status']) ;
									else
										echo html::image('application/views/themes/default/images/icons/12x12/shield-not-'.strtolower($row['status']).'.png',$row['status']) ;
								?>
							</td>
							<td style="padding-right: 7px"><?php echo html::anchor($row['url'], html::specialchars($row['lable'].' '.$row['status'])) ?></td>
						</tr>
					<?php } ?>
					<tr>
					<td class="status icon">
						<?php
							if ($svc_total_problems > 0)
								echo html::image('application/views/themes/default/images/icons/12x12/shield-warning.png',array('title' => $row['status'], 'alt' => $row['status']));
							else
								echo html::image('application/views/themes/default/images/icons/12x12/shield-not-warning.png',array('title' => $row['status'], 'alt' => $row['status']));
						?>
					</td>
					<td style="padding-right: 7px"><?php echo html::anchor('status/service/'.$host.'/?hoststatustypes='.(nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&servicestatustypes='.(nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL).'&group_type='.$grouptype, html::specialchars($svc_total_problems.' '.$label_all_service_problems)) ?></td>
					</tr>
					<tr>
					<td class="status icon"><?php echo html::image('application/views/themes/default/images/icons/12x12/shield-info.png',array('title' => $row['status'], 'alt' => $row['status'])); ?></td>
					<td style="padding-right: 7px"><?php echo html::anchor('status/service/'.$host.'/?hoststatustypes='.$host_state.'&group_type='.$grouptype, html::specialchars($svc_total_services.' Services in Total')) ?></td>
				</tr>
			</table>
		</div>
	</div>
</div>

<div class="widget movable collapsable removable closeconfirm right" id="widget-host_totals" style="margin-right: 1%; width: 170px">
	<div class="widget-header"><?php echo $host_title ?></div>
	<div class="widget-content">
		<table class="max" style="border-spacing: 1px; background-color: #dcdccd; margin-top: -1px">

			<?php foreach ($host_header as $row) { ?>
			<tr>
				<td class="status icon">
					<?php
					if ($row['lable'] > 0)
						echo html::image('application/views/themes/default/images/icons/12x12/shield-'.strtolower($row['status']).'.png',array('title' => $row['status'], 'alt' => $row['status']));
					else
						echo html::image('application/views/themes/default/images/icons/12x12/shield-not-'.strtolower($row['status']).'.png',array('title' => $row['status'], 'alt' => $row['status']));
					?>
				</td>
				<td style="padding-right: 7px"><?php echo html::anchor($row['url'], html::specialchars($row['lable'].' '.$row['status'])) ?></td>
				</tr>
				<?php	} ?>
				<tr>
					<td class="status icon">
						<?php
							if ($total_problems > 0)
								echo html::image('application/views/themes/default/images/icons/12x12/shield-warning.png',array('title' => $row['status'], 'alt' => $row['status']));
							else
								echo html::image('application/views/themes/default/images/icons/12x12/shield-not-warning.png',array('title' => $row['status'], 'alt' => $row['status']));
						?>
					</td>
					<td style="padding-right: 7px"><?php echo html::anchor('status/host/'.$host.'/'.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'?group_type='.$grouptype, html::specialchars($total_problems.' '.$label_all_host_problems)) ?></td>
				</tr>
				<tr>
					<td class="status icon"><?php echo html::image('application/views/themes/default/images/icons/12x12/shield-info.png',array('title' => $row['status'], 'alt' => $row['status'])); ?></td>
					<td style="padding-right: 7px"><?php echo html::anchor('status/'.$target_method.'/'.$host.'?group_type='.$grouptype, html::specialchars($total_hosts.' Hosts in Total')) ?></td>
				</tr>
		</table>
	</div>
</div>