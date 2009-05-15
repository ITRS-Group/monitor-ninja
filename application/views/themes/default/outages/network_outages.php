<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div align="center">
	<div class='dataTitle'>
		<?php echo $title ?>
	</div>

	<table border="0" class='data'>
		<tr>
		<th class='data'>
				<?php echo $label_severity ?>
			</th>
			<th class='data'>
				<?php echo $label_host ?>
			</th>
			<th class='data'>
				<?php echo $label_state ?>
			</th>
			<th class='data'>
				<?php echo $label_notes ?>
			</th>
			<th class='data'>
				<?php echo $label_duration ?>
			</th>
			<th class='data'>
				<?php echo $label_hosts_affected ?>
			</th>
			<th class='data'>
				<?php echo $label_services_affected ?>
			</th>
			<th class='data'>
				<?php echo $label_actions ?>
			</th>
		</tr>
		<?php
		if (!empty($outage_data)) {
			foreach ($outage_data as $host => $details) {
		?>
		<tr class='dataOdd'>
			<td class='dataOdd'>
				<?php echo $details['severity'] ?>
			</td>
			<td class='dataOdd'>
				<a href='extinfo.cgi?type=1&amp;host=switch1'><?php echo $host ?></a>
			</td>
			<td class='hostDOWN'>
				<?php echo Current_status_Model::status_text($details['current_state']) ?>
			</td>
			<td class='dataOdd'>
				<?php echo html::anchor('extinfo/details/host/'.$host, "<img src='/monitor/images/comment.gif' border='0' alt=
				'This host has ".$details['comments']." comments associated with it' title='This host has ".$details['comments']." comments associated with it' />") ?>
			</td>
			<td class='dataOdd'>
				<?php echo $details['duration'] ?>
			</td>
			<td class='dataOdd'>
				<?php echo $details['affected_hosts'] ?>
			</td>
			<td class='dataOdd'>
				<?php echo $details['affected_services'] ?>
			</td>
			<td class='dataOdd'>
				<?php echo html::anchor('status/host/'.$host, "<img src='/monitor/images/status2.gif' border='0' alt='View status detail for this host' title='View status detail for this host' />") ?>
				<?php echo html::anchor('statusmap/'.$host, "<img src='/monitor/images/status3.gif' border='0' alt='View status map for this host and its children' title='View status map for this host and its children' />") ?>
				<?php echo html::anchor('trends/host/'.$host, "<img src='/monitor/images/trends.gif' border='0' alt='View trends for this host'title='View trends for this host' />") ?>
				<?php echo html::anchor('history/host/'.$host, "<img src='/monitor/images/history.gif'border='0' alt='View alert history for this host' title='View alert history for this host' />") ?>
				<?php echo html::anchor('notifications/host/'.$host, "<img src='/monitor/images/notify.gif' border='0' alt='View notifications for this host' title='View notifications for this host' />") ?>
			</td>
		</tr>
		<?php
			}
		}
		?>
	</table>
</div>
