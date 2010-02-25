<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<br />
<div class="widget left w98">
	<h1><?php echo $title ?></h1>

	<?php if (!empty($host_data)) { ?>

	<h1><?php echo $host_title_str ?></h1>
	<?php echo html::anchor('command/submit?cmd_typ=SCHEDULE_HOST_DOWNTIME', html::image($this->add_path('icons/16x16/downtime.png')).' '.$host_link_text) ?>
	<table id="scheduled_host_downtime">
		<thead>
			<tr>
				<th><?php echo $label_host_name ?></th>
				<th><?php echo $label_entry_time ?></th>
				<th><?php echo $label_author ?></th>
				<th><?php echo $label_comment ?></th>
				<th><?php echo $label_start_time ?></th>
				<th><?php echo $label_end_time ?></th>
				<th><?php echo $label_type ?></th>
				<th><?php echo $label_duration ?></th>
				<th><?php echo $label_trigger_id ?></th>
				<th><?php echo $label_actions ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($host_data as $row) { ?>
		<tr>
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name) ?></td>
			<td><?php echo date($date_format, $row->entry_time) ?></td>
			<td><?php echo $row->author_name ?></td>
			<td><?php echo $row->comment_data ?></td>
			<td><?php echo date($date_format, $row->start_time) ?></td>
			<td><?php echo date($date_format, $row->end_time) ?></td>
			<td><?php echo $row->fixed ? $fixed : $flexible ?></td>
			<td><?php echo time::to_string($row->duration) ?></td>
			<td><?php echo empty($row->trigger_id) ? $na_str : $row->trigger_id ?></td>
			<td><?php echo html::anchor('command/submit?cmd_typ=DEL_HOST_DOWNTIME&downtime_id='.$row->downtime_id, html::image($this->add_path('icons/16x16/delete-comment.png'), array('alt' => $link_titlestring, 'title' => $link_titlestring))) ?></td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
	<br />
	<br />

	<?php }

	if (!empty($service_data)) { ?>
	<h1><?php echo $service_title_str ?></h1>
	<?php echo html::anchor('command/submit?cmd_typ=SCHEDULE_SVC_DOWNTIME', html::image($this->add_path('icons/16x16/downtime.png')).' '.$service_link_text) ?>
	<table id="scheduled_service_downtime">
		<thead>
			<tr>
				<th><?php echo $label_host_name ?></th>
				<th><?php echo $label_service ?></th>
				<th><?php echo $label_entry_time ?></th>
				<th><?php echo $label_author ?></th>
				<th><?php echo $label_comment ?></th>
				<th><?php echo $label_start_time ?></th>
				<th><?php echo $label_end_time ?></th>
				<th><?php echo $label_type ?></th>
				<th><?php echo $label_duration ?></th>
				<th><?php echo $label_trigger_id ?></th>
				<th><?php echo $label_actions ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($service_data as $row) { ?>
		<tr>
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name) ?></td>
			<td><?php echo html::anchor('extinfo/details/service/'.$row->host_name.'?service='.urlencode($row->service_description), $row->service_description) ?></td>
			<td><?php echo date($date_format, $row->entry_time) ?></td>
			<td><?php echo $row->author_name ?></td>
			<td><?php echo $row->comment_data ?></td>
			<td><?php echo date($date_format, $row->start_time) ?></td>
			<td><?php echo date($date_format, $row->end_time) ?></td>
			<td><?php echo $row->fixed ? $fixed : $flexible ?></td>
			<td><?php echo time::to_string($row->duration) ?></td>
			<td><?php echo empty($row->trigger_id) ? $na_str : $row->trigger_id ?></td>
			<td><?php echo html::anchor('command/submit?cmd_typ=DEL_SVC_DOWNTIME&downtime_id='.$row->downtime_id, html::image($this->add_path('icons/16x16/delete-comment.png'), array('alt' => $link_titlestring, 'title' => $link_titlestring))) ?></td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php } ?>
</div>