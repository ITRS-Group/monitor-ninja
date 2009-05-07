<table border="0" width="100%">
	<tr>
		<td valign="top" align="left" width="33%"></td>
		<td valign="top" align="center" width="33%">
			<div align="center" class='statusTitle'>
				<?php echo $label_header ?>
			</div><br />
		</td>
		<td valign="top" align="right" width="33%"></td>
	</tr>
</table>

<?php
foreach ($group_details as $details) {
?>
<div align="center">
	<div class='status'>
		<?php echo html::anchor('status/servicegroup/'.$details->group_name.'?style=detail', html::specialchars($details->group_name)) ?>
		(<?php echo html::anchor('extinfo/details/'.$details->group_type.'group/'.$details->group_name, html::specialchars($details->group_name)) ?>)
	</div>
	<table border="1" class='status' align="center">
		<tr>
			<th class='status'>
				<?php echo $label_host ?>
			</th>
			<th class='status'>
				<?php echo $label_services ?>
			</th>
			<th class='status'>
				<?php echo $label_actions ?>
			</th>
		</tr>
		<?php
		foreach ($details->hosts as $host) {
			$host_class = false;
			switch ($host['current_state']) {
				case Current_status_Model::HOST_DOWN: case Current_status_Model::HOST_UNREACHABLE:
					$host_class = 'HOST'.Current_status_Model::status_text($host['current_state']);
					break;
				default:
					$host_class = 'Odd';
			} ?>
		<tr class='statusOdd'>
			<td class='statusOdd'>
				<table border="0" width='100%' cellpadding="0" cellspacing="0">
					<tr>
						<td align="left">
							<table border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="left" valign="center" class='status<?php echo $host_class ?>'>
										<?php echo html::anchor('extinfo/details/host/'.$host['host_name'], html::specialchars($host['host_name'])) ?>
										<?php 	if (!empty($host['icon_image'])) {
													echo html::anchor('extinfo/details/host/'.$host['host_name'], '<img src="'.$logos_path.$host['icon_image'].'" alt="'.$host['icon_image_alt'].'" title="'.$host['icon_image_alt'].'" width=20 height=20 align="right"');
												} ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
			<td class='statusOdd'>
			<?php	foreach	($details->services[$host['host_name']] as $service) {
						$service_class = 'status'.Current_status_Model::status_text($service['current_state'], 'service');
						echo html::anchor('extinfo/details/service/'.$host['host_name'].'/?service='.$service['service_description'], $service['service_description'], array('class' => $service_class)) ?>
			<?php 	} # end each service ?>
			</td>
			<?php
				# also each host, under Actions
			?>
			<td class='statusOdd'>
				<?php echo html::anchor('extinfo/host/'.$host['host_name'], html::image($icon_path.'detail.gif', array('alt' => $label_host_extinfo, 'title' => $label_host_extinfo))) ?>
				<?php echo html::anchor('status/host/'.$host['host_name'], html::image($icon_path.'status2.gif', array('alt' => $label_service_status, 'title' => $label_service_status))) ?>
				<?php echo html::anchor('statusmap/host/'.$host['host_name'], html::image($icon_path.'status3.gif', array('alt' => $label_status_map, 'title' => $label_status_map)));
				if (isset($nacoma_path)) {
					echo '<a href="'.$nacoma_path.'edit.php?obj_type=host&amp;host='.$host['host_name'].'" title="'.$label_nacoma.'">'.html::image($icon_path.'nacoma.png', array('alt' => $label_nacoma, 'title' => $label_nacoma)).'</a>';
				}
				if (isset($pnp_path)) {
					echo '<a href="'.$pnp_path.'index.php?host='.$host['host_name'].'" title="'.$label_pnp.'">'.html::image($icon_path.'graphlight.png', array('alt' => $label_pnp, 'title' => $label_pnp)).'</a>';
				} ?>
			</td>
		</tr><?php
		}	# end each host ?>
	</table>
</div>
<?php
}	# end each group
?>