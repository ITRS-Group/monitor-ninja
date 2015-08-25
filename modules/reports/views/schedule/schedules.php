<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="schedules_area">
	<?php echo isset($new_schedule) ? $new_schedule : '' ?>
	<h1><?php echo _('Scheduled reports') ?></h1>
	<?php foreach($defined_report_types as $report_type => $report_type_label) { ?>
	<div id="scheduled_<?php echo $report_type ?>_reports">
		<table class="padd-table" id="<?php echo $report_type ?>_scheduled_reports_table">
			<caption><?php echo _('Your scheduled '.$report_type_label.' Reports') ?></caption>
			<thead id="<?php echo $report_type ?>_headers">
				<tr class="setup">
					<th><?php echo _('Interval') ?></th>
					<th><?php echo _('Report') ?></th>
					<th><?php echo _('Recipients') ?></th>
					<th><?php echo _('Filename') ?></th>
					<th><?php echo _('Description') ?></th>
					<th><?php echo _("Local persistent filepath") ?></th>
					<th><?php echo _("Attach description") ?></th>
					<th><?php echo _('Actions'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr id="<?php echo $report_type ?>_no_result" class="no-result" style="display: none"><td colspan="8"><center><h3><?php echo sprintf(_('There are no scheduled %s'), $report_type_label) ?></h3></center></td></tr>
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
			<td class="attach_description" title="<?php echo _('Double click to edit') ?>"></td>
			<td class="action">
				<a href="" class="direct_link" title="<?php echo _("View report") ?>"><span class="icon-16 x16-status-detail"></span></a>
				<a href="#" class="send_report_now" title="<?php echo _("Send this report now") ?>" data-schedule="" data-report_id="" data-type=""><span class="icon-16 x16-send-report"></span></a>
				<a href="#" title="<?php echo _("Delete scheduled report") ?>" class="delete_schedule" data-schedule="" data-type=""><span class="icon-16 x16-delete-schedule deleteimg"></span></a>
			</td>
		</tr>
	</table>
</div>
