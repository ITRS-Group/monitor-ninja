<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<img src='/monitor/images/comment.gif' alt="" />
	<?php echo html::anchor('cmd/request_command/?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_add_comment, $label_add_comment); ?>

	<img src='/monitor/images/delete.gif' alt="" />
	<?php echo html::anchor('cmd/request_command/?host='.$host.'&service='.urlencode($service).'&cmd_typ='.$cmd_delete_all_comments, $label_delete_all_comments); ?>

<div class="widget left w98" id="extinfo_info">
	<div class="widget-header"><?php echo $this->translate->_('Comments'); ?></div>
	<table id="sort-table" style="table-layout: fixed">
		<colgroup>
			<col style="width: 122px" />
			<col style="width: 150px" />
			<col style="width: 100%" />
			<col style="width: 100px" />
			<col style="width: 100px" />
			<col style="width: 100px" />
			<col style="width: 122px" />
			<col style="width: 80px" />
		</colgroup>
		<thead>
		<tr>
			<th class="bl"><?php echo $label_entry_time ?></th>
			<th><?php echo $label_author ?></th>
			<th><?php echo $label_comment ?></th>
			<th><?php echo $label_comment_id ?></th>
			<th><?php echo $label_persistent ?></th>
			<th><?php echo $label_type ?></th>
			<th><?php echo $label_expires ?></th>
			<th><?php echo $label_actions ?></th>
		</tr>
		<thead>
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
				<td>
					<?php echo html::anchor('cmd/request_command/?cmd_typ='.$cmd_delete_comment.'&com_id='.$row->id,
					'<img src="/monitor/images/delete.gif" alt="'.$label_delete.'" title="'.$label_delete.'" />') ?>
				</td>
			</tr>
			<?php	} } else { # print message - no comments available ?>
			<tr>
				<td colspan="8"><?php echo $no_data ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>