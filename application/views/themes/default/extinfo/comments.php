<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>



<div class="widget left w98">

<div style="position: absolute; right: 0px; margin-right: 1%">
	<?php echo html::image('application/views/themes/default/icons/16x16/add-comment.png', array('alt' => $label_add_comment, 'title' => $label_add_comment, 'style' => 'margin-bottom: -4px')) ?>
	<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_add_comment, $label_add_comment); ?>
	&nbsp; &nbsp;
	<?php echo html::image('application/views/themes/default/icons/16x16/delete-comments.png', array('alt' => $label_delete_all_comments, 'title' => $label_delete_all_comments, 'style' => 'margin-bottom: -4px')) ?>
	<?php echo html::anchor('command/submit?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_delete_all_comments, $label_delete_all_comments); ?>
</div>
<a name="comments" />
	<table class="comments_table">
		<caption><?php echo (isset($label_title)) ? $label_title : $this->translate->_('Comments'); ?></caption>
		<thead>
			<tr>
				<th><?php echo $label_entry_time ?></th>
				<th><?php echo $label_author ?></th>
				<th><?php echo $label_comment ?></th>
				<th style="white-space: nowrap"><?php echo $this->translate->_('ID') ?></th>
				<th style="width: 80px"><?php echo $label_persistent ?></th>
				<th><?php echo $label_type ?></th>
				<th style="width: 65px"><?php echo $label_expires ?></th>
				<th class="no-sort"><?php echo $label_actions ?></th>
			</tr>
			</thead>
			<tbody>
	<?php
		if ($data->count()) {
			$i=0;foreach ($data as $row) { $i++; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
				<td class="bl"><?php echo !empty($row->entry_time) ? date($date_format_str, $row->entry_time) : '' ?></td>
				<td><?php echo $row->author_name ?></td>
				<td style="white-space:normal"><?php echo $row->comment_data ?></td>
				<td><?php echo $row->comment_id ?></td>
				<td><?php echo $row->persistent ? $label_yes : $label_yes ?></td>
				<td>
					<?php	switch ($row->entry_type) {
						case Comment_Model::USER_COMMENT:
							echo $label_type_user;
							break;
						case Comment_Model::DOWNTIME_COMMENT:
							echo $label_type_downtime;
							break;
						case Comment_Model::FLAPPING_COMMENT:
							echo $label_type_flapping;
							break;
						case Comment_Model::ACKNOWLEDGEMENT_COMMENT:
							echo $label_type_acknowledgement;
							break;
						default:
							echo '?';
					} ?>
				</td>
				<td><?php echo $row->expires ? date($date_format_str, $row_>expire_time) : $na_str ?></td>
				<td class="icon">
					<?php echo html::anchor('command/submit?cmd_typ='.$cmd_delete_comment.'&com_id='.$row->comment_id,
					html::image('application/views/themes/default/icons/16x16/delete-comment.png', array('alt' => $label_delete, 'title' => $label_delete)),array('style' => 'border: 0px')) ?>
				</td>
			</tr>
			<?php	} } else { # print message - no comments available ?>
			<tr>
				<td colspan="8"><?php echo $no_data ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>
