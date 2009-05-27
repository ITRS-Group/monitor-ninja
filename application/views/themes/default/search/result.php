<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w98" id="search_result">
	<!--<p><strong><?php echo $this->translate->_('Search result for'); ?> &quot;<?php echo $query ?>&quot;</strong>:</p>-->

<?php echo isset($no_data) ? $no_data : '';
# show host data if available
if (isset($host_result) ) { ?>

<table>
	<caption><?php echo $this->translate->_('Host results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header">&nbsp;</th>
		<th class="header"><?php echo $this->translate->_('Host'); ?></th>
		<th class="header"><?php echo $this->translate->_('Alias'); ?></th>
		<th class="header" style="width: 70px"><?php echo $this->translate->_('Address'); ?></th>
		<th class="header"><?php echo $this->translate->_('Status Information'); ?></th>
		<?php if (isset ($nacoma_link)) { ?>
		<th class="header">&nbsp;</th>
		<?php } ?>
	</tr>
<?php	$i = 0; foreach ($host_result as $host) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl icon">
			<?php echo html::image('/application/views/themes/default/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($host->current_state)).'.png',array('alt' => Current_status_Model::status_text($host->current_state), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($host->current_state))); ?>
		</td>
		<td><?php echo html::anchor('extinfo/details/host/'.$host->host_name, $host->host_name) ?></td>
		<td style="white-space: normal"><?php echo $host->alias ?></td>
		<td><?php echo $host->address ?></td>
		<td style="white-space	: normal"><?php echo str_replace('','',$host->output) ?></td>
		<td><?php echo $host->display_name ?></td>
		<?php if (isset ($nacoma_link)) { ?>
		<td class="icon">
			<?php echo html::anchor($nacoma_link.'host/'.$host->host_name, html::image($this->img_path('images/icons/16x16/nacoma.png'), array('alt' => $label_nacoma, 'title' => $label_nacoma))) ?>
		</td>
		<?php } ?>
	</tr>
<?php	$i++; } ?>
</table><br /><?php
}

# show service data if available
if (isset($service_result) ) { ?>

<table>
<caption><?php echo $this->translate->_('Service results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header">&nbsp;</th>
		<th class="header"><?php echo $this->translate->_('Host'); ?></th>
		<th class="header">&nbsp;</th>
		<th class="header"><?php echo $this->translate->_('Service'); ?></th>
		<th class="header"><?php echo $this->translate->_('Last Check'); ?></th>
		<th class="header"><?php echo $this->translate->_('Display name'); ?></th>
		<?php if (isset ($nacoma_link)) { ?>
		<th class="header">&nbsp;</th>
		<?php } ?>
	</tr>
<?php	$i = 0; foreach ($service_result as $service) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl icon"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($service->host_state)).'.png',array('alt' => Current_status_Model::status_text($service->host_state), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($service->host_state))); ?></td>
		<td><?php echo html::anchor('extinfo/details/host/'.$service->host_name, $service->host_name) ?></td>
		<td class="icon"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($service->current_state, 'service')).'.png',array('alt' => Current_status_Model::status_text($service->current_state, 'service'), 'title' => $this->translate->_('Service status').': '.Current_status_Model::status_text($service->current_state, 'service'))); ?></td>
		<td>
			<?php echo html::anchor('/extinfo/details/service/'.$service->host_name.'?service='.urlencode($service->service_description), $service->service_description) ?>
		</td>
		<td><?php echo date('Y-m-d H:i:s',$service->last_check) ?></td>
		<td><?php echo $service->display_name ?></td>
		<?php if (isset ($nacoma_link)) { ?>
		<td class="icon">
			<?php echo html::anchor($nacoma_link.'service/'.$service->host_name.'?service='.urlencode($service->service_description), html::image($this->img_path('images/icons/16x16/nacoma.png'), array('alt' => $label_nacoma, 'title' => $label_nacoma))) ?>
		</td>
		<?php } ?>
	</tr>
<?php	$i++; } ?>
</table><br /><?php
}

# show hostgroup data if available
if (isset($hostgroup_result) ) { ?>
<table>
<caption><?php echo $this->translate->_('Hostgroup results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header"><?php echo $this->translate->_('Hostgroup'); ?></th>
		<th class="header"><?php echo $this->translate->_('Alias'); ?></th>
		<?php if (isset ($nacoma_link)) { ?>
		<th class="header">&nbsp;</th>
		<?php } ?>
	</tr>
<?php	$i = 0; foreach ($hostgroup_result as $hostgroup) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl"><?php echo html::anchor('extinfo/details/hostgroup/'.$hostgroup->hostgroup_name, $hostgroup->hostgroup_name) ?></td>
		<td><?php echo html::anchor('status/hostgroup/'.$hostgroup->hostgroup_name.'?style=detail', $hostgroup->alias) ?></td>
		<?php if (isset ($nacoma_link)) { ?>
		<td class="icon">
			<?php echo html::anchor($nacoma_link.'hostgroup/'.urlencode($hostgroup->hostgroup_name), html::image($this->img_path('images/icons/16x16/nacoma.png'), array('alt' => $label_nacoma, 'title' => $label_nacoma))) ?>
		</td>
		<?php } ?>
	</tr>
<?php	$i++; } ?>
</table><br /><?php
}

# show hostgroup data if available
if (isset($servicegroup_result) ) { ?>
<table>
<caption><?php echo $this->translate->_('Servicegroup results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header"><?php echo $this->translate->_('Servicegroup'); ?></th>
		<th class="header"><?php echo $this->translate->_('Alias'); ?></th>
	</tr>
<?php	$i = 0; foreach ($servicegroup_result as $servicegroup) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl"><?php echo html::anchor('extinfo/details/servicegroup/'.$servicegroup->servicegroup_name, $servicegroup->servicegroup_name) ?></td>
		<td><?php echo html::anchor('status/servicegroup/'.$servicegroup->servicegroup_name.'?style=detail', $servicegroup->alias) ?></td>
	</tr>
<?php $i++;	} ?>
</table><?php
}