<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="network_outages">
	<table id="network_outages_table">
		<caption><?php echo $title ?></caption>
		<thead>
			<tr>
				<th class="headerNone"><em>Status</em></th>
				<th class="headerNone"><?php echo _('Host') ?></th>
				<th class="headerNone" style="width: 57px"><?php echo _('Notes') ?></th>
				<th class="headerNone"><?php echo _('Severity') ?></th>
				<th class="headerNone"><?php echo _('State Duration') ?></th>
				<th class="headerNone"><?php echo _('# Hosts Affected') ?></th>
				<th class="headerNone"><?php echo _('# Services Affected') ?></th>
				<th class="headerNone"><?php echo _('Actions') ?></th>
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
				<td class="icon <?php echo strtolower(Current_status_Model::status_text($details['state'], $details['has_been_checked'])); ?>">
					<em><?php echo Current_status_Model::status_text($details['state'], $details['has_been_checked']) ?></em>
				</td>
				<td><?php echo html::anchor('extinfo/details/host/'.$details['name'], $details['name']) ?></td>
				<td class="icon">
					<?php echo $details['comments'] == 0 ? '' : html::anchor('extinfo/details/host/'.$details['name'], html::image($this->add_path('icons/16x16/add-comment.png'),array('alt' => _('View comments for this host'), 'title' => _('View comments for this host'))),array('style' => 'border: 0px')); ?>
				</td>
				<td><?php echo $details['severity'] ?></td>
				<td><?php echo time::to_string($details['duration']) ?></td>
				<td><?php echo $details['affected_hosts'] ?></td>
				<td><?php echo $details['affected_services'] ?></td>
				<td>
					<?php
						echo html::anchor('status/service/'.$details['name'], html::image($this->add_path('icons/16x16/service-details.gif'),array('alt' => _('View status detail for this host'), 'title' => _('View status detail for this host'))),array('style' => 'border: 0px')).'&nbsp;';
						if ( Kohana::config('nagvis.nagvis_path') ) {
							echo html::anchor('statusmap/host/'.$details['name'], html::image($this->add_path('icons/16x16/locate-host-on-map.png'),array('alt' => _('View status map for this host and its children'), 'title' => _('View status map for this host and its children'))),array('style' => 'border: 0px')).'&nbsp;';
						}
						echo html::anchor('trends/generate?host_name[]='.$details['name'], html::image($this->add_path('icons/16x16/trends.png'),array('alt' => _('View trends for this host'), 'title' => _('View trends for this host'))),array('style' => 'border: 0px')).'&nbsp;';
						echo html::anchor('alert_history/generate?host_name[]='.$details['name'], html::image($this->add_path('icons/16x16/alert-history.png'),array('alt' => _('View alert history for this host'), 'title' => _('View alert history for this host'))),array('style' => 'border: 0px')).'&nbsp;';
						echo html::anchor('notifications/host/'.$details['name'], html::image($this->add_path('icons/16x16/notify.png'),array('alt' => _('View notifications for this host'), 'title' => _('View notifications for this host'))),array('style' => 'border: 0px'))
					?>
				</td>
			</tr>
			<?php }	}	?>
		</tbody>
	</table>
</div>
