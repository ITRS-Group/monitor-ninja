<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w98" id="network_outages">
	<table id="network_outages_table">
		<!--<caption><?php echo $title ?></caption>-->
		<thead>
			<tr>
				<th class="header">&nbsp;</th>
				<th class="header"><?php echo $label_host ?></th>
				<th class="header"><?php echo $label_severity ?></th>
				<th class="header"><?php echo $label_duration ?></th>
				<th class="header"><?php echo $label_hosts_affected ?></th>
				<th class="header"><?php echo $label_services_affected ?></th>
				<th class="header" colspan="6"><?php echo $label_actions ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 0;
			if (!empty($outage_data)) {
				foreach ($outage_data as $host => $details) {
					$i++;
			?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
				<td class="icon">
					<?php echo html::image('/application/views/themes/default/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($details['current_state'])).'.png',array('alt' => 'hej', 'title' => 'hej')) ?>
				</td>
				<td>
					<?php echo html::anchor('extinfo/details/host/'.$host, $host) ?>
				</td>
				<td><?php echo $details['severity'] ?></td>
				<td><?php echo $details['duration'] ?></td>
				<td><?php echo $details['affected_hosts'] ?></td>
				<td><?php echo $details['affected_services'] ?></td>
				<td class="icon">
					<?php echo html::anchor('extinfo/details/host/'.$host, html::image('/application/views/themes/default/icons/16x16/add-comment.png',array('alt' => $this->translate->_('View comments for this host'), 'title' => $this->translate->_('View comments for this host')))) ?>
				</td>
				<td class="icon">
					<?php echo html::anchor('status/host/'.$host, html::image('/application/views/themes/default/icons/16x16/service-details.gif',array('alt' => $this->translate->_('View status detail for this host'), 'title' => $this->translate->_('View status detail for this host')))) ?>
				</td>
				<td class="icon">
					<?php echo html::anchor('statusmap/'.$host, html::image('/application/views/themes/default/icons/16x16/locate-host-on-map.png',array('alt' => $this->translate->_('View status map for this host and its children'), 'title' => $this->translate->_('View status map for this host and its children')))) ?>
				</td>
				<td class="icon">
					<?php echo html::anchor('trends/host/'.$host, html::image('/application/views/themes/default/icons/16x16/trends.gif',array('alt' => $this->translate->_('View trends for this host'), 'title' => $this->translate->_('View trends for this host')))) ?>
				</td>
				<td class="icon">
					<?php echo html::anchor('history/host/'.$host, html::image('/application/views/themes/default/icons/16x16/history.gif',array('alt' => $this->translate->_('View alert history for this host'), 'title' => $this->translate->_('View alert history for this host')))) ?>
				</td>
				<td class="icon">
					<?php echo html::anchor('notifications/host/'.$host, html::image('/application/views/themes/default/icons/16x16/notify.png',array('alt' => $this->translate->_('View notifications for this host'), 'title' => $this->translate->_('View notifications for this host')))) ?>
				</td>
			</tr>
			<?php }	}	?>
		</tbody>
	</table>
</div>