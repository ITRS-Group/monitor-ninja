<?php defined('SYSPATH') OR die('No direct access allowed.');
?>
<p>
	<?php echo $this->translate->_('Search result for'); ?> &quot;<i><?php echo $query ?></i>&quot;:
</p>
<?php echo isset($no_data) ? $no_data : '' ?>
<?php
# show host data if available
if (isset($host_result) ) { ?>
<?php echo $this->translate->_('Host results').":" ?>
<table cellpadding="1" cellspacing="0">
	<tr>
		<th>&nbsp;</th>
		<th><?php echo $this->translate->_('Host'); ?></th>
		<th><?php echo $this->translate->_('Alias'); ?></th>
		<th><?php echo $this->translate->_('Address'); ?></th>
		<th><?php echo $this->translate->_('Status Information'); ?></th>
		<th><?php echo $this->translate->_('Display name'); ?></th>
		<?php if (isset ($nacoma_link)) { ?>
		<th>&nbsp;</th>
		<?php } ?>
	</tr>
<?php	foreach ($host_result as $host) { ?>
	<tr>
		<td width="16px">
			<?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($host->current_state)).'.png',array('alt' => Current_status_Model::status_text($host->current_state), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($host->current_state))); ?>
		</td>
		<td><?php echo html::anchor('extinfo/details/host/'.$host->host_name, $host->host_name) ?></td>
		<td><?php echo $host->alias ?></td>
		<td><?php echo $host->address ?></td>
		<td style="white-space	: normal"><?php echo str_replace('','',$host->output) ?></td>
		<td><?php echo $host->display_name ?></td>
		<?php if (isset ($nacoma_link)) { ?>
		<td><?php printf($nacoma_link, 'host', 'host', urlencode($host->host_name)) ?></td>
		<?php } ?>
	</tr>
<?php	} ?>
</table><br /><?php
}

# show service data if available
if (isset($service_result) ) { ?>
<?php echo $this->translate->_('Service results').":" ?>
<table cellpadding="1" cellspacing="0">
	<tr>
		<th>&nbsp;</th>
		<th><?php echo $this->translate->_('Host'); ?></th>
		<th>&nbsp;</th>
		<th><?php echo $this->translate->_('Service'); ?></th>
		<th><?php echo $this->translate->_('Last Check'); ?></th>
		<th><?php echo $this->translate->_('Display name'); ?></th>
		<?php if (isset ($nacoma_link)) { ?>
		<th>&nbsp;</th>
		<?php } ?>
	</tr>
<?php	foreach ($service_result as $service) { ?>
	<tr>
		<td width="16px"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($service->host_state)).'.png',array('alt' => Current_status_Model::status_text($service->host_state), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($service->host_state))); ?></td>
		<td><?php echo html::anchor('extinfo/details/host/'.$service->host_name, $service->host_name) ?></td>
		<td width="16px"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($service->current_state, 'service')).'.png',array('alt' => Current_status_Model::status_text($service->current_state, 'service'), 'title' => $this->translate->_('Service status').': '.Current_status_Model::status_text($service->current_state, 'service'))); ?></td>
		<td>
			<?php echo html::anchor('/extinfo/details/service/'.$service->host_name.'?service='.urlencode($service->service_description), $service->service_description) ?>
		</td>
		<td><?php echo date('Y-m-d H:i:s',$service->last_check) ?></td>
		<td><?php echo $service->display_name ?></td>
		<?php if (isset ($nacoma_link)) { ?>
		<td><?php printf($nacoma_link, 'service', 'service', urlencode($service->service_description)) ?></td>
		<?php } ?>
	</tr>
<?php	} ?>
</table><br /><?php
}

# show hostgroup data if available
if (isset($hostgroup_result) ) { ?>
<?php echo $this->translate->_('Hostgroup results').":" ?>
<table cellpadding="1" cellspacing="0">
	<tr>
		<th><?php echo $this->translate->_('Hostgroup'); ?></th>
		<th><?php echo $this->translate->_('Alias'); ?></th>
		<?php if (isset ($nacoma_link)) { ?>
		<th>&nbsp;</th>
		<?php } ?>
	</tr>
<?php	foreach ($hostgroup_result as $hostgroup) { ?>
	<tr>
		<td><?php echo html::anchor('extinfo/details/hostgroup/'.$hostgroup->hostgroup_name, $hostgroup->hostgroup_name) ?></td>
		<td><?php echo html::anchor('status/hostgroup/'.$hostgroup->hostgroup_name.'?style=detail', $hostgroup->alias) ?></td>
		<?php if (isset ($nacoma_link)) { ?>
		<td><?php printf($nacoma_link, 'hostgroup', 'hostgroup', urlencode($hostgroup->hostgroup_name)) ?></td>
		<?php } ?>
	</tr>
<?php	} ?>
</table><br /><?php
}

# show hostgroup data if available
if (isset($servicegroup_result) ) { ?>
<?php echo $this->translate->_('Servicegroup results').":" ?>
<table cellpadding="1" cellspacing="0">
	<tr>
		<th><?php echo $this->translate->_('Servicegroup'); ?></th>
		<th><?php echo $this->translate->_('Alias'); ?></th>
	</tr>
<?php	foreach ($servicegroup_result as $servicegroup) { ?>
	<tr>
		<td><?php echo html::anchor('extinfo/details/servicegroup/'.$servicegroup->servicegroup_name, $servicegroup->servicegroup_name) ?></td>
		<td><?php echo html::anchor('status/servicegroup/'.$servicegroup->servicegroup_name.'?style=detail', $servicegroup->alias) ?></td>
	</tr>
<?php	} ?>
</table><br /><?php
}
