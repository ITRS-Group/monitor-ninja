<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
	<table border="0" cellspacing="2" cellpadding="0" id="filter_table">
		<tr>
			<td colspan="2" valign="top" align="left" class='filterTitle'>
				<?php echo $header_title ?>:
			</td>
		</tr>

		<tr>
			<td valign="top" align="left" class='filterName'>
			<?php echo $lable_host_status_types ?>:
			</td>
			<td valign="top" align="left" class='filterValue'>
				<?php echo $host_status_type_val ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="left" class='filterName'>
				<?php echo $lable_host_properties ?>:
			</td>
			<td valign="top" align="left" class='filterValue'>
				<?php echo $hostprop_val ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="left" class='filterName'>
				<?php echo $lable_service_status_types ?>:
			</td>
			<td valign="top" align="left" class='filterValue'>
				<?php echo $service_status_type_val ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="left" class='filterName'>
				<?php echo $lable_service_properties ?>:
			</td>
			<td valign="top" align="left" class='filterValue'>
				<?php echo $serviceprop_val ?>
			</td>
		</tr>
	</table>