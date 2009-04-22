<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div align="center" class='statusTitle'>
	<?php echo $lable_header ?>
</div>

<div align="center">
<?php
	foreach ($group_details as $group) { ?>
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td align="center">
				<div class='status'>
					<a href='status.cgi?servicegroup=<?php echo $group->groupname ?>&amp;style=detail'><?php echo $group->group_alias ?></a>
					(<a href='extinfo.cgi?type=8&amp;servicegroup=<?php echo $group->groupname ?>'><?php echo $group->groupname ?></a>)
				</div>

				<div class='status'>
					<table border="1" cellpadding="2" cellspacing="0" class='status'>
						<tr>
							<th class='status'>
								<?php echo $lable_host ?>
							</th>
							<th class='status'>
								<?php echo $lable_status ?>
							</th>
							<th class='status'>
								<?php echo $lable_services ?>
							</th>
							<th class='status'>
								<?php echo $lable_actions ?>
							</th>
						</tr>
				<?php if (!empty($group->hostinfo))
						foreach ($group->hostinfo as $host => $details) { ?>
						<tr class='statusEven'>
							<td class='statusEven'>
								<table border="0" width="100%" cellpadding="0" cellspacing="0">
									<tr class='statusEven'>
										<td class='statusEven'>
											<?php echo $details['status_link'] ?>
											<?php echo !empty($details['host_icon']) ? $details['host_icon'] : '' ?>
										</td>
									</tr>
								</table>
							</td>
							<td class='<?php echo $details['class_name'] ?>'>
								<?php echo $details['state_str'] ?>
							</td>
							<td class='statusEven'>
								<table border="0" width="100%">
							<?php if (!empty($group->service_states[$host]))
									foreach ($group->service_states[$host] as $svc_state) {	?>
									<tr>
										<td class='<?php echo $svc_state['class_name'] ?>'>
											<?php echo $svc_state['status_link'] ?>
										</td>
									</tr>
								<?php } ?>
								</table>
							</td>
							<td valign="center" class='statusEven'>
								<?php echo $svc_state['extinfo_link'] ?>
								<?php echo !empty($details['notes_link']) ? $details['notes_link'] : '' ?>
								<?php echo !empty($details['action_link']) ? $details['action_link'] : '' ?>
								<?php echo $svc_state['svc_status_link'] ?>
								<?php echo $svc_state['statusmap_link'] ?>
								<?php echo !empty($svc_state['nacoma_link']) ? $svc_state['nacoma_link'] : '' ?>
								<?php echo !empty($svc_state['pnp_link']) ? $svc_state['pnp_link'] : '' ?>
								</a>
							</td>
						</tr>
				<?php 	} ?>
					</table>
				</div>
			</td>
		</tr>
	</table>
	<?php } ?>
</div>
