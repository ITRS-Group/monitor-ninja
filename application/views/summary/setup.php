<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"></div>

<div>
	<?php if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
		echo form::open('summary/index', array('id' => 'saved_report_form', 'style' => 'margin-top: 7px;'));
	 ?>
		<div style="width: 100%; padding-left: 0px">
			<!--	onchange="check_and_submit(this.form)"	-->
			<?php echo help::render('saved_reports', 'reports') ?> <?php echo _('Saved reports') ?><br />
			<select name="report_id" id="report_id">
				<option value=""> - <?php echo _('Select saved report') ?> - </option>
				<?php	$sched_str = "";
				foreach ($saved_reports as $info) {
					$sched_str = in_array($info->id, $scheduled_ids) ? " ( *"._('Scheduled')."* )" : "";
					if (in_array($info->id, $scheduled_ids)) {
						$sched_str = " ( *"._('Scheduled')."* )";
						$title_str = $scheduled_periods[$info->id]." "._('schedule');
					} else {
						$sched_str = "";
						$title_str = "";
					}
					echo '<option title="'.$title_str.'" '.(($options['report_id'] == $info->id) ? 'selected="selected"' : '').
						' value="'.$info->id.'">'.$info->report_name.$sched_str.'</option>'."\n";
				}  ?>
			</select>
			<input type="hidden" name="summary_type" value="<?php echo $options['summary_type'] ?>" />
			<input type="submit" class="button select" value="<?php echo _('Select') ?>" name="fetch_report" />
			<input type="button" class="button new" value="<?php echo _('New') ?>" name="new_report" title="<?php echo _('Create new saved Summary report') ?>" id="new_report" />
			<input type="button" class="button delete" value="Delete" name="delete_report" title="<?php echo _('Delete report') ?>" id="delete_report" />
			<?php if (isset($is_scheduled) && $is_scheduled) { ?>
			<div id="single_schedules" style="display:inline">
				<span id="is_scheduled" title="<?php echo _('This report has been scheduled. Click the icons below to change settings') ?>">
					<?php echo _('This is a scheduled report') ?>
				</span>
			</div>
		<?php	} ?>
	</div>
	<?php echo form::close(); } ?>

	<h1><?php echo _('Alert Summary Report') ?></h1>
	<h2><?php echo _('Report Mode') ?></h2>
	<form id="report_mode_form"><br />
	<label><?php echo form::radio(array('name' => 'report_mode'), 'standard', !$options['report_type'] || $options['standardreport']); ?> <?php echo _('Standard') ?></label> &nbsp; &nbsp; <label><?php echo form::radio(array('name' => 'report_mode'), 'custom', $options['report_type'] && !$options['standardreport']); ?> <?php echo _('Custom') ?></label>
	</form>
<br />

	<?php echo $report_options ?>
</div>
