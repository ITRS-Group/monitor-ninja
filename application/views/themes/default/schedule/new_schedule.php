<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<h1><?php echo _('New Schedule'); ?></h1>
<hr />

<div id="new_schedule_area">
	<form action="schedule/schedule" id="new_schedule_report_form">
		
		<div class="left width-50">
			<label for="type"><?php echo help::render('report-type-save', 'reports').' '._('Select report type') ?></label><br />
					<?php echo form::dropdown(array('name' => 'type'), $defined_report_types); ?><br />
					<?php if (!empty($available_schedule_periods)) { ?>
						<label for="period"><?php echo help::render('interval', 'reports').' '._('Report Interval') ?></label><br />
						<select name="period" id="period">
						<?php	foreach ($available_schedule_periods as $id => $period) { ?>
							<option value="<?php echo $id ?>"><?php echo $period ?></option>
						<?php	} ?>
						</select><br />
					<?php } ?>
					<label for="saved_report_id"><?php echo help::render('select-report', 'reports').' '._('Select report') ?></label><br />
					<select name="saved_report_id" id="saved_report_id">
						<option value=""> - <?php echo _('Select saved report') ?> - </option>
						<?php foreach ($saved_reports as $report) { ?>
						<option value="<?php echo $report->id ?>"><?php echo $report->report_name ?></option>
						<?php } ?>
					</select><br />
					<label for="recipients"><?php echo help::render('recipents', 'reports').' '._('Recipients') ?></label><br /><input type="text" class="schedule" name="recipients" id="recipients" value="" />
		</div>
		
		<div class="right width-50">
			<label for="filename"><?php echo help::render('filename', 'reports').' '._('Filename (defaults to pdf, may end in .csv)') ?></label><br /><input type="text" class="schedule" name="filename" id="filename" value="" /><br />
			<label for="description"><?php echo help::render('description', 'reports').' '._('Description') ?></label><br /><textarea cols="31" rows="4" id="description" name="description"></textarea><br />
			<label for="local_persistent_filepath"><?php echo help::render('local_persistent_filepath', 'reports').' '._("Save report in this local folder") ?></label><br /><input type="text" class="schedule" name="local_persistent_filepath" id="local_persistent_filepath" value="" /><br />
			<label for="attach_description"><?php echo help::render('attach_description', 'reports').' '._("Attach description") ?></label><br />
			<select name="attach_description" id="attach_description">
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select>
		</div>

		<div class="clear"></div>
		<span>
			<input type="submit" class="button save" value="<?php echo _('Save') ?>" />
			<input type="reset" class="button clear" value="<?php echo _('Clear') ?>" />
		</span>
	</form>
</div>
