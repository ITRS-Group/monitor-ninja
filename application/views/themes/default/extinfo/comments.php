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
	<form action="">
	<?php
	echo form::input(array('id' => ($service == false ? 'host' : 'service').'filterbox', 'style' => 'color:grey', 'class' => 'filterboxfield'), $filter_string);
	echo form::button('clear'.($service == false ? 'host' : 'service').'search', _('Clear'));
	?>
	</form>
<!--<div style="position: absolute; right: 0px; margin-right: 1%; margin-top: -20px">
	<?php echo html::image($this->add_path('icons/16x16/add-comment.png'), array('alt' => $label_add_comment, 'title' => $label_add_comment, 'style' => 'margin-bottom: -4px')) ?>
	<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_add_comment, $label_add_comment); ?>
	&nbsp; &nbsp;
	<?php echo html::image($this->add_path('icons/16x16/delete-comments.png'), array('alt' => _('Delete all comments'), 'title' => _('Delete all comments'), 'style' => 'margin-bottom: -4px')) ?>
	<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_delete_all_comments, _('Delete all comments')); ?>
</div>-->
<a name="comments"></a>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<?php if (Router::$method == 'show_comments') { echo form::open('extinfo/show_comments'); } ?>
	<table class="comments_table" id="<?php echo ($service == false ? 'host' : 'service') ?>comments_table">
		<caption>
			<?php echo (isset($label_title)) ? $label_title : _('Comments'); ?>:
			<?php echo html::image($this->add_path('icons/16x16/add-comment.png'), array('alt' => $label_add_comment, 'title' => $label_add_comment, 'style' => 'margin-bottom: -4px')) ?>
			<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_add_comment, _('Add comment'), array('style' => 'font-weight: normal')); ?>
			&nbsp;
			<?php echo html::image($this->add_path('icons/16x16/delete-comments.png'), array('alt' => _('Delete all comments'), 'title' => _('Delete all comments'), 'style' => 'margin-bottom: -4px')) ?>
			<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_delete_all_comments, _('Delete all'), array('style' => 'font-weight: normal')); ?>
			<?php if (Router::$method == 'show_comments') {
				echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple<?php echo ($service == false ? '' : '_service') ?>_items" style="font-weight: normal"><?php echo _('Select Multiple Items') ?></a>
			<?php } ?>
		</caption>
		<thead>
			<tr>
			<?php if (Router::$method == 'show_comments') { ?>
				<th class="item_select<?php echo ($service == false ? '' : '_service') ?>">
					<?php echo form::checkbox(array('name' => 'selectall_'.($service == false ? 'host' : 'service'), 'class' => 'select_all_items'.($service == false ? '' : '_service')), ''); ?>
				</th>
				<th style="white-space: nowrap">
					<?php echo _('Host name') ?>
				</th>
				<?php if ($service) { ?>
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
				<!--<th style="widt: 25px" class="no-sort"><?php //echo _('Actions') ?></th>-->
			</tr>
			</thead>
			<tbody>
	<?php
		if ($data!==false && !empty($data)) {
			$i=0;foreach ($data as $row) { $i++; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
			<?php if (Router::$method == 'show_comments') { ?>
				<td class="item_select<?php echo ($service == false ? '' : '_service') ?>"><?php echo form::checkbox(array('name' => 'del_'.$row['comment_type'].'[]', 'class' => 'deletecommentbox_'.($service == false ? 'host' : 'service')), $row['comment_id']); ?></td>
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
						echo _('N/A');
					} else {
						echo $row['persistent'] ? _('YES') : _('NO');
					}
					?></td>
				<td style="white-space: normal">
					<?php	switch ($row['entry_type']) {
						case Comment_Model::USER_COMMENT:
							$entry_type = _('User');
							break;
						case Comment_Model::DOWNTIME_COMMENT:
							$entry_type = _('Scheduled downtime');
							break;
						case Comment_Model::FLAPPING_COMMENT:
							$entry_type = _('Flap detection');
							break;
						case Comment_Model::ACKNOWLEDGEMENT_COMMENT:
							$entry_type = _('Acknowledgement');
							break;
						default:
							$entry_type =  '?';
					}

					if ($row['comment_type'] == 'downtime') {
						$entry_type = _('Scheduled downtime').' ('._('User').')';
					}
					echo $entry_type; ?>
				</td>
				<td><?php echo $row['expires'] ? date($date_format_str, $row['expire_time']) : _('N/A') ?></td>
				<td class="icon">
			<?php 	if ($row['comment_type'] == 'downtime') {
						echo html::anchor('command/submit?cmd_typ='.$cmd_delete_downtime.'&downtime_id='.$row['comment_id'],
						html::image($this->add_path('icons/16x16/delete-downtime.png'), array('alt' => _('Delete this downtime'), 'title' => _('Delete this downtime'))),array('style' => 'border: 0px'));
					} else {
						echo html::anchor('command/submit?cmd_typ='.$cmd_delete_comment.'&com_id='.$row['comment_id'],
						html::image($this->add_path('icons/16x16/delete-comment.png'), array('alt' => _('Delete this comment'), 'title' => _('Delete this comment'))),array('style' => 'border: 0px'));
					} ?>
				</td>
			</tr>
			<?php	} } else { # print message - no comments available ?>
			<tr class="even">
				<td colspan="<?php echo $service ? 10 : 9 ?>"><?php echo $no_data ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
		if (Router::$method == 'show_comments') {
			echo '<div class="item_select'.($service == false ? '' : '_service').'">';
			echo form::submit(array('name' => 'del_submit'.($service == false ? 'host' : 'service')), _('Delete Selected'));
			echo '</div>';
			echo form::close();
		}
	?>
</div>
