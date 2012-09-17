<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="schedules_area" class="left w98">
	<?php echo isset($new_schedule) ? $new_schedule : '' ?>
	<?php foreach(array('Availability' => 'avail', 'SLA' => 'sla', 'Summary' => 'summary') as $report_type_label => $report_type) { ?>
	<br /><br />
	<div id="scheduled_<?php echo $report_type ?>_reports">
		<table id="<?php echo $report_type ?>_scheduled_reports_table">
		<caption><?php echo _($report_type_label.' Reports') ?></caption>
			<thead id="<?php echo $report_type ?>_headers" <?php if (!count(${$report_type."_schedules"})) { ?>style="display:none;"<?php } ?>>
				<tr class="setup">
					<th class="headerNone left"><?php echo _('Interval') ?></th>
					<th class="headerNone left"><?php echo _('Report') ?></th>
					<th class="headerNone left"><?php echo _('Recipients') ?></th>
					<th class="headerNone left"><?php echo _('Filename') ?></th>
					<th class="headerNone left"><?php echo _('Description') ?></th>
					<th class="headerNone left"><?php echo _("Local persistent filepath") ?></th>
					<th class="headerNone left" style='width: 60px'><?php echo _('Actions'); ?></th>
				</tr>
			</thead>
			<?php if (!empty(${$report_type."_schedules"}) && count(${$report_type."_schedules"})) { ?>
			<tbody>
				<?php	$recipients = false; $i = 0;
					foreach (${$report_type."_schedules"} as $schedule) {
						$i++;
						$schedule = (object)$schedule;
						$recipients = str_replace(' ', '', $schedule->recipients);
						$recipients = str_replace(',', ', ', $recipients); ?>
					<tr id="report-<?php echo $schedule->id ?>" class="<?php echo ($i%2 == 0 ? 'odd' : 'even'); ?>">
						<td class="period_select" title="<?php echo _('Double click to edit') ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
						<td class="report_name" id="<?php echo $schedule->report_type_id ?>.report_id-<?php echo $schedule->id ?>"><?php echo $schedule->reportname ?></td>
						<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
						<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
						<td class="iseditable_txtarea" title="<?php echo _('Double click to edit') ?>" id="description-<?php echo $schedule->id ?>"><?php echo $schedule->description ?></td>
						<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="local_persistent_filepath-<?php echo $schedule->id ?>"><?php echo $schedule->local_persistent_filepath ?></td>
						<td>
							<a href="<?php echo url::base(true).$report_type.'/generate?report_id='.$schedule->report_id;?>"><img src="<?php echo $this->add_path("icons/16x16/status-detail.png") ?>" alt="<?php echo _("View") ?>" /></a>
							<form><input type="button" class="send_report_now" data-schedule="<?php echo $schedule->id ?>" data-report_id="<?php echo $schedule->report_id ?>" data-type="<?php echo $report_type ?>" title="<?php echo _('Send this report now') ?>" value="&nbsp;"></form>
							<div class="delete_schedule <?php echo $report_type ?>_del" id="alldel_<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'), array('alt' => _('Delete scheduled report'), 'title' => _('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
						</td>
					</tr>
					<?php } ?>
			</tbody>
			<?php }?>
			<tr id="<?php echo $report_type ?>_no_result" class="no-result"<?php if (!empty(${$report_type."_schedules"}) && count(${$report_type."_schedules"})) { ?> style="display:none;"<?php } ?>><td colspan="7"><?php echo _('There are no scheduled reports') ?></td></tr>
		</table>
	</div>
<?php } ?>
</div>
