<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<br />

<h1><?php echo $title ?></h1>

<?php if (!empty($host_data)) { ?>

<h1><?php echo $host_title_str ?></h1>

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
		<td><?php echo $row->host_name ?></td>
		<td><?php echo date($date_format, $row->entry_time) ?></td>
		<td><?php echo $row->author_name ?></td>
		<td><?php echo $row->comment_data ?></td>
		<td><?php echo date($date_format, $row->start_time) ?></td>
		<td><?php echo date($date_format, $row->end_time) ?></td>
		<td><?php echo $row->fixed ? $fixed : $flexible ?></td>
		<td><?php echo time::to_string($row->duration) ?></td>
		<td><?php echo empty($row->trigger_id) ? $na_str : $row->trigger_id ?></td>
		<td>IMAGE + LINK HERE</td> <!-- link to delete/cancel downtime + icon -->
	</tr>
	<?php } ?>
	</tbody>
</table>
<br />
<br />

<?php }

if (!empty($service_data)) { ?>
<h1><?php echo $service_title_str ?></h1>

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
		<td><?php echo $row->host_name ?></td>
		<td><?php echo $row->service_description ?></td>
		<td><?php echo date($date_format, $row->entry_time) ?></td>
		<td><?php echo $row->author_name ?></td>
		<td><?php echo $row->comment_data ?></td>
		<td><?php echo date($date_format, $row->start_time) ?></td>
		<td><?php echo date($date_format, $row->end_time) ?></td>
		<td><?php echo $row->fixed ? $fixed : $flexible ?></td>
		<td><?php echo time::to_string($row->duration) ?></td>
		<td><?php echo empty($row->trigger_id) ? $na_str : $row->trigger_id ?></td>
		<td>IMAGE + LINK HERE</td> <!-- link to delete/cancel downtime + icon -->
	</tr>
	<?php } ?>
	</tbody>
</table>
<?php } ?>