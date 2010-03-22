<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
			<div id="schedules_area">
				<?php echo isset($new_schedule) ? $new_schedule : '' ?>
				<br /><br />
				<div id="scheduled_avail_reports">
					<table id="avail_scheduled_reports_table">
					<caption><?php echo $avail_header ?></caption>
						<thead id="avail_headers" <?php if (!count($avail_schedules)) { ?>style="display:none;"<?php } ?>>
							<tr class="setup">
								<th class="headerNone left"><?php echo $label_sch_interval ?></th>
								<th class="headerNone left"><?php echo $label_sch_name ?></th>
								<th class="headerNone left"><?php echo $label_sch_recipients ?></th>
								<th class="headerNone left"><?php echo $label_sch_filename ?></th>
								<th class="headerNone left"><?php echo $label_sch_description ?></th>
								<th class="headerNone left" style='width: 60px'><?php echo $this->translate->_('Actions'); ?></th>
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
								<td class="period_select" title="<?php echo $label_dblclick ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
								<td class="report_name" id="<?php echo $schedule->report_type_id ?>.report_id-<?php echo $schedule->id ?>"><?php echo $schedule->reportname ?></td>
								<td class="iseditable" title="<?php echo $label_dblclick ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
								<td class="iseditable" title="<?php echo $label_dblclick ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
								<td class="iseditable_txtarea" title="<?php echo $label_dblclick ?>" id="description-<?php echo $schedule->id ?>"><?php echo utf8_decode($schedule->description) ?></td>
								<td>
									<form><input type="button" class="send_report_now" id="send_now_avail_<?php echo $schedule->id ?>" title="<?php echo $this->translate->_('Send this report now') ?>" value="&nbsp;"></form>
									<div class="delete_schedule avail_del" id="alldel_<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'), array('alt' => $this->translate->_('Delete scheduled report'), 'title' => $this->translate->_('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
								</td>
							</tr>
							<?php } ?>
							</tbody>
							<?php }?>
								<tr id="avail_no_result" class="no-result"<?php if (!empty($avail_schedules) && count($avail_schedules)) { ?> style="display:none;"<?php } ?>><td colspan="7"><?php echo $label_no_schedules ?></td></tr>
					</table>
				</div>

				<br /><br />
				<div id="scheduled_sla_reports" style="width: 100%">
					<table id="sla_scheduled_reports_table" style='width: 100%;' class="white-table">
					<caption><?php echo $sla_header ?></caption>
						<thead id="sla_headers"<?php if (!count($sla_schedules)) { ?> style="display:none"<?php } ?>>
							<tr class="setup">
								<th class="headerNone left"><?php echo $label_sch_interval ?></th>
								<th class="headerNone left"><?php echo $label_sch_name ?></th>
								<th class="headerNone left"><?php echo $label_sch_recipients ?></th>
								<th class="headerNone left"><?php echo $label_sch_filename ?></th>
								<th class="headerNone left"><?php echo $label_sch_description ?></th>
								<th class="headerNone left"style='width: 60px'><?php echo $this->translate->_('Actions'); ?></th>
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
								<td class="period_select" title="<?php echo $label_dblclick ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
								<td class="report_name" id="<?php echo $schedule->report_type_id ?>.report_id-<?php echo $schedule->id ?>"><?php echo $schedule->reportname ?></td>
								<td class="iseditable" title="<?php echo $label_dblclick ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
								<td class="iseditable" title="<?php echo $label_dblclick ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
								<td class="iseditable_txtarea" title="<?php echo $label_dblclick ?>" id="description-<?php echo $schedule->id ?>"><?php echo utf8_decode($schedule->description) ?></td>
								<td>
									<form><input type="button" class="send_report_now" id="send_now_sla_<?php echo $schedule->id ?>" title="<?php echo $this->translate->_('Send this report now') ?>" value="&nbsp;"></form>
									<div class="delete_schedule sla_del" id="alldel_<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'),array('alt' => $this->translate->_('Delete scheduled report'), 'title' => $this->translate->_('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
						<?php }?>
							<tr id="sla_no_result" class="no-result"<?php if (!empty($sla_schedules) && count($sla_schedules)) { ?> style="display:none"<?php } ?>><td colspan="7"><?php echo $label_no_schedules ?></td></tr>
					</table>
				</div>
			</div>