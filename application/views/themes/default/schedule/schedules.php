<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="schedules_area">
	<?php echo isset($new_schedule) ? $new_schedule : '' ?>
	<?php foreach(array('Availability' => 'avail', 'SLA' => 'sla', 'Summary' => 'summary') as $report_type_label => $report_type) { ?>
	<br /><br />
	<div id="scheduled_<?php echo $report_type ?>_reports">
		<table id="<?php echo $report_type ?>_scheduled_reports_table">
			<caption><?php echo _($report_type_label.' Reports') ?></caption>
			<thead id="<?php echo $report_type ?>_headers">
				<tr class="setup">
					<th><?php echo _('Interval') ?></th>
					<th><?php echo _('Report') ?></th>
					<th><?php echo _('Recipients') ?></th>
					<th><?php echo _('Filename') ?></th>
					<th><?php echo _('Description') ?></th>
					<th><?php echo _("Local persistent filepath") ?></th>
					<th><?php echo _('Actions'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr id="<?php echo $report_type ?>_no_result" class="no-result" style="display: none"><td colspan="7"><?php echo _('There are no scheduled reports') ?></td></tr>
			</tbody>
		</table>
	</div>
<?php } # Note: attributes with empty values here are assumed to be filled in ?>
	<table id="schedule_template" style="display: none">
		<tr id="">
			<td class="period_select" title="<?php echo _('Double click to edit') ?>"></td>
			<td class="report_name"></td>
			<td class="iseditable recipients" title="<?php echo _('Double click to edit') ?>"></td>
			<td class="iseditable filename" title="<?php echo _('Double click to edit') ?>"></td>
			<td class="iseditable_txtarea description" title="<?php echo _('Double click to edit') ?>"></td>
			<td class="iseditable local-path" title="<?php echo _('Double click to edit') ?>"></td>
			<td class="action">
				<a href="" class="direct_link"><img src="<?php echo $this->add_path("icons/16x16/status-detail.png") ?>" title="<?php echo _("View report") ?>" alt="<?php echo _("View report") ?>" /></a>
				<a href="#" class="send_report_now" data-schedule="" data-report_id="" data-type=""><img src="<?php echo $this->add_path('icons/16x16/send-report.png') ?>" alt="<?php echo _('Send this report now') ?>" title="<?php echo _('Send this report now') ?>" /></a>
				<a href="#" class="delete_schedule" data-schedule="" data-type=""><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'), array('alt' => _('Delete scheduled report'), 'title' => _('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
			</td>
		</tr>
	</table>
</div>
