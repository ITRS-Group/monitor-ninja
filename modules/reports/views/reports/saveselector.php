<?php echo form::open($type.'/index', array('id' => 'saved_report_form', 'class' => 'report-block', 'method' => 'get')); ?>
	<div id="saved_reports_display" style="width: 100%; padding-left: 0px;<?php if (!$saved_reports) { ?>display:none;<?php } ?>">
		<?php echo help::render('saved_reports') ?> <label for="report_id"><?php echo _('Saved reports') ?></label><br />
		<select name="report_id" id="report_id">
			<option value=""> - <?php echo _('Select saved report') ?> - </option>
			<?php
			foreach ($saved_reports as $id => $report_name) {
				echo '<option '.(($options['report_id'] == $id) ? 'selected="selected"' : '').
					' value="'.$id.'">'.$report_name.'</option>'."\n";
			} ?>
		</select>
		<input type="submit" class="button select" value="<?php echo _('Select') ?>" name="fetch_report" />
		<input type="button" class="button new" value="<?php echo _('New') ?>" name="new_report" title="<?php echo _('New empty report'); ?>" id="new_report" />
		<input type="button" class="button delete" value="Delete" name="delete_report" title="<?php echo _('Delete report') ?>" id="delete_report" />
		<?php if (!empty($scheduled_info)) { ?>
		<div id="single_schedules" style="display:inline">
			<span id="is_scheduled" title="<?php echo _('This report has been scheduled. Click the icons below to change settings') ?>">
				<?php echo _('This is a scheduled report') ?>
				<a href="<?php echo url::base(true) ?>schedule/show" id="show_scheduled" class="help">[<?php echo _('edit') ?>]</a>
			</span>
		</div>
		<?php } ?>
	</div>
<?php echo form::close();?>

