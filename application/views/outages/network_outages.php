<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="network_outages">
	<table id="network_outages_table">
		<thead>
			<tr>
				<th><em>Status</em></th>
				<th><?php echo _('Host') ?></th>
				<th style="width: 57px"><?php echo _('Notes') ?></th>
				<th><?php echo _('Severity') ?></th>
				<th><?php echo _('State Duration') ?></th>
				<th><?php echo _('# Hosts Affected') ?></th>
				<th><?php echo _('# Services Affected') ?></th>
				<th><?php echo _('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 0;
			if (!empty($outage_data)) {
				foreach ($outage_data as $details) {
					$i++;
			?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
				<?php $current_status = strtolower(Current_status_Model::status_text($details['state'], $details['has_been_checked'])); ?>
				<td class="icon <?php echo $current_status; ?>">
					<span class="icon-16 x16-shield-<?php echo $current_status; ?>"></span>
				</td>
				<td><?php echo html::anchor('extinfo/details/host/'.$details['name'], $details['name']) ?></td>
				<td class="icon">
					<?php echo $details['comments'] == 0 ? '' : html::anchor('extinfo/details/host/'.$details['name'], html::image($this->add_path('icons/16x16/add-comment.png'),array('alt' => _('View comments for this host'), 'title' => _('View comments for this host'))),array('style' => 'border: 0px')); ?>
					<?php echo $details['acknowledged'] == 0 ? '' : html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => _('Host problem is acknowledged'), 'title' => _('Host problem is acknowledged')),array('style' => 'border: 0px')); ?>
					<?php echo $details['scheduled_downtime_depth'] == 0 ? '' : html::image($this->add_path('icons/16x16/scheduled-downtime.png'),array('alt' => _('Host is in scheduled downtime'), 'title' => _('Host is in scheduled downtime')),array('style' => 'border: 0px')); ?>
				</td>
				<td><?php echo $details['severity'] ?></td>
				<td><?php echo time::to_string($details['duration']) ?></td>
				<td><?php echo $details['affected_hosts'] ?></td>
				<td><?php echo $details['affected_services'] ?></td>
				<td>
					<?php
						echo html::anchor(listview::link('services',array('host.name'=>$details['name'])), html::image($this->add_path('icons/16x16/service-details.gif'),array('alt' => _('View status detail for this host'), 'title' => _('View status detail for this host'))),array('style' => 'border: 0px')).'&nbsp;';
						if ( Kohana::config('nagvis.nagvis_path') ) {
							echo html::anchor('nagvis/automap/host/'.urlencode($details['name']), html::image($this->add_path('icons/16x16/locate-host-on-map.png'),array('alt' => _('Locate host on map'), 'title' => _('Locate host on map'))),array('style' => 'border: 0px')).'&nbsp;';
						}
						echo html::anchor('avail/generate?include_trends=1&amp;host_name[]='.urlencode($details['name']), html::image($this->add_path('icons/16x16/trends.png'),array('alt' => _('View trends for this host'), 'title' => _('View trends for this host'))),array('style' => 'border: 0px')).'&nbsp;';
						echo html::anchor('alert_history/generate?host_name[]='.urlencode($details['name']), html::image($this->add_path('icons/16x16/alert-history.png'),array('alt' => _('View alert history for this host'), 'title' => _('View alert history for this host'))),array('style' => 'border: 0px')).'&nbsp;';
						echo html::anchor(listview::link('notifications',array('host_name'=>$details['name'])), html::image($this->add_path('icons/16x16/notify.png'),array('alt' => _('View notifications for this host'), 'title' => _('View notifications for this host'))),array('style' => 'border: 0px'))
					?>
				</td>
			</tr>
			<?php }	}	?>
		</tbody>
	</table>
</div>
