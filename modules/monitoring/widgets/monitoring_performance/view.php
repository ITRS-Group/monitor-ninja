<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table class="w-table">
	<tr>
		<td class="icon dark"><span class="icon-20 x20-time"></span></td>
		<td>
			<?php echo html::anchor('extinfo/performance', _('Service Check Execution Time').':'  ) ?><br />
			<?php echo html::anchor('extinfo/performance', $service_check_execution_time) ?>
		</td>
	</tr>
	<tr>
		<td class="icon dark"><span class="icon-20 x20-time_latency"></span></td>
		<td>
			<?php echo html::anchor('extinfo/performance', _('Service Check Latency').':'  ) ?><br />
			<?php echo html::anchor('extinfo/performance', $service_check_latency); ?>
		</td>
	</tr>
	<tr>
		<td class="icon dark"><span class="icon-20 x20-time"></span></td>
		<td>
			<?php echo html::anchor('extinfo/performance', _('Host Check Execution Time').':') ?><br />
			<?php echo html::anchor('extinfo/performance', $host_check_execution_time) ?>
		</td>
	</tr>
	<tr>
		<td class="icon dark"><span class="icon-20 x20-time_latency"></span></td>
		<td>
			<?php echo html::anchor('extinfo/performance', _('Host Check Latency').':') ?><br />
			<?php echo html::anchor('extinfo/performance', $host_check_latency) ?>
		</td>
	</tr>
	<tr>
		<td class="icon dark"><span class="icon-20 x20-share"></span></td>
		<td>
			<?php echo html::anchor('status/service/?serviceprops='.nagstat::SERVICE_ACTIVE_CHECK, _('# Active Host / Service Checks').':') ?><br />
			<?php echo html::anchor('status/host/?hostprops='.nagstat::HOST_ACTIVE_CHECK, $total_active_host_checks) ?>
			/
			<?php echo html::anchor('status/service/?serviceprops='.nagstat::SERVICE_ACTIVE_CHECK, $total_active_service_checks) ?>
		</td>
	</tr>
	<tr>
		<td class="icon dark"><span class="icon-20 x20-share2"></span></td>
		<td>
			<?php echo html::anchor('status/service/?serviceprops='.nagstat::SERVICE_PASSIVE_CHECK, _('# Passive Host / Service Checks').':') ?><br />
			<?php echo html::anchor('status/host/?hostprops='.nagstat::HOST_PASSIVE_CHECK, $total_passive_host_checks) ?>
			/
			<?php echo html::anchor('status/host/?hostprops='.nagstat::HOST_PASSIVE_CHECK, $total_passive_service_checks) ?>
		</td>
	</tr>
</table>
