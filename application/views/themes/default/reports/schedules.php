<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
			<div id="schedules_area">
				<?php echo isset($new_schedule) ? $new_schedule : '' ?>
				<br /><br /><a name="avail_schedules"></a>
				<div id="scheduled_avail_reports">
					<table id="avail_scheduled_reports_table">
					<caption><?php echo _('Availability Reports') ?></caption>
						<thead id="avail_headers" <?php if (!count($avail_schedules)) { ?>style="display:none;"<?php } ?>>
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
						<?php if (!empty($avail_schedules) && count($avail_schedules)) { ?>
						<tbody>
							<?php	$recipients = false; $i = 0;
								foreach ($avail_schedules as $schedule) {
									$i++;
									$schedule = (object)$schedule;
									$recipients = str_replace(' ', '', $schedule->recipients);
									$recipients = str_replace(',', ', ', $recipients);	?>
								<tr id="report-<?php echo $schedule->id ?>" class="<?php echo ($i%2 == 0 ? 'odd' : 'even'); ?>">
								<td class="period_select" title="<?php echo _('Double click to edit') ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
								<td class="report_name" id="<?php echo $schedule->report_type_id ?>.report_id-<?php echo $schedule->id ?>"><?php echo $schedule->reportname ?></td>
								<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
								<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
								<td class="iseditable_txtarea" title="<?php echo _('Double click to edit') ?>" id="description-<?php echo $schedule->id ?>"><?php echo $schedule->description ?></td>
								<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="local_persistent_filepath-<?php echo $schedule->id ?>"><?php echo $schedule->local_persistent_filepath ?></td>
								<td>
									<form><input type="button" class="send_report_now" id="send_now_avail_<?php echo $schedule->id ?>" title="<?php echo _('Send this report now') ?>" value="&nbsp;"></form>
									<div class="delete_schedule avail_del" id="alldel_<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'), array('alt' => _('Delete scheduled report'), 'title' => _('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
								</td>
							</tr>
							<?php } ?>
							</tbody>
							<?php }?>
								<tr id="avail_no_result" class="no-result"<?php if (!empty($avail_schedules) && count($avail_schedules)) { ?> style="display:none;"<?php } ?>><td colspan="7"><?php echo _('There are no scheduled reports') ?></td></tr>
					</table>
				</div>

				<br /><br /><a name="sla_schedules"></a>
				<div id="scheduled_sla_reports" style="width: 100%">
					<table id="sla_scheduled_reports_table">
					<caption><?php echo _('SLA Reports') ?></caption>
						<thead id="sla_headers"<?php if (!count($sla_schedules)) { ?> style="display:none"<?php } ?>>
							<tr class="setup">
								<th class="headerNone left"><?php echo _('Interval') ?></th>
								<th class="headerNone left"><?php echo _('Report') ?></th>
								<th class="headerNone left"><?php echo _('Recipients') ?></th>
								<th class="headerNone left"><?php echo _('Filename') ?></th>
								<th class="headerNone left"><?php echo _('Description') ?></th>
								<th class="headerNone left"><?php echo _("Local persistent filepath") ?></th>
								<th class="headerNone left"style='width: 60px'><?php echo _('Actions'); ?></th>
							</tr>
						</thead>
						<?php if (!empty($sla_schedules) && count($sla_schedules)) { ?>
						<tbody>
							<?php	$recipients = false;
								$j=0;
								foreach ($sla_schedules as $schedule) {
									$j++;
									$schedule = (object)$schedule;
									$recipients = str_replace(' ', '', $schedule->recipients);
									$recipients = str_replace(',', ', ', $recipients);	?>
								<tr id="report-<?php echo $schedule->id ?>" class="<?php echo ($j%2 == 0 ? 'odd' : 'even')?>">
								<td class="period_select" title="<?php echo _('Double click to edit') ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
								<td class="report_name" id="<?php echo $schedule->report_type_id ?>.report_id-<?php echo $schedule->id ?>"><?php echo $schedule->reportname ?></td>
								<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
								<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
								<td class="iseditable_txtarea" title="<?php echo _('Double click to edit') ?>" id="description-<?php echo $schedule->id ?>"><?php echo $schedule->description ?></td>
								<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="local_persistent_filepath-<?php echo $schedule->id ?>"><?php echo $schedule->local_persistent_filepath ?></td>
								<td>
									<form><input type="button" class="send_report_now" id="send_now_sla_<?php echo $schedule->id ?>" title="<?php echo _('Send this report now') ?>" value="&nbsp;"></form>
									<div class="delete_schedule sla_del" id="alldel_<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'),array('alt' => _('Delete scheduled report'), 'title' => _('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
						<?php }?>
							<tr id="sla_no_result" class="no-result"<?php if (!empty($sla_schedules) && count($sla_schedules)) { ?> style="display:none"<?php } ?>><td colspan="7"><?php echo _('There are no scheduled reports') ?></td></tr>
					</table>
				</div>

				<br /><br /><a name="summary_schedules"></a>
				<div id="scheduled_summary_reports" style="width: 100%">
					<table id="summary_scheduled_reports_table">
					<caption><?php echo _('Alert Summary Reports') ?></caption>
						<thead id="summary_headers"<?php if (!count($summary_schedules)) { ?> style="display:none"<?php } ?>>
							<tr class="setup">
								<th class="headerNone left"><?php echo _('Interval') ?></th>
								<th class="headerNone left"><?php echo _('Report') ?></th>
								<th class="headerNone left"><?php echo _('Recipients') ?></th>
								<th class="headerNone left"><?php echo _('Filename') ?></th>
								<th class="headerNone left"><?php echo _('Description') ?></th>
								<th class="headerNone left"><?php echo _("Local persistent filepath") ?></th>
								<th class="headerNone left"style='width: 60px'><?php echo _('Actions'); ?></th>
							</tr>
						</thead>
						<?php if (!empty($summary_schedules) && count($summary_schedules)) { ?>
						<tbody>
							<?php	$recipients = false;
								$j=0;
								foreach ($summary_schedules as $schedule) {
									$j++;
									$schedule = (object)$schedule;
									$recipients = str_replace(' ', '', $schedule->recipients);
									$recipients = str_replace(',', ', ', $recipients);	?>
								<tr id="report-<?php echo $schedule->id ?>" class="<?php echo ($j%2 == 0 ? 'odd' : 'even')?>">
								<td class="period_select" title="<?php echo _('Double click to edit') ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
								<td class="report_name" id="<?php echo $schedule->report_type_id ?>.report_id-<?php echo $schedule->id ?>"><?php echo $schedule->reportname ?></td>
								<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
								<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
								<td class="iseditable_txtarea" title="<?php echo _('Double click to edit') ?>" id="description-<?php echo $schedule->id ?>"><?php echo $schedule->description ?></td>
								<td class="iseditable" title="<?php echo _('Double click to edit') ?>" id="local_persistent_filepath-<?php echo $schedule->id ?>"><?php echo $schedule->local_persistent_filepath ?></td>
								<td>
									<form><input type="button" class="send_report_now" id="send_now_summary_<?php echo $schedule->id ?>" title="<?php echo _('Send this report now') ?>" value="&nbsp;"></form>
									<div class="delete_schedule summary_del" id="alldel_<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'),array('alt' => _('Delete scheduled report'), 'title' => _('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
						<?php }?>
							<tr id="summary_no_result" class="no-result"<?php if (!empty($summary_schedules) && count($summary_schedules)) { ?> style="display:none"<?php } ?>><td colspan="7"><?php echo _('There are no scheduled reports') ?></td></tr>
					</table>
				</div>
			</div>
