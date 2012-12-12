<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($command_result)) {
	echo "<br />";
	$img = $command_success ? 'shield-ok.png' : 'shield-not-ok.png';
	echo '<div id="comment_del_msg" class="widget w32 left">'.
		html::image($this->add_path('icons/16x16/'.$img, array('style' => 'margin-bottom: -4px'))).
		$command_result.'<br /></div>'."\n";
}

?>

<div>
	<form action="">
	<?php
	echo form::input(array('id' => $type.'filterbox', 'style' => 'color:grey', 'class' => 'filterboxfield'), $filter_string);
	echo form::button('clear'.$type.'search', _('Clear'));
	?>
	</form>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<?php if (Router::$method == 'show_comments') { echo form::open('extinfo/show_comments'); } ?>
	<table class="comments_table" id="<?php echo $type ?>comments_table">
		<caption>
			<?php echo (isset($label_title)) ? $label_title : _('Comments'); ?>:
			<?php echo html::image($this->add_path('icons/16x16/add-comment.png'), array('alt' => $label_add_comment, 'title' => $label_add_comment, 'style' => 'margin-bottom: -4px')) ?>
			<?php echo html::anchor('command/submit?host='.(isset($host)?$host:'').'&service='.(isset($service)?urlencode($service):'').'&cmd_typ='.$cmd_add_comment, _('Add comment'), array('style' => 'font-weight: normal')); ?>
			&nbsp;
			<?php echo html::image($this->add_path('icons/16x16/delete-comments.png'), array('alt' => _('Delete all comments'), 'title' => _('Delete all comments'), 'style' => 'margin-bottom: -4px')) ?>
			<?php echo html::anchor('command/submit?host='.(isset($host)?$host:'').'&service='.(isset($service)?urlencode($service):'').'&cmd_typ='.$cmd_delete_all_comments, _('Delete all'), array('style' => 'font-weight: normal')); ?>
			<?php if (Router::$method == 'show_comments') {
				echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple<?php echo ($type == 'service' ? '_service' : '') ?>_items" style="font-weight: normal"><?php echo _('Select Multiple Items') ?></a>
			<?php } ?>
		</caption>
		<thead>
			<tr>
			<?php if (Router::$method == 'show_comments') { ?>
				<th class="item_select<?php echo ($type == 'service' ? '_service' : '') ?>">
					<?php echo form::checkbox(array('name' => 'selectall_'.$type, 'class' => 'select_all_items'.($type == 'service' ? '_service' : '')), ''); ?>
				</th>
				<th style="white-space: nowrap">
					<?php echo _('Host name') ?>
				</th>
				<?php if ($type == 'service') { ?>
				<th style="white-space: nowrap"><?php echo _('Service') ?></th>
				<?php }
				}?>
				<th style="white-space: nowrap"><?php echo _('Entry Time') ?></th>
				<th><?php echo _('Author') ?></th>
				<th><?php echo _('Comment') ?></th>
				<th style="white-space: nowrap"><?php echo _('ID') ?></th>
				<th><?php echo _('Persistent') ?></th>
				<th><?php echo _('Type') ?></th>
				<th colspan="2"><?php echo _('Expires') ?></th>
			</tr>
			</thead>
			<tbody>
	<?php
		if ($data!==false && !empty($data)) {
			$i=0;foreach ($data as $row) { $i++; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
			<?php if (Router::$method == 'show_comments') { ?>
				<td class="item_select<?php echo ($type == 'service' ? '_service' : '') ?>"><?php echo form::checkbox(array('name' => 'del_'.$row['comment_type'].'[]', 'class' => 'deletecommentbox_'.$type), $row['id']); ?></td>
				<td style="white-space: nowrap"><?php echo html::anchor('extinfo/details/host/'.$row['host_name'], $row['host_name']) ?></td>
				<?php if ($type == 'service') { ?>
				<td style="white-space: normal"><?php echo html::anchor('extinfo/details/service/'.$row['host_name'].'?service='.urlencode($row['service_description']), $row['service_description']) ?></td>
				<?php }
				} ?>
				<td style="white-space: normal"><?php echo !empty($row['entry_time']) ? date($date_format_str, $row['entry_time']) : '' ?></td>
				<td style="white-space: normal"><?php echo $row['author'] ?></td>
				<td style="white-space:normal">
					<?php echo security::xss_clean($row['comment']); ?>
				</td>
				<td><?php echo $row['id'] ?></td>
				<td><?php
					if (!isset($row['persistent'])) {
						echo _('N/A');
					} else {
						echo $row['persistent'] ? _('YES') : _('NO');
					}
					?></td>
				<td style="white-space: normal">
					<?php
					if (!isset($row['entry_type'])) { // XXX: This could be untrue, if the caller has excluded this column
						$entry_type = _('Scheduled downtime').' ('._('User').')';
					} else {
						switch ($row['entry_type']) {
						 case Old_Comment_Model::USER_COMMENT:
							$entry_type = _('User');
							break;
						 case Old_Comment_Model::DOWNTIME_COMMENT:
							$entry_type = _('Scheduled downtime');
							break;
						 case Old_Comment_Model::FLAPPING_COMMENT:
							$entry_type = _('Flap detection');
							break;
						 case Old_Comment_Model::ACKNOWLEDGEMENT_COMMENT:
							$entry_type = _('Acknowledgement');
							break;
						 default:
							$entry_type =  '?';
						}
					}

					echo $entry_type; ?>
				</td>
				<td><?php
					if (isset($row['expires'])) { // a comment
						echo $row['expires'] ? date($date_format_str, $row['expire_time']) : _('N/A');
					} else {
						echo $row['end_time'] ? date($date_format_str, $row['end_time']) : _('N/A');
					}
				?></td>
				<td class="icon">
			<?php 	if ($row['comment_type'] == 'downtime') {
						echo html::anchor('command/submit?cmd_typ='.$cmd_delete_downtime.'&downtime_id='.$row['id'],
						html::image($this->add_path('icons/16x16/delete-downtime.png'), array('alt' => _('Delete this downtime'), 'title' => _('Delete this downtime'))),array('style' => 'border: 0px'));
					} else {
						echo html::anchor('command/submit?cmd_typ='.$cmd_delete_comment.'&com_id='.$row['id'],
						html::image($this->add_path('icons/16x16/delete-comment.png'), array('alt' => _('Delete this comment'), 'title' => _('Delete this comment'))),array('style' => 'border: 0px'));
					} ?>
				</td>
			</tr>
			<?php	} } else { # print message - no comments available ?>
			<tr class="even">
				<td colspan="<?php echo $type == 'service' ? 10 : 9 ?>"><?php echo $no_data ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
		if (Router::$method == 'show_comments') {
			echo '<div class="item_select'.($type == 'service' ? '_service' : '').'">';
			echo form::submit(array('name' => 'del_submit'.$type), _('Delete Selected'));
			echo '</div>';
			echo form::close();
		}
	?>
</div>
