<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($command_result)) {
	echo "<br />";
	$img = $command_success ? 'shield-ok.png' : 'shield-not-ok.png';
	echo '<div id="comment_del_msg" class="widget w32 left">'.
		html::image($this->add_path('icons/16x16/'.$img, array('style' => 'margin-bottom: -4px'))).
		$command_result.'<br /></div>'."\n";
}
?>
<div class="widget left w98">

	<h2><?php echo $host_title_str ?></h2>
	<?php if (!empty($host_data)) { ?>
	<form action="">
		<?php
		echo form::input(array('id' => 'hostfilterbox_sched', 'style' => 'color:grey', 'class' => 'filterboxfield'), $filter_string);
		echo form::button(array('id' => 'clearhostsearch_sched', 'class' => 'clearbtn'), _('Clear'));
		?>
	</form>
	<?php } ?><br />

	<span style="float: right; margin-top: -30px"><?php echo html::anchor('command/submit?cmd_typ=SCHEDULE_HOST_DOWNTIME', html::image($this->add_path('icons/16x16/scheduled-downtime.png')), array('style' => 'border: 0px; float: left; margin-right: 5px;')).
				  html::anchor('command/submit?cmd_typ=SCHEDULE_HOST_DOWNTIME',$host_link_text).' &nbsp; ';
				  echo html::anchor('recurring_downtime', html::image($this->add_path('icons/16x16/recurring-downtime.png'), array('alt' => '', 'title' => 'Schedule recurring downtime')), array('style' => 'border: 0px')).' &nbsp;';
	echo html::anchor('recurring_downtime', 'Schedule recurring downtime').'&nbsp; ';
	if (!empty($host_data)) {
		echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_items" style="font-weight: normal"><?php echo _('Select Multiple Items') ?></a><?php
	}?>

				  <div style="clear:both"></div></span>



	<?php
	echo form::open('extinfo/scheduled_downtime', array('id' => 'del_host_downtime_form'));
	if (!empty($host_data)) {?>
	<table id="scheduled_host_downtime">
		<!--<caption>
			<?php echo $host_title_str ?>
		</caption>-->
		<thead>
			<tr>
				<th class="headerNone item_select" style="display:none">
					<?php echo form::checkbox(array('name' => 'selectall_host', 'class' => 'select_all_items'), ''); ?>
				</th>
				<th class="headerNone"><?php echo _('Host name') ?></th>
				<th class="headerNone"><?php echo _('Entry Time') ?></th>
				<th class="headerNone"><?php echo _('Author') ?></th>
				<th class="headerNone"><?php echo _('Comment') ?></th>
				<th class="headerNone"><?php echo _('Start time') ?></th>
				<th class="headerNone"><?php echo _('End time') ?></th>
				<th class="headerNone"><?php echo _('Type') ?></th>
				<th class="headerNone"><?php echo _('Duration') ?></th>
				<th class="headerNone"><?php echo _('Trigger ID') ?></th>
				<th class="headerNone" style="width: 45px"><?php echo _('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php $i=0; foreach ($host_data as $row) { $i++; ?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
			<td class="item_select" style="display:none;padding-left:7px"><?php echo form::checkbox(array('name' => 'del_host[]', 'class' => 'deletecommentbox_host'), $row->downtime_id); ?></td>
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name) ?></td>
			<td><?php echo date($date_format, $row->entry_time) ?></td>
			<td><?php echo $row->author_name ?></td>
			<td><?php echo $row->comment_data ?></td>
			<td><?php echo date($date_format, $row->start_time) ?></td>
			<td><?php echo date($date_format, $row->end_time) ?></td>
			<td><?php echo $row->fixed ? $fixed : $flexible ?></td>
			<td><?php echo time::to_string($row->duration) ?></td>
			<td><?php
		if(empty($row->triggered_by)) {
			echo _('N/A');
		} else {
			if(!empty($row->triggering_service)) {
				echo html::anchor('extinfo/details/service/'.$row->triggering_service, $row->triggering_service." (ID $row->triggered_by)");
			} elseif(!empty($row->triggering_host)) {
				echo html::anchor('extinfo/details/host/'.$row->triggering_host, $row->triggering_host." (ID $row->triggered_by)");
			} else {
				echo _('N/A');
			}
		} ?></td>
			<td style="text-align: center">
				<?php
					echo html::anchor('command/submit?cmd_typ=DEL_HOST_DOWNTIME&downtime_id='.$row->downtime_id, html::image($this->add_path('icons/16x16/delete-downtime.png'), array('alt' => $link_titlestring, 'title' => $link_titlestring)), array('style' => 'border: 0px')).' &nbsp;';
					echo html::anchor('recurring_downtime?host='.$row->host_name, html::image($this->add_path('icons/16x16/recurring-downtime.png'), array('alt' => '', 'title' => 'Schedule recurring downtime')), array('style' => 'border: 0px'));
				?>
			</td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
	echo '<div class="item_select" style="display:none">';
	echo form::submit(array('name' => 'del_submithost'), _('Delete Selected'));
	echo form::submit(array('id' => 'del_submithost_svc'), _('Delete for services too'));
	echo '<span class="host_feedback"></span>';
	echo '</div></form>'; ?>
	<br />
	<br />
	<?php
	} else { echo _('No hosts scheduled for downtime') . "<br/><br/>"; }

	echo '<h2>'.$service_title_str.'</h2>';

	if (!empty($service_data)) { ?>
	<form action="">
		<?php
		echo form::input(array('id' => 'servicefilterbox_sched', 'style' => 'color:grey', 'class' => 'filterboxfield'), $filter_string);
		echo form::button(array('id' => 'clearservicesearch_sched', 'class' => 'clearbtn'), _('Clear'));
		?>
	</form>
	<?php }
	echo "<br />";

	echo '<span style="float: right; margin-top: -30px; ">';

	echo html::anchor('command/submit?cmd_typ=SCHEDULE_SVC_DOWNTIME', html::image($this->add_path('icons/16x16/scheduled-downtime.png')), array('style' => 'border: 0px; float: left; margin-right: 5px;')).html::anchor('command/submit?cmd_typ=SCHEDULE_SVC_DOWNTIME',$service_link_text).' &nbsp; ';
	echo html::anchor('recurring_downtime', html::image($this->add_path('icons/16x16/recurring-downtime.png'), array('alt' => '', 'title' => 'Schedule recurring downtime')), array('style' => 'border: 0px')).' &nbsp;';
	echo html::anchor('recurring_downtime', 'Schedule recurring downtime').'&nbsp; ';
	if (!empty($service_data)) {
		echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_service_items" style="font-weight: normal"><?php echo _('Select Multiple Items') ?></a><?php
	} ?>
	</span>

	<?php
	echo form::open('extinfo/scheduled_downtime', array('id' => 'del_svc_downtime_form'));
	if (!empty($service_data)) {?>

	<table id="scheduled_service_downtime" style="margin-bottom: 15px">
		<!--<caption><?php echo $service_title_str ?></caption>-->
		<thead>
			<tr>
				<th class="headerNone item_select_service" style="display:none">
					<?php echo form::checkbox(array('name' => 'selectall_service', 'class' => 'select_all_items_service'), ''); ?>
				</th>
				<th class="headerNone"><?php echo _('Host name') ?></th>
				<th class="headerNone"><?php echo _('Service') ?></th>
				<th class="headerNone"><?php echo _('Entry Time') ?></th>
				<th class="headerNone"><?php echo _('Author') ?></th>
				<th class="headerNone"><?php echo _('Comment') ?></th>
				<th class="headerNone"><?php echo _('Start time') ?></th>
				<th class="headerNone"><?php echo _('End time') ?></th>
				<th class="headerNone"><?php echo _('Type') ?></th>
				<th class="headerNone"><?php echo _('Duration') ?></th>
				<th class="headerNone"><?php echo _('Trigger ID') ?></th>
				<th class="headerNone" style="width: 45px"><?php echo _('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php $i = 0; foreach ($service_data as $row) { $i++; ?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
			<td class="item_select_service" style="display:none;padding-left:7px"><?php echo form::checkbox(array('name' => 'del_service[]', 'class' => 'deletecommentbox_service'), $row->downtime_id); ?></td>
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name) ?></td>
			<td><?php echo html::anchor('extinfo/details/service/'.$row->host_name.'?service='.urlencode($row->service_description), $row->service_description) ?></td>
			<td><?php echo date($date_format, $row->entry_time) ?></td>
			<td><?php echo $row->author_name ?></td>
			<td><?php echo $row->comment_data ?></td>
			<td><?php echo date($date_format, $row->start_time) ?></td>
			<td><?php echo date($date_format, $row->end_time) ?></td>
			<td><?php echo $row->fixed ? $fixed : $flexible ?></td>
			<td><?php echo time::to_string($row->duration) ?></td>
			<td><?php
			if(empty($row->triggered_by)) {
				echo _('N/A');
			} else {
				if(!empty($row->triggering_service)) {
					echo html::anchor('extinfo/details/service/'.$row->triggering_service, $row->triggering_service." (ID $row->triggered_by)");
				} elseif(!empty($row->triggering_host)) {
					echo html::anchor('extinfo/details/host/'.$row->triggering_host, $row->triggering_host." (ID $row->triggered_by)");
				} else {
					echo _('N/A');
				}
			} ?></td>
			<td style="text-align: center">
				<?php
					echo html::anchor('command/submit?cmd_typ=DEL_SVC_DOWNTIME&downtime_id='.$row->downtime_id, html::image($this->add_path('icons/16x16/delete-downtime.png'), array('alt' => $link_titlestring, 'title' => $link_titlestring)), array('style' => 'border: 0px')).' &nbsp;';
					echo html::anchor('recurring_downtime?host='.$row->host_name.'&service='.urlencode($row->service_description), html::image($this->add_path('icons/16x16/recurring-downtime.png'), array('alt' => '', 'title' => 'Schedule recurring downtime')), array('style' => 'border: 0px'));
				?>
			</td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
	echo '<div class="item_select_service" style="display:none">';
	echo form::submit(array('name' => 'del_submitservice'), _('Delete Selected'));
	echo '<span  class="service_feedback"></span>';
	echo '</div>'; ?>
	<?php } else { echo _('No services scheduled for downtime'); }
	echo form::close(); ?>
</div>
