<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
$saved_reports_exists = false;
if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
	$saved_reports_exists = true;
}
?>

<div class="no-borders">
	<div class="report-page-setup availsla">
		<div id="response"></div>
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
					<?php echo help::render('saved_reports') ?> <?php echo _('Saved reports') ?><br />
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
			<input type="hidden" name="type" value="<?php echo $type ?>" />
			<table summary="Select report type" class="setup-tbl"><!--id="main_table"-->
				<tr>
					<td colspan="3">
						<label for="report_type"><?php echo help::render('report-type').' '._('Report type'); ?></label><br />
						<select name="report_type" id="report_type">
							<option value="hostgroups"><?php echo _('Hostgroups') ?></option>
							<option value="hosts"><?php echo _('Hosts') ?></option>
							<option value="servicegroups"><?php echo _('Servicegroups') ?></option>
							<option value="services"><?php echo _('Services') ?></option>
						</select>
						<input type="button" id="sel_report_type" class="button select20" value="<?php echo _('Select') ?>" />
					</td>
				</tr>
				<tr id="filter_row">
					<td colspan="3">
						<?php echo help::render('filter').' '._('Filter') ?><br />
						<input type="text" name="filter_field" id="filter_field" autocomplete=off size="10" value="">
						<input type="button" name="clear_filter" id="clear_filter" value="<?php echo _('Clear') ?>">
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<div id="progress"></div>
					</td>
				</tr>
				<tr data-show-for="hostgroups">
					<td colspan="3">

							<div class="left" style="width: 40%">
								<label for="hostgroup_tmp"><?php echo _('Available').' '._('Hostgroups') ?></label><br />
								<select name="hostgroup_tmp[]" style="width: 100%" id="hostgroup_tmp" multiple="multiple" size='8' class="multiple"></select>
							</div>

							<div class="left" style="padding-top: 40px;">
								<input type="button" value="&gt;" id="mv_hg_r" class="button arrow-right" /><br />
								<input type="button" value="&lt;" id="mv_hg_l" class="button arrow-left" />
							</div>

							<div class="left" style="width: 40%">
								<label for="hostgroup"><?php echo _('Selected').' '._('Hostgroups') ?></label><br />
								<select name="hostgroup[]" style="width: 100%" id="hostgroup" multiple="multiple" size="8" class="multiple"></select>
							</div>

							<div class="clear"></div>

						</div>
					</td>
				</tr>
				<tr data-show-for="servicegroups">
					<td colspan="3">

						<div class="left" style="width: 40%">
							<label for="servicegroup_tmp"><?php echo _('Available').' '._('Servicegroups') ?></label><br />
							<select name="servicegroup_tmp[]" style="width: 100%" id="servicegroup_tmp" multiple="multiple" size='8' class="multiple"></select>
						</div>

						<div class="left" style="padding-top: 40px;">
							<input type="button" value="&gt;" id="mv_sg_r" class="button arrow-right" /><br />
							<input type="button" value="&lt;" id="mv_sg_l" class="button arrow-left" />
						</div>

						<div class="left" style="width: 40%">
							<label for="servicegroup"><?php echo _('Selected').' '._('Servicegroups') ?></label><br />
							<select name="servicegroup[]" style="width: 100%" id="servicegroup" multiple="multiple" size="8" class="multiple"></select>
						</div>

						<div class="clear"></div>

					</td>
				</tr>
				<tr data-show-for="hosts">
					<td colspan="3">

						<div class="left" style="width: 40%">
							<label for="host_tmp"><?php echo _('Available').' '._('Hosts') ?></label><br />
							<select name="host_tmp[]" style="width: 100%" id="host_tmp" multiple="multiple" size="8" class="multiple"></select>
						</div>

						<div class="left" style="padding-top: 40px;">
							<input type="button" value="&gt;" id="mv_h_r" class="button arrow-right" /><br />
							<input type="button" value="&lt;" id="mv_h_l" class="button arrow-left" />
						</div>

						<div class="left" style="width: 40%">
							<label for="host_name"><?php echo _('Selected').' '._('Hosts') ?></label><br />
							<select name="host_name[]" style="width: 100%" id="host_name" multiple="multiple" size="8" class="multiple"></select>
						</div>

				</tr>
				<tr data-show-for="services">
					<td colspan="3">
						<div class="left" style="width: 40%">
							<label for="service_tmp"><?php echo _('Available').' '._('Services') ?></label><br />
							<select name="service_tmp[]" style="width: 100%" id="service_tmp" multiple="multiple" size="8" class="multiple"></select>
						</div>

						<div class="left" style="padding-top: 40px;">
							<input type="button" value="&gt;" id="mv_s_r" class="button arrow-right" /><br />
							<input type="button" value="&lt;" id="mv_s_l" class="button arrow-left"  />
						</div>

						<div class="left" style="width: 40%">
							<label for="service_description"><?php echo _('Selected').' '._('Services') ?></label><br />
							<select name="service_description[]" style="width: 100%" id="service_description" multiple="multiple" size="8" class="multiple"></select>
						</div>
					</td>
				</tr>
			</table>

			<?php echo $report_options; ?>
		</form>
	</div>
</div>
