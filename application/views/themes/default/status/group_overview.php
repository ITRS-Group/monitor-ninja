<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>
<!--<div class="widget collapsable left w98" id="status_service">
<div class="widget-header"><?php echo $lable_header ?></div>-->


<?php
	foreach ($group_details as $group) { ?>
	<div class="widget left w98" id="status_<?php echo $group->groupname; ?>">
		<div class="widget-header">
			<a href='status.cgi?servicegroup=<?php echo $group->groupname ?>&amp;style=detail'><?php echo $group->group_alias ?></a>
			(<a href='extinfo.cgi?type=8&amp;servicegroup=<?php echo $group->groupname ?>'><?php echo $group->groupname ?></a>)
		</div>
		<table>
			<tr>
				<th class="no-sort">&nbsp;</th>
				<th class="headerSortDown" colspan="2" style="width: 200px"><?php echo $lable_host ?></th>
				<th class="header"><?php echo $lable_services ?></th>
				<th class="header" style="width: 100px"><?php echo $lable_actions ?></th>
			</tr>
			<?php $i=0; if (!empty($group->hostinfo))
				foreach ($group->hostinfo as $host => $details) { ?>
			<tr class="<?php echo ($i % 2 == 0) ? 'odd' : 'even' ?>">
				<td class="icon bl"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower($details['state_str']).'.png', array('alt' => $details['state_str'], 'title' => $details['state_str'])); ?></td>
				<td><?php echo $details['status_link'] ?></td>
				<td class="icon"><?php echo !empty($details['host_icon']) ? $details['host_icon'] : '' ?></td>
				<td>
					<?php if (!empty($group->service_states[$host]))
							foreach ($group->service_states[$host] as $svc_state) {
								echo $svc_state['status_link'].'<br />';
							}
					?>
				</td>
				<td>
					<?php echo $svc_state['extinfo_link'] ?>
					<?php echo $svc_state['svc_status_link'] ?>
					<?php echo $svc_state['statusmap_link'] ?>
					<?php echo !empty($details['notes_link']) ? $details['notes_link'] : '' ?>
					<?php echo !empty($details['action_link']) ? $details['action_link'] : '' ?>

					<?php echo !empty($svc_state['nacoma_link']) ? $svc_state['nacoma_link'] : '' ?>
					<?php echo !empty($svc_state['pnp_link']) ? $svc_state['pnp_link'] : '' ?>
				</td>
			</tr>
				<?php $i++; 	} ?>
		</table>
	</div>
	<?php } ?>
<!--</div>-->
