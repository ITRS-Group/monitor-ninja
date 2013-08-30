<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
$saved_reports_exists = false;
if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
	$saved_reports_exists = true;
}
?>

<div class="no-borders">
	<div class="report-page-setup availsla">
	<div id="response"><?php
	if (isset($error_msg)) {
		echo '<ul class="alert error"><li>'.$error_msg.'</li></ul>';
	}
	?></div>
		<div class="setup-table">
			<h1 id="report_type_label"><?php echo $label_create_new ?></h1>

			<div id="switcher" class="report-block">
				<a id="switch_report_type" href="<?php echo url::base(true) . ($type == 'avail' ? 'sla' : 'avail') ?>/index" style="border: 0px; float: left; margin-right: 5px">
				<?php
					echo $type == 'avail' ?
					html::image($this->add_path('icons/16x16/sla.png'), array('alt' => _('SLA'), 'title' => _('SLA'), 'ID' => 'switcher_image')) :
					html::image($this->add_path('icons/16x16/availability.png'), array('alt' => _('Availability'), 'title' => _('Availability'), 'ID' => 'switcher_image'));
				?>
				<span id="switch_report_type_txt" style="border-bottom: 1px dotted #777777">
				<?php echo $type == 'avail' ? _('Switch to SLA report') :_('Switch to Availability report'); ?>
				</span>
				</a>
			</div>

			<?php echo form::open($type.'/index', array('id' => 'saved_report_form', 'class' => 'report-block', 'method' => 'get')); ?>
				<div id="saved_reports_display" style="width: 100%; padding-left: 0px;<?php if (!$saved_reports_exists) { ?>display:none;<?php } ?>">
					<?php echo help::render('saved_reports') ?> <label for="report_id"><?php echo _('Saved reports') ?></label><br />
					<select name="report_id" id="report_id">
						<option value=""> - <?php echo _('Select saved report') ?> - </option>
						<?php	$sched_str = "";
						if ($saved_reports_exists) {
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
							}
						} ?>
					</select>
					<input type="submit" class="button select" value="<?php echo _('Select') ?>" name="fetch_report" />
					<input type="button" class="button new" value="<?php echo _('New') ?>" name="new_report" title="<?php echo $new_saved_title ?>" id="new_report" />
					<input type="button" class="button delete" value="Delete" name="delete_report" title="<?php echo _('Delete report') ?>" id="delete_report" />
					<?php if (isset($is_scheduled) && $is_scheduled) { ?>
					<div id="single_schedules" style="display:inline">
						<span id="is_scheduled" title="<?php echo _('This report has been scheduled. Click the icons below to change settings') ?>">
							<?php echo _('This is a scheduled report') ?>
							<a href="<?php echo url::base(true) ?>schedule/show" id="show_scheduled" class="help">[<?php echo _('edit') ?>]</a>
						</span>
					</div>
					<?php	} ?>
				</div>
			<?php echo form::close();?>
		</div>

		<?php echo form::open($type.'/generate', array('id' => 'report_form')); ?>
			<?php echo $report_options; ?>
		</form>
	</div>
</div>
