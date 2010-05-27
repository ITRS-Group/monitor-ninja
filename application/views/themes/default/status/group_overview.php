<?php defined('SYSPATH') OR die('No direct access allowed.');?>
<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
<div class="widget left w32" id="page_links">
		<ul>
		<li><?php echo $this->translate->_('View').', '.$label_view_for.':'; ?></li>
		<?php
		if (isset($page_links)) {
			foreach ($page_links as $label => $link) {
				?>
				<li><?php echo html::anchor($link, $label) ?></li>
				<?php
			}
		}
		?>
		</ul>
	</div>
	<?php
	if (!empty($widgets)) {
		foreach ($widgets as $widget) {
			echo $widget;
		}
	}
	?>

	<div id="filters" class="left">
	<?php
	if (isset($filters) && !empty($filters)) {
		echo $filters;
	}
	?>
	</div>
</div>

<div class="widget left w98" id="status_group-overview">
<?php if (nacoma::link()===true)
	echo sprintf($this->translate->_('Add new %sgroup'), ucfirst($grouptype)).': &nbsp;'.nacoma::link('configuration/configure/'.$grouptype.'group/', 'icons/16x16/nacoma.png', sprintf($this->translate->_('Add new %sgroup'), $grouptype));?>
<?php
	$j = 0;
	# make sure we have something to iterate over
	if (!empty($group_details))
	foreach ($group_details as $group) { ?>

		<table class="group_overview_table">
			<caption>
			<?php echo html::anchor('status/'.$grouptype.'group/'.$group->groupname.'?style=detail', $group->group_alias) ?>
			(<?php echo html::anchor('extinfo/details/'.$grouptype.'group/'.$group->groupname, $group->groupname) ?>)
			<?php if (nacoma::link()===true)
				echo nacoma::link('configuration/configure/'.$grouptype.'group/'.urlencode($group->groupname), 'icons/16x16/nacoma.png', sprintf($this->translate->_('Configure this %sgroup'), $grouptype));?>
		</caption>
			<thead>
			<tr>
				<th>&nbsp;</th>
				<th colspan="2"><?php echo $lable_host ?></th>
				<th class="no-sort"><?php echo $lable_services ?></th>
				<th class="no-sort"><?php echo $lable_actions ?></th>
			</tr>
			</thead>
			<tbody>
			<?php $i=0; if (!empty($group->hostinfo))
				foreach ($group->hostinfo as $host => $details) { ?>
			<tr class="<?php echo ($i % 2 == 0) ? 'even' : 'odd' ?>">
				<td class="icon bl <?php echo strtolower($details['state_str']); ?>">&nbsp;</td>
				<td style="width: 180px"><?php echo $details['status_link'] ?></td>
				<td class="icon"><?php echo !empty($details['host_icon']) ? $details['host_icon'] : '' ?></td>
				<td>
					<?php if (!empty($group->service_states[$host]))
						//print_r($svc_state);
						foreach ($group->service_states[$host] as $svc_state) {
							echo html::image($this->add_path('icons/12x12/shield-'.strtolower(str_replace('miniStatus','',$svc_state['class_name'])).'.png'), array('alt' => strtolower(str_replace('miniStatus','',$svc_state['class_name'])), 'title' => strtolower(str_replace('miniStatus','',$svc_state['class_name'])), 'style' => 'margin-bottom: -2px'));
							echo '&nbsp; '.strtolower(ucfirst($svc_state['status_link'])).' &nbsp; ';
						}
					?>
				</td>
				<td class="icon" style="text-align: left">
					<?php
						echo !empty($svc_state['nacoma_link']) ? $svc_state['nacoma_link'].'&nbsp;' : '';
						echo !empty($svc_state['pnp_link']) ? $svc_state['pnp_link'].'&nbsp;' : '';
						echo $svc_state['extinfo_link'].'&nbsp;';
						echo $svc_state['statusmap_link'].'&nbsp;';
						echo $svc_state['svc_status_link'].'&nbsp;';
						echo !empty($details['action_link']) ? $details['action_link'].'&nbsp;' : '';
						echo !empty($details['notes_link']) ? $details['notes_link'].'&nbsp;' : '';
					?>
				</td>
			</tr>
			<?php $i++; } ?>
			</tbody>
		</table>

<?php $j++; }
	else { ?>
		<table class="group_overview_table">
			<thead>
			<tr>
				<th>&nbsp;</th>
				<th><?php echo $lable_host ?></th>
				<th class="no-sort"><?php echo $lable_services ?></th>
				<th class="no-sort"><?php echo $lable_actions ?></th>
			</tr>
			</thead>
			<tbody>
			<tr class="even">
				<td colspan="4"><?php echo $error_message ?></td>
			</tr>
			</tbody>
		</table>

<?php } ?>
</div>