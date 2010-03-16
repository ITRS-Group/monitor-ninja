<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w98" id="network_outages">
	<table id="network_outages_table">
		<caption><?php echo $title ?></caption>
		<thead>
			<tr>
				<th class="headerNone">&nbsp;</th>
				<th class="headerNone"><?php echo $label_host ?></th>
				<th class="headerNone" style="width: 57px"><?php echo $label_notes ?></th>
				<th class="headerNone"><?php echo $label_severity ?></th>
				<th class="headerNone"><?php echo $label_duration ?></th>
				<th class="headerNone"><?php echo $label_hosts_affected ?></th>
				<th class="headerNone"><?php echo $label_services_affected ?></th>
				<th class="headerNone"><?php echo $label_actions ?></th>
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
					<?php echo html::image($this->add_path('icons/16x16/shield-'.strtolower(Current_status_Model::status_text($details['current_state'])).'.png'),array('alt' => Current_status_Model::status_text($details['current_state']), 'title' => Current_status_Model::status_text($details['current_state']))) ?>
				</td>
				<td><?php echo html::anchor('extinfo/details/host/'.$host, $host) ?></td>
				<td class="icon">
					<?php echo $details['comments'] == 0 ? '' : html::anchor('extinfo/details/host/'.$host, html::image($this->add_path('icons/16x16/add-comment.png'),array('alt' => $this->translate->_('View comments for this host'), 'title' => $this->translate->_('View comments for this host'))),array('style' => 'border: 0px')); ?>
				</td>
				<td><?php echo $details['severity'] ?></td>
				<td><?php echo time::to_string($details['duration']) ?></td>
				<td><?php echo $details['affected_hosts'] ?></td>
				<td><?php echo $details['affected_services'] ?></td>
				<td>
					<?php
						echo html::anchor('status/service/'.$host, html::image($this->add_path('icons/16x16/service-details.gif'),array('alt' => $this->translate->_('View status detail for this host'), 'title' => $this->translate->_('View status detail for this host'))),array('style' => 'border: 0px')).'&nbsp;';
						echo html::anchor('nagvis/geomap/host/'.$host, html::image($this->add_path('icons/16x16/locate-host-on-map.png'),array('alt' => $this->translate->_('View status map for this host and its children'), 'title' => $this->translate->_('View status map for this host and its children'))),array('style' => 'border: 0px')).'&nbsp;';
						echo html::anchor('trends/host/'.$host, html::image($this->add_path('icons/16x16/trends.png'),array('alt' => $this->translate->_('View trends for this host'), 'title' => $this->translate->_('View trends for this host'))),array('style' => 'border: 0px')).'&nbsp;';
						echo html::anchor('showlog/showlog/'.$host, html::image($this->add_path('icons/16x16/history.png'),array('alt' => $this->translate->_('View alert history for this host'), 'title' => $this->translate->_('View alert history for this host'))),array('style' => 'border: 0px')).'&nbsp;';
						echo html::anchor('notifications/host/'.$host, html::image($this->add_path('icons/16x16/notify.png'),array('alt' => $this->translate->_('View notifications for this host'), 'title' => $this->translate->_('View notifications for this host'))),array('style' => 'border: 0px'))
					?>
				</td>
			</tr>
			<?php }	}	?>
		</tbody>
	</table>
</div>