<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table id="filter_table" style="width: auto" class="ext">
	<tr>
		<th colspan="2"><?php echo $header_title ?></th>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_host_status_types ?></td>
		<td style="white-space:normal"><?php echo $host_status_type_val ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_host_properties ?></td>
		<td style="white-space:normal"><?php echo $hostprop_val ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_service_status_types ?></td>
		<td style="white-space:normal"><?php echo $service_status_type_val ?></td>
	</tr>
	<tr>
		<td class="dark"><?php echo $lable_service_properties ?></td>
		<td style="white-space:normal"><?php echo $serviceprop_val ?></td>
	</tr>
</table>