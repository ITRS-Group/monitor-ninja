<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w98" id="network_outages">
	<table id="network_outages_table">
		<!--<caption><?php echo $title ?></caption>-->
		<thead>
			<tr>
				<th><?php //echo $label_state ?>&nbsp;</th>
				<th><?php echo $label_severity ?></th>
				<th><?php echo $label_host ?></th>
				<th><?php echo $label_notes ?></th>
				<th><?php echo $label_duration ?></th>
				<th><?php echo $label_hosts_affected ?></th>
				<th><?php echo $label_services_affected ?></th>
				<th class="no-sort"><?php echo $label_actions ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (!empty($outage_data)) {
				foreach ($outage_data as $host => $details) {
			?>
			<tr>
				<td><?php echo Current_status_Model::status_text($details['current_state']) ?></td>
				<td><?php echo $details['severity'] ?></td>
				<td><a href='extinfo.cgi?type=1&amp;host=switch1'><?php echo $host ?></a></td>				
				<td>
					<?php echo html::anchor('extinfo/details/host/'.$host, "<img src='/monitor/images/comment.gif' border='0' alt=
					'This host has ".$details['comments']." comments associated with it' title='This host has ".$details['comments']." comments associated with it' />") ?>
				</td>
				<td><?php echo $details['duration'] ?></td>
				<td><?php echo $details['affected_hosts'] ?></td>
				<td><?php echo $details['affected_services'] ?></td>
				<td>
					<?php echo html::anchor('status/host/'.$host, "<img src='/monitor/images/status2.gif' border='0' alt='View status detail for this host' title='View status detail for this host' />") ?>
					<?php echo html::anchor('statusmap/'.$host, "<img src='/monitor/images/status3.gif' border='0' alt='View status map for this host and its children' title='View status map for this host and its children' />") ?>
					<?php echo html::anchor('trends/host/'.$host, "<img src='/monitor/images/trends.gif' border='0' alt='View trends for this host'title='View trends for this host' />") ?>
					<?php echo html::anchor('history/host/'.$host, "<img src='/monitor/images/history.gif'border='0' alt='View alert history for this host' title='View alert history for this host' />") ?>
					<?php echo html::anchor('notifications/host/'.$host, "<img src='/monitor/images/notify.gif' border='0' alt='View notifications for this host' title='View notifications for this host' />") ?>
				</td>
			</tr>
			<?php }	}	?>
		</tbody>
	</table>
</div>