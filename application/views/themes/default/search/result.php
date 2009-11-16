<?php defined('SYSPATH') OR die('No direct access allowed.');
$label_na = $this->translate->_('N/A');
?>

<div class="widget left w98" id="search_result">
	<!--<p><strong><?php echo $this->translate->_('Search result for'); ?> &quot;<?php echo $query ?>&quot;</strong>:</p>-->

<?php echo isset($no_data) ? $no_data : '';
# show host data if available
if (isset($host_result) ) { ?>

<table id="host_table">
	<caption><?php echo $this->translate->_('Host results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header">&nbsp;</th>
		<th class="header"><?php echo $this->translate->_('Host'); ?></th>
		<th class="no-sort"
			<?php echo 'colspan="'.
					(((nacoma::link()===true) && (Kohana::config('config.pnp4nagios_path')!==false))
					? 5
					: (((nacoma::link()===true) || (Kohana::config('config.pnp4nagios_path')!==false))
						? 4
						: 3)).'">'?>
				<?php echo $this->translate->_('Actions'); ?></th>

		<th class="header"><?php echo $this->translate->_('Alias'); ?></th>
		<th class="header" style="width: 70px"><?php echo $this->translate->_('Address'); ?></th>
		<th class="header"><?php echo $this->translate->_('Status Information'); ?></th>
		<th class="header"><?php echo $this->translate->_('Display Name'); ?></th>
	</tr>
<?php	$i = 0; foreach ($host_result as $host) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl icon">
			<?php echo html::image($this->add_path('icons/16x16/shield-'.strtolower(Current_status_Model::status_text($host->current_state)).'.png'),array('alt' => Current_status_Model::status_text($host->current_state), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($host->current_state))); ?>
		</td>
		<td>
			<div style="float: left"><?php echo html::anchor('extinfo/details/host/'.$host->host_name, $host->host_name) ?></div>
			<?php	$host_comments = Comment_Model::count_comments($host->host_name);
				if ($host_comments!=0) { ?>
			<span style="float: right">
				<?php echo html::anchor('extinfo/details/host/'.$host->host_name.'#comments',
						html::image($this->add_path('icons/16x16/add-comment.png'),
						array('alt' => sprintf($this->translate->_('This host has %s comment(s) associated with it'), $host_comments),
						'title' => sprintf($this->translate->_('This host has %s comment(s) associated with it'), $host_comments))), array('style' => 'border: 0px')); ?>
			</span>
			<?php } ?>
			<div style="float: right"><?php
				if ($host->problem_has_been_acknowledged) {
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => $this->translate->_('Acknowledged'), 'title' => $this->translate->_('Acknowledged'))), array('style' => 'border: 0px'));
				}
				if (empty($host->notifications_enabled)) {
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => $this->translate->_('Notification enabled'), 'title' => $this->translate->_('Notification disabled'))), array('style' => 'border: 0px'));
				}
				if (!$host->active_checks_enabled) {
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => $this->translate->_('Active checks enabled'), 'title' => $this->translate->_('Active checks disabled'))), array('style' => 'border: 0px'));
				}
				if (isset($host->is_flapping) && $host->is_flapping) {
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => $this->translate->_('Flapping'), 'title' => $this->translate->_('Flapping'), 'style' => 'margin-bottom: -2px')), array('style' => 'border: 0px'));
				}
				if ($host->scheduled_downtime_depth > 0) {
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16/downtime.png'),array('alt' => $this->translate->_('Scheduled downtime'), 'title' => $this->translate->_('Scheduled downtime'))), array('style' => 'border: 0px'));
				} ?>
			</div>
		</td>
		<td class="icon">
			<?php echo html::anchor('status/service/'.$host->host_name,html::image($this->add_path('icons/16x16/service-details.gif'), $this->translate->_('View service details for this host')), array('style' => 'border: 0px')) ?>
		</td>
		<td class="icon">
			<?php if (!empty($host->action_url)) { ?>
				<a href="<?php echo nagstat::process_macros($host->action_url, $host) ?>" style="border: 0px" target="_blank">
					<?php echo html::image($this->add_path('icons/16x16/host-actions.png'), $this->translate->_('Perform extra host actions')) ?>
				</a>
				<?php } ?>
			</td>
			<td class="icon">
			<?php	if (!empty($host->notes_url)) { ?>
				<a href="<?php echo nagstat::process_macros($host->notes_url, $host) ?>" style="border: 0px" target="_blank">
					<?php echo html::image($this->add_path('icons/16x16/host-notes.png'), $this->translate->_('View extra host notes')) ?>
				</a>
			<?php	} ?>
			</td>
			<?php if (Kohana::config('config.pnp4nagios_path')!==false) { ?>
			<td class="icon">
				<?php echo (pnp::has_graph($host->host_name))  ? '<a href="/ninja/index.php/pnp/?host='.urlencode($host->host_name).'" style="border: 0px">'.html::image($this->add_path('icons/16x16/pnp.png'), array('alt' => 'Show performance graph', 'title' => 'Show performance graph')).'</a>' : ''; ?>
			</td>
			<?php } ?>
		<?php if (isset ($nacoma_link)) { ?>
		<td class="icon">
			<?php echo html::anchor($nacoma_link.'host/'.$host->host_name, html::image($this->img_path('icons/16x16/nacoma.png'), array('alt' => $label_nacoma, 'title' => $label_nacoma))) ?>
		</td>
		<?php } ?>
		<td style="white-space: normal"><?php echo $host->alias ?></td>
		<td><?php echo $host->address ?></td>
		<td style="white-space	: normal"><?php echo str_replace('','',$host->output) ?></td>
		<td><?php echo $host->display_name ?></td>
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
<?php
	$i = 0;
	$prev_host = false;
	foreach ($service_result as $service) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<?php if ($prev_host != $service->host_name) { ?>
		<td class="bl icon"><?php echo html::image($this->add_path('icons/16x16/shield-'.strtolower(Current_status_Model::status_text($service->host_state)).'.png'),array('alt' => Current_status_Model::status_text($service->host_state), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($service->host_state))); ?></td>
		<td><?php echo html::anchor('extinfo/details/host/'.$service->host_name, $service->host_name) ?></td>
		<?php } else { ?>
		<td colspan="2" class="white" style="background-color:#ffffff;border:0px; border-right: 1px solid #cdcdcd"></td>
		<?php } ?>
		<td class="icon"><?php echo html::image($this->add_path('icons/16x16/shield-'.strtolower(Current_status_Model::status_text($service->current_state, 'service')).'.png'),array('alt' => Current_status_Model::status_text($service->current_state, 'service'), 'title' => $this->translate->_('Service status').': '.Current_status_Model::status_text($service->current_state, 'service'))); ?></td>
		<td>
			<?php echo html::anchor('/extinfo/details/service/'.$service->host_name.'?service='.urlencode($service->service_description), $service->service_description) ?>
		</td>
		<td><?php echo $service->last_check ? date('Y-m-d H:i:s',$service->last_check) : $label_na ?></td>
		<td><?php echo $service->display_name ?></td>
		<?php if (isset ($nacoma_link)) { ?>
		<td class="icon">
			<?php echo html::anchor($nacoma_link.'service/'.$service->host_name.'?service='.urlencode($service->service_description), html::image($this->img_path('icons/16x16/nacoma.png'), array('alt' => $label_nacoma, 'title' => $label_nacoma))) ?>
		</td>
		<?php } ?>
	</tr>
<?php	$i++;
	$prev_host = $service->host_name;
	} ?>
</table><br /><?php
}

# show servicegroup data if available
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

# show hostgroup data if available
if (isset($hostgroup_result) ) { ?>
<table>
<caption><?php echo $this->translate->_('Hostgroup results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header"><?php echo $this->translate->_('Hostgroup'); ?></th>
		<th class="header"><?php echo $this->translate->_('Alias'); ?></th>
	</tr>
<?php	$i = 0; foreach ($hostgroup_result as $hostgroup) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl"><?php echo html::anchor('extinfo/details/hostgroup/'.$hostgroup->hostgroup_name, $hostgroup->hostgroup_name) ?></td>
		<td><?php echo html::anchor('status/hostgroup/'.$hostgroup->hostgroup_name.'?style=detail', $hostgroup->alias) ?></td>
	</tr>
<?php $i++;	} ?>
</table><?php
}