<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table id="filter_table">
	<tr>
		<th style="border: 1px solid #d0d0d0" colspan="2"><?php echo $header_title ?></th>
	</tr>
	<tr class="even">
		<td><?php echo $lable_host_status_types ?></td>
		<td><?php echo $host_status_type_val ?></td>
	</tr>
	<tr class="odd">
		<td><?php echo $lable_host_properties ?></td>
		<td><?php echo $hostprop_val ?></td>
	</tr>
	<tr class="even">
		<td><?php echo $lable_service_status_types ?></td>
		<td><?php echo $service_status_type_val ?></td>
	</tr>
	<tr class="odd">
		<td><?php echo $lable_service_properties ?></td>
		<td><?php echo $serviceprop_val ?></td>
	</tr>
</table>