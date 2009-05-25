<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>
<?php //echo $lable_header ?>
<div class="widget left w98" id="status_group-overview">
<div id="page_links">
	<?php
	if (isset($page_links)) {
		foreach ($page_links as $label => $link) {
			?>
			<li><?php echo html::anchor($link, $label) ?></li>
			<?php
		}
	}
	?>
</div>
<?php
	$j = 0;
	foreach ($group_details as $group) { ?>

		<table style="table-layout: fixed" class="group_overview_table">
			<caption>
			<?php echo html::anchor('status/'.$grouptype.'group/'.$group->groupname.'?style=detail', $group->group_alias) ?>
			(<?php echo html::anchor('extinfo/details/'.$grouptype.'group/'.$group->groupname, $group->groupname) ?>)
		</caption>
			<colgroup>
				<col style="width: 30px" />
				<col style="width: 200px" />
				<col style="width: 30px" />
				<col style="width: 100%" />
				<col style="width: 30px" />
				<col style="width: 30px" />
				<col style="width: 30px" />
				<col style="width: 30px" />
				<col style="width: 30px" />
				<col style="width: 30px" />
			</colgroup>
			<thead>
			<tr>
				<th>&nbsp;</th>
				<th colspan="2"><?php echo $lable_host ?></th>
				<th class="no-sort"><?php echo $lable_services ?></th>
				<th class="no-sort" colspan="6"><?php echo $lable_actions ?></th>
			</tr>
			</thead>
			<tbody>
			<?php $i=0; if (!empty($group->hostinfo))
				foreach ($group->hostinfo as $host => $details) { ?>
			<tr class="<?php echo ($i % 2 == 0) ? 'even' : 'odd' ?>">
				<td class="icon bl"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-'.strtolower($details['state_str']).'.png', array('alt' => $details['state_str'], 'title' => $details['state_str'])); ?></td>
				<td><?php echo $details['status_link'] ?></td>
				<td class="icon"><?php echo !empty($details['host_icon']) ? $details['host_icon'] : '' ?></td>
				<td>
					<?php if (!empty($group->service_states[$host]))
						//print_r($svc_state);
						foreach ($group->service_states[$host] as $svc_state) {
							echo html::image('/application/views/themes/default/icons/12x12/shield-'.strtolower(str_replace('miniStatus','',$svc_state['class_name'])).'.png', array('alt' => strtolower(str_replace('miniStatus','',$svc_state['class_name'])), 'title' => strtolower(str_replace('miniStatus','',$svc_state['class_name'])), 'style' => 'margin-bottom: -2px'));
							echo '&nbsp; '.strtolower(ucfirst($svc_state['status_link'])).' &nbsp; ';
						}
					?>
				</td>
				<td class="icon"><?php echo !empty($svc_state['pnp_link']) ? $svc_state['pnp_link'] : '' ?></td>
				<td class="icon"><?php echo $svc_state['extinfo_link'] ?></td>
				<td class="icon"><?php echo $svc_state['statusmap_link'] ?></td>
				<td class="icon"><?php echo $svc_state['svc_status_link'] ?></td>
				<td class="icon">
					<?php echo !empty($details['notes_link']) ? $details['notes_link'] : '' ?>
					<?php echo !empty($details['action_link']) ? $details['action_link'] : '' ?>
				</td>
				<td class="icon"><?php echo !empty($svc_state['nacoma_link']) ? $svc_state['nacoma_link'] : '' ?></td>
			</tr>
			<?php $i++; } ?>
			</tbody>
		</table>

<?php $j++; } ?>
</div>