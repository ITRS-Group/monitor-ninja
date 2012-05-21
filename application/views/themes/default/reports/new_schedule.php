<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="new_schedule_area">
<?php echo form::open('reports/schedule', array('id' => 'new_schedule_report_form', 'onsubmit' => 'return ajax_submit(this)')); ?>
		<h1><?php echo _('New schedule') ?></h1>
		<table id="new_schedule_report_table">
			<tr>
				<td>
					<?php echo help::render('report-type-save').' '._('Select report type') ?><br />
					<?php echo form::dropdown(array('name' => 'rep_type'), $defined_report_types); ?><br />
					<?php if (!empty($available_schedule_periods)) { ?>
						<?php echo help::render('interval').' '._('Report Interval') ?><br />
						<select name="period" id="period">
						<?php	foreach ($available_schedule_periods as $id => $period) { ?>
							<option value="<?php echo $id ?>"><?php echo $period ?></option>
						<?php	} ?>
						</select><br />
					<?php } ?>
					<?php echo help::render('select-report').' '._('Select report') ?><br />
					<!--	saved_report_id as drop-down depending on type		-->
					<select name="saved_report_id" id="saved_report_id">
						<option value=""> - <?php echo _('Select saved report') ?> - </option>
					<?php	foreach ($saved_reports as $report) { ?>
						<option value="<?php echo $report->id ?>"><?php echo $report->report_name ?></option>
					<?php	} ?>
					</select><br />
					<?php echo help::render('recipents').' '._('Recipients') ?><br /><input type="text" class="schedule" name="recipients" id="recipients" value="" />
				</td>
				<td>
					<?php echo help::render('filename').' '._('Filename (defaults to pdf, may end in .csv)') ?><br /><input type="text" class="schedule" name="filename" id="filename" value="" /><br />
					<?php echo help::render('description').' '._('Description') ?><br /><textarea cols="31" rows="4" id="description" name="description"></textarea><br />
					<?php echo help::render('local_persistent_filepath').' '._("Save report in this local folder") ?><br /><input type="text" class="schedule" name="local_persistent_filepath" id="local_persistent_filepath" value="" />
				</td>
			</tr>
			<tr>
				<td id="scheduled_btn_ctrl" colspan="2">
					<input type="submit" class="button save" name="sched_subm" id="sched_subm" value="<?php echo _('Save') ?>" />
					<input type="reset" class="button clear" name="reset_frm" id="reset_frm" value="<?php echo _('Clear') ?>" />
				</td>
			</tr>
		</table>
	</form>
</div>
