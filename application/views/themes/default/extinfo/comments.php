<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table border=0>
	<tr>
		<td valign=center>
			<img src='/monitor/images/comment.gif' border=0 align=center>
		</td>
		<td class='comment'>
		<?php echo html::anchor('cmd/request_command/?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_add_comment, $label_add_comment); ?>
		</td>
		<td valign=center>
			<img src='/monitor/images/delete.gif' border=0 align=center>
		</td>
		<td class='comment'>
		<?php echo html::anchor('cmd/request_command/?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_delete_all_comments, $label_delete_all_comments); ?>
		</td>
	</tr>
</table>

<table border="0" class='comment'>
	<tr class='comment'>
		<th class='comment'>
			<?php echo $label_entry_time ?>
		</th>
		<th class='comment'>
			<?php echo $label_author ?>
		</th>
		<th class='comment'>
		<?php echo $label_comment ?>
		</th>
		<th class='comment'>
		<?php echo $label_comment_id ?>
		</th>
		<th class='comment'>
			<?php echo $label_persistent ?>
		</th>
		<th class='comment'>
			<?php echo $label_type ?>
		</th>
		<th class='comment'>
			<?php echo $label_expires ?>
		</th>
		<th class='comment'>
			<?php echo $label_actions ?>
		</th>
	</tr>

<?php
	if ($data->count()) {
		foreach ($data as $row) { ?>
		<tr class='commentOdd'>
			<td class='commentOdd'>
				<?php echo !empty($row->entry_time) ? date($date_format_str, $row->entry_time) : '' ?>
			</td>
			<td class='commentOdd'>
				<?php echo $row->author_name ?>
			</td>
			<td class='commentOdd'>
				<?php echo $row->comment_data ?>
			</td>

			<td class='commentOdd'>
				<?php echo $row->comment_id ?>
			</td>
			<td class='commentOdd'>
				<?php echo $row->persistent ? $label_yes : $label_yes ?>
			</td>
			<td class='commentOdd'>
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
						echo "?";
				} ?>
			</td>
			<td class='commentOdd'>
				<?php echo $row->expires ? date($date_format_str, $row_>expire_time) : $na_str ?>
			</td>
			<td>
				<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_delete_comment.'&com_id='.$row->id,
				"<img src='/monitor/images/delete.gif' border='0' alt='" . $label_delete . "' title='" . $label_delete . "' />") ?>
			</td>
		</tr>
	<?php	}
	} else {
		# print message - no comments available ?>
		<tr class="commentOdd">
			<td colspan="8">
				<?php echo $no_data ?>
			</td>
		</tr>
	<?php
	} ?>
</table>