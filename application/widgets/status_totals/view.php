<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm right" id="widget-host_totals" style="margin-right: 1%">
	<div class="widget-header">
		<strong><?php echo $host_title ?></strong>
	</div>
	<div class="widget-content">
		<table class="max" style="border-spacing: 1px">
			<tr>
			<?php foreach ($host_header as $row) { ?>
				<td class="status icon"><?php echo html::image('application/views/themes/default/images/icons/16x16/shield-'.strtolower($row['status']).'.png',array('title' => $this->translate->_($row['status']), 'alt' => $this->translate->_($row['status']))) ?></td>
				<td class="icon"><?php echo html::anchor($row['url'], html::specialchars($row['lable'])) ?></td>
			<?php	} ?>
			</tr>
			<tr>
				<td class="status icon"><?php echo $total_problems ?></td>
				<td colspan="3"><?php echo html::anchor('status/host/'.$host.'/'.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), html::specialchars($label_all_host_problems)) ?></td>
				<td class="status icon"><?php echo $total_hosts ?></td>
				<td colspan="3"><?php echo html::anchor('status/service/'.$host.'/false/'.$service_state, html::specialchars($label_all_host_types)) ?></td>
			</tr>
		</table>
	</div>
</div>

<div class="widget movable collapsable removable closeconfirm right" id="widget-status_totals">
	<div class="widget-header">
		<strong><?php echo $service_title ?></strong>
	</div>
	<div class="widget-content">
		<div id="widget_status_totals_right">
			<table style="border-spacing: 1px">
				<tr>
					<?php foreach ($service_header as $row) { ?>
						<td class="status icon"><?php echo html::image('application/views/themes/default/images/icons/16x16/shield-'.strtolower($row['status']).'.png',$this->translate->_($row['status'])) ?></td>
						<td class="icon"><?php echo html::anchor($row['url'], html::specialchars($row['lable'])) ?></td>
					<?php } ?>
				</tr>
				<tr>
					<td class="status icon"><?php echo $svc_total_problems ?></td>
					<td colspan="5"><?php echo html::anchor('status/service/'.$host.'/'.(nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'/'.(nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL), html::specialchars($label_all_service_problems)) ?></td>
					<td class="status icon"><?php echo $svc_total_services ?></td>
					<td colspan="3"><?php echo html::anchor('status/service/'.$host.'/', html::specialchars($label_all_service_types)) ?></td>
				</tr>
			</table>
		</div>
	</div>
</div>
