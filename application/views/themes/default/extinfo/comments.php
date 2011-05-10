<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($command_result)) {
	echo "<br />";
	$img = $command_success ? 'shield-ok.png' : 'shield-not-ok.png';
	echo '<div id="comment_del_msg" class="widget w32 left">'.
		html::image($this->add_path('icons/16x16/'.$img, array('style' => 'margin-bottom: -4px'))).
		$command_result.'<br /></div>'."\n";
}
?>

<a name="comments"></a>
<div class="widget left w98">
<!--<div style="position: absolute; right: 0px; margin-right: 1%; margin-top: -20px">
	<?php echo html::image($this->add_path('icons/16x16/add-comment.png'), array('alt' => $label_add_comment, 'title' => $label_add_comment, 'style' => 'margin-bottom: -4px')) ?>
	<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_add_comment, $label_add_comment); ?>
	&nbsp; &nbsp;
	<?php echo html::image($this->add_path('icons/16x16/delete-comments.png'), array('alt' => $label_delete_all_comments, 'title' => $label_delete_all_comments, 'style' => 'margin-bottom: -4px')) ?>
	<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_delete_all_comments, $label_delete_all_comments); ?>
</div>-->
<a name="comments"></a>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<table class="comments_table">
		<caption>
			<?php echo (isset($label_title)) ? $label_title : $this->translate->_('Comments'); ?>:
			<?php echo html::image($this->add_path('icons/16x16/add-comment.png'), array('alt' => $label_add_comment, 'title' => $label_add_comment, 'style' => 'margin-bottom: -4px')) ?>
			<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_add_comment, $this->translate->_('Add comment'), array('style' => 'font-weight: normal')); ?>
			&nbsp;
			<?php echo html::image($this->add_path('icons/16x16/delete-comments.png'), array('alt' => $label_delete_all_comments, 'title' => $label_delete_all_comments, 'style' => 'margin-bottom: -4px')) ?>
			<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_delete_all_comments, $this->translate->_('Delete all'), array('style' => 'font-weight: normal')); ?>
			<?php if (Router::$method == 'show_comments') {
				echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_delete_<?php echo ($service == false ? 'host' : 'service') ?>" style="font-weight: normal"><?php echo $this->translate->_('Select Multiple Items') ?></a>
			<?php } ?>
		</caption>
		<thead>
			<tr>
			<?php if (Router::$method == 'show_comments') {
					echo form::open('extinfo/show_comments'); ?>
				<th class="td_<?php echo ($service == false ? 'host' : 'service') ?>_checkbox" style="display_none">
					<?php echo form::checkbox(array('name' => 'selectall_'.($service == false ? 'host' : 'service'), 'class' => 'selectall_'.($service == false ? 'host' : 'service')), ''); ?>
				</th>
				<th style="white-space: nowrap">
					<?php echo $label_host_name ?>
				</th>
				<?php if ($service) { ?>
				<th style="white-space: nowrap"><?php echo $label_service ?></th>
				<?php }
				}?>
				<th style="white-space: nowrap"><?php echo $label_entry_time ?></th>
				<th><?php echo $label_author ?></th>
				<th><?php echo $label_comment ?></th>
				<th style="white-space: nowrap"><?php echo $this->translate->_('ID') ?></th>
				<th><?php echo $label_persistent ?></th>
				<th><?php echo $label_type ?></th>
				<th colspan="2"><?php echo $label_expires ?></th>
				<!--<th style="widt: 25px" class="no-sort"><?php //echo $label_actions ?></th>-->
			</tr>
			</thead>
			<tbody>
	<?php
		if ($data!==false && !empty($data)) {
			$i=0;foreach ($data as $row) { $i++; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
			<?php if (Router::$method == 'show_comments') { ?>
				<td class="td_<?php echo ($service == false ? 'host' : 'service') ?>_checkbox" style="display_none"><?php echo form::checkbox(array('name' => 'del_'.$row['comment_type'].'[]', 'class' => 'deletecommentbox_'.($service == false ? 'host' : 'service')), $row['comment_id']); ?></td>
				<td style="white-space: nowrap"><?php echo html::anchor('extinfo/details/host/'.$row['host_name'], $row['host_name']) ?></td>
				<?php if (isset($row['service_description']) && !empty($row['service_description'])) { ?>
				<td style="white-space: normal"><?php echo html::anchor('extinfo/details/service/'.$row['host_name'].'?service='.urlencode($row['service_description']), $row['service_description']) ?></td>
				<?php }
				} ?>
				<td style="white-space: normal"><?php echo !empty($row['entry_time']) ? date($date_format_str, $row['entry_time']) : '' ?></td>
				<td style="white-space: normal"><?php echo $row['author_name'] ?></td>
				<td style="white-space:normal">
					<?php echo $row['comment']; ?>
				</td>
				<td><?php echo $row['comment_id'] ?></td>
				<td><?php
					if ($row['persistent'] === false) {
						echo $na_str;
					} else {
						echo $row['persistent'] ? $label_yes : $label_no;
					}
					?></td>
				<td style="white-space: normal">
					<?php	switch ($row['entry_type']) {
						case Comment_Model::USER_COMMENT:
							$entry_type = $label_type_user;
							break;
						case Comment_Model::DOWNTIME_COMMENT:
							$entry_type = $label_type_downtime;
							break;
						case Comment_Model::FLAPPING_COMMENT:
							$entry_type = $label_type_flapping;
							break;
						case Comment_Model::ACKNOWLEDGEMENT_COMMENT:
							$entry_type = $label_type_acknowledgement;
							break;
						default:
							$entry_type =  '?';
					}

					if ($row['comment_type'] == 'downtime') {
						$entry_type = $label_type_downtime.' ('.$label_type_user.')';
					}
					echo $entry_type; ?>
				</td>
				<td><?php echo $row['expires'] ? date($date_format_str, $row['expire_time']) : $na_str ?></td>
				<td class="icon">
			<?php 	if ($row['comment_type'] == 'downtime') {
						echo html::anchor('command/submit?cmd_typ='.$cmd_delete_downtime.'&downtime_id='.$row['comment_id'],
						html::image($this->add_path('icons/16x16/delete-downtime.png'), array('alt' => $label_delete_downtime, 'title' => $label_delete_downtime)),array('style' => 'border: 0px'));
					} else {
						echo html::anchor('command/submit?cmd_typ='.$cmd_delete_comment.'&com_id='.$row['comment_id'],
						html::image($this->add_path('icons/16x16/delete-comment.png'), array('alt' => $label_delete, 'title' => $label_delete)),array('style' => 'border: 0px'));
					} ?>
				</td>
			</tr>
			<?php	} } else { # print message - no comments available ?>
			<tr class="even">
				<td colspan="<?php echo $service ? 10 : 9 ?>"><?php echo $no_data ?></td>
			</tr>
		<?php }
		if (Router::$method == 'show_comments') {
			echo '<tr class="odd submit'.($service == false ? 'host' : 'service').'"><td colspan="'.($service ? 11 : 10).'">';
			echo form::submit(array('name' => 'del_submit'.($service == false ? 'host' : 'service')), $this->translate->_('Delete Selected'));
			echo '<span  class="'.($service == false ? 'host' : 'service').'_feedback"></span></td></tr>';
			echo form::close();
		}
		?>
		</tbody>
	</table>

</div>
