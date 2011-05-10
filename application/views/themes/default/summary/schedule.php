<?php defined('SYSPATH') OR die("No direct access allowed");
if (!$create_pdf) {
?>
	<span id="view_add_schedule"<?php if (!$report_id) {?> style="display: none;"<?php } ?> style="position: absolute; right: 1%; top: 11px">
		<a id="new_schedule_btn" href="#new_schedule_form_area" class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-add-schedule.png'), array('alt' => $this->translate->_('Add').' '. strtolower($label_new_schedule), 'title' => $this->translate->_('Add').' '. strtolower($label_new_schedule))); ?></a>
		<a id="show_schedule" href="#schedule_report"<?php echo (empty($scheduled_info)) ? ' style="display:none;"' : ''; ?> class="fancybox" style="border: 0px"><?php echo html::image($this->add_path('/icons/32x32/square-view-schedule.png'), array('alt' => $label_view_schedule, 'title' => $label_view_schedule)); ?></a>
	</span>

	<span id="autoreport_periods" style="display:none"><?php echo $json_periods ?></span>
	<div id="new_schedule_form_area" style="display:none">
	<?php	echo form::open('reports/schedule', array('id' => 'schedule_report_form', 'onsubmit' => 'return trigger_schedule_save(this);'));
		?>
		<h1><?php echo $label_new_schedule ?></h1>
		<table id="new_schedule_report_table" class="white-table">
			<tr class="none">
				<?php if (!empty($available_schedule_periods)) { ?>
				<td>
					<?php echo $label_interval ?><br>
					<select name="period" id="period" class="popup">
					<?php	foreach ($available_schedule_periods as $id => $period) { ?>
					<option value="<?php echo $id ?>"><?php echo $period ?></option>
					<?php	} ?>
					</select>
				</td>
				<?php } ?>
				<td><?php echo $label_recipients ?><br /><input type="text" class="schedule" name="recipients" id="recipients" value="" style="width: 200px" /></td>
				<td><?php echo $label_filename ?><br /><input type="text" class="schedule" name="filename" id="filename" value="" style="width: 200px" /></td>
			</tr>
			<tr class="none">
				<td colspan="<?php echo empty($available_schedule_periods) ? '2' : '3';?>"><?php echo $label_description ?><br /><textarea cols="31" rows="4" id="description" name="description" style="width: 540px;margin-top: 3px"></textarea></td>
			</tr>
			<tr class="none">
				<td id="scheduled_btn_ctrl" colspan="<?php echo empty($available_schedule_periods) ? '2' : '3';?>">
					<input type="submit" name="sched_subm" id="sched_subm" value="<?php echo $label_save ?>" />
					<input type="reset" name="reset_frm" id="reset_frm" value="<?php echo $label_clear ?>" />
				</td>
			</tr>
		</table>
		<div><input type="hidden" name="saved_report_id" id="saved_report_id" value="<?php echo $report_id ?>" />
		<input type="hidden" name="type" value="summary" />
		<input type="hidden" name="module_save" id="module_save" value="1" /></div>
		</form>
	</div>

	<div id="schedule_report" style="display:none">
		<table id="schedule_report_table">
				<caption><?php echo $lable_schedules ?> (<span id="scheduled_report_name"><?php echo !empty($report_info) ? (!empty($report_info['report_name']) ?
									$report_info['report_name'] : $report_info['sla_name']) : '' ?></span>)</caption>
				<tr id="schedule_header">
					<th class="headerNone left"><?php echo $label_interval ?></th>
					<th class="headerNone left"><?php echo $label_recipients ?></th>
					<th class="headerNone left"><?php echo $label_filename ?></th>
					<th class="headerNone left"><?php echo $label_description ?></th>
					<th class="headerNone left" style="width: 45px"><?php echo $this->translate->_('Actions') ?></th>
				</tr>
			<?php if (!empty($scheduled_info)) {
				$i = 0;
				foreach ($scheduled_info as $schedule) {
					$i++;
					$schedule = (object)$schedule;
					$recipients = str_replace(' ', '', $schedule->recipients);
					$recipients = str_replace(',', ', ', $recipients); ?>
			<tr id="report-<?php echo $schedule->id ?>" class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
				<td class="period_select" title="<?php echo $label_dblclick ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
				<td class="iseditable" title="<?php echo $label_dblclick ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
				<td class="iseditable" title="<?php echo $label_dblclick ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
				<td class="iseditable_txtarea" title="<?php echo $label_dblclick ?>" id="description-<?php echo $schedule->id ?>"><?php echo $schedule->description ?></td>
				<td>
					<form><input type="button" class="send_report_now" onclick="send_report_now('summary', <?php echo $schedule->id ?>)" id="send_now_summary_<?php echo $schedule->id ?>" title="<?php echo $this->translate->_('Send this report now') ?>" value="&nbsp;"></form>
					<div class="delete_schedule <?php echo $type ?>_del" onclick="schedule_delete(<?php echo $schedule->id ?>, 'summary')" id="delid_<?php echo $schedule->id ?>"><?php echo html::image($this->add_path('icons/16x16/delete-schedule.png'), array('alt' => $this->translate->_('Delete scheduled report'), 'title' => $this->translate->_('Delete scheduled report'),'class' => 'deleteimg')) ?></div>
				</td>
			</tr>
			<?php }	} ?>
		</table>
	</div>
<?php } ?>
