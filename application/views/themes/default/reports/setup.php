<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>
<div id="response"></div>
<div id="progress"></div>
<div class="report-page">
	<div class="setup-table">
	<!--<div style="top: 10px; position: relative; float: right;">
	<a href="/monitor/cgi-bin/avail.cgi"><img src="icons/32x32/square-old.png" alt="Old availabilty" title="Old availability" /></a>
	</div>-->
	<?php if (isset($saved_reports) && count($saved_reports)>0 && !empty($saved_reports)) {
			echo form::open('reports/index', array('id' => 'saved_report_form', 'style' => 'margin-top: 7px;'));
			 ?>
			<div style="width: 100%;">
				<!--	onchange="check_and_submit(this.form)"	-->
				<select name="report_id" id="report_id">
					<option value=""> - <?php echo $this->translate->_('Select saved report') ?> - </option>
			<?php	$sched_str = "";
					foreach ($saved_reports as $info) {
						$sched_str = in_array($info->id, $scheduled_ids) ? " ( *".$scheduled_label."* )" : "";
						if (in_array($info->id, $scheduled_ids)) {
							$sched_str = " ( *".$scheduled_label."* )";
							$title_str = $scheduled_periods[$info->id]." ".$title_label;
						} else {
							$sched_str = "";
							$title_str = "";
						}

						echo '<option title="'.$title_str.'" '.(($report_id == $info->id) ? 'selected="selected"' : '').
							' value="'.$info->id.'">'.($type == 'avail' ? $info->report_name : $info->sla_name).$sched_str.'</option>'."\n";
					}  ?>
				</select>
				<input type="hidden" name="type" value="<?php echo $type ?>"/>
				<input type="submit" class="button select" value="<?php echo $label_select ?>" name="fetch_report" />
				<input type="button" class="button new" value="<?php echo $label_new ?>" name="new_report"
						title="<?php echo $new_saved_title ?>" id="new_report" />
				<input type="button" class="button delete" value="Delete" name="delete_report"
						title="<?php echo $label_delete ?>" id="delete_report" />
		<?php
		if (isset($is_scheduled) && $is_scheduled) { ?>
			<span id="autoreport_periods"><?php echo $json_periods ?></span>
			<span id="is_scheduled" title="<?php echo $is_scheduled_clickstr ?>">
				<?php echo $is_scheduled_report ?>
				<a href="#" id="show_scheduled" class="help">[<?php echo $edit_str ?>]</a>
			</span>
			<div id="schedule_report" style='width: 100%'>
				<table id="schedule_report_table" style='width: 100%; margin-top: 10px' class="white-table">
				<?php
				if (!empty($scheduled_info)) { ?>
				<thead>
					<tr id="schedule_header" class="setup">
						<th style='width: 9%'><?php echo $label_sch_interval ?></th>
						<th style='width: 20%'><?php echo $label_sch_recipients ?></th>
						<th style='width: 20%'><?php echo $label_sch_filename ?></th>
						<th style='width: 50%'><?php echo $label_sch_description ?></th>
						<th style='width: 1%'></th>
					</tr>
				</thead>
				<tbody>
			<?php	$recipients = false;
					foreach ($scheduled_info as $schedule) {
						$recipients = str_replace(' ', '', $schedule->recipients);
						$recipients = str_replace(',', ', ', $recipients);	?>
					<tr id="report-<?php echo $schedule->id ?>" class="odd">
					<td class="period_select" title="<?php echo $label_dblclick ?>" id="period_id-<?php echo $schedule->id ?>"><?php echo $schedule->periodname ?></td>
					<td class="iseditable" title="<?php echo $label_dblclick ?>" id="recipients-<?php echo $schedule->id ?>"><?php echo $recipients ?></td>
					<td class="iseditable" title="<?php echo $label_dblclick ?>" id="filename-<?php echo $schedule->id ?>"><?php echo $schedule->filename ?></td>
					<td class="iseditable_txtarea" title="<?php echo $label_dblclick ?>" id="description-<?php echo $schedule->id ?>"><?php echo utf8_decode($schedule->description) ?></td>
					<td class="delete_report" id="<?php echo $schedule->id ?>" style='text-align: right'><?php echo html::image($this->add_path('icons/12x12/cross.gif')) ?></td>
				</tr>
		<?php 		}
				} ?>
				</tbody>
				</table>
			</div>
	<?php	} ?>
	</div>
	<?php
			echo form::close();
		} ?>
	</div>
<?php	echo form::open('reports/generate', array('id' => 'report_form'));
		# @@@FIXME: onsubmit="return check_form_values(this);"
		?>
		<div class="setup-table">
				<input type='hidden' name='new_report_setup' value='1'/>
				<input type="hidden" name="type" value="<?php echo $type ?>"/>
			  	<table summary="Select report type" class="white-table" style="width: 100%; border-right: 1px solid #dcdccd; border-bottom: 1px solid #dcdccd"><!--id="main_table"-->
					<colgroup>
						<col style="width: 10px" />
						<col style="width: 350px" />
						<col style="width: 20px" />
						<col style="width: 350px" />
						<col style="width: auto" />
					</colgroup>
					<tr class="setup">
						<th colspan="5"><?php echo $label_create_new ?></th>
					</tr>
					<tr class="odd">
						<td class="bl"><?php echo help::render('report-type') ?></td>
						<td colspan="4">
							<select name="report_type" id="report_type" onchange="set_selection(this.value);">
								<option value="hostgroups"><?php echo $label_hostgroups ?></option>
								<option value="hosts"><?php echo $label_hosts ?></option>
								<option value="servicegroups"><?php echo $label_servicegroups ?></option>
								<option value="services"><?php echo $label_services ?></option>
							</select>
							<input type="button" id="sel_report_type" class="button select20" onclick="set_selection(document.forms['report_form'].report_type.value);" value="<?php echo $label_select ?>" />
						</td>
					</tr>
					<tr id="hostgroup_row" class="even">
						<td class="bl">&nbsp;</td>
						<td>
							<strong><?php echo $label_available.' '.$label_hostgroups ?>:</strong> &nbsp;<br />
							<select name="hostgroup_tmp[]" id="hostgroup_tmp" multiple="multiple" size='8' class="multiple" />
							</select>
						</td>
						<td class="move-buttons">
							<input type="button" value="&gt;" id="mv_hg_r" class="button arrow-right" /><br />
							<input type="button" value="&lt;" id="mv_hg_l" class="button arrow-left" style="margin-top: 7px" />
						</td>
						<td valign="bottom">
							<strong><?php echo $label_selected.' '.$label_hostgroups ?></strong><br />
							<select name="hostgroup[]" id="hostgroup" multiple="multiple" size="8" class="multiple">
							</select>
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr id="servicegroup_row" class="even">
					<td class="bl">&nbsp;</td>
						<td>
							<strong><?php echo $label_available.' '.$label_servicegroups ?>:</strong> &nbsp;<br />
							<select name="servicegroup_tmp[]" id="servicegroup_tmp" multiple="multiple" size='8' class="multiple" />
							</select>
						</td>
						<td class="move-buttons">
							<input type="button" value="&gt;" id="mv_sg_r" class="button arrow-right" /><br />
							<input type="button" value="&lt;" id="mv_sg_l" class="button arrow-left" style="margin-top: 7px" />
						</td>
						<td valign="bottom">
							<strong><?php echo $label_selected.' '.$label_servicegroups ?>:</strong><br />
							<select name="servicegroup[]" id="servicegroup" multiple="multiple" size="8" class="multiple" />
							</select>
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr id="host_row_2" class="even">
					<td class="bl">&nbsp;</td>
						<td>
							<strong><?php echo $label_available.' '.$label_hosts ?>:</strong> &nbsp;<br />
							<select name="host_tmp[]" id="host_tmp" multiple="multiple" size="8" class="multiple">
							</select>
						</td>
						<td class="move-buttons">
							<input type="button" value="&gt;" id="mv_h_r" class="button arrow-right" /><br />
							<input type="button" value="&lt;" id="mv_h_l" class="button arrow-left" style="margin-top: 7px" />
						</td>
						<td valign="bottom">
							<strong><?php echo $label_selected.' '.$label_hosts ?>:</strong><br />
							<select name="host_name[]" id="host_name" multiple="multiple" size="8" class="multiple" />
							</select>
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr id="service_row_2" class="even">
					<td class="bl">&nbsp;</td>
						<td>
							<strong><?php echo $label_available.' '.$label_services ?>:</strong> &nbsp;<br />
							<select name="service_tmp[]" id="service_tmp" multiple="multiple" size="8" class="multiple" />
							</select>
						</td>
						<td class="move-buttons">
							<input type="button" value="&gt;" id="mv_s_r" class="button arrow-right" /><br />
							<input type="button" value="&lt;" id="mv_s_l" class="button arrow-left" style="margin-top: 7px"  />
						</td>
						<td valign="bottom">
							<strong><?php echo $label_selected.' '.$label_services ?>:</strong><br />
							<select name="service_description[]" id="service_description" multiple="multiple" size="8" class="multiple" />
							</select>
						</td>
						<td>&nbsp;</td>
					</tr>
				</table>
		</div>

		<div class="setup-table" id="settings_table">
			<table summary="0" style="width: 100%; border-right: 1px solid #dcdccd" class="white-table">
				<colgroup>
					<col style="width: 10px;" />
					<col style="width: 280px" />
					<col style="width: auto" />
				</colgroup>
				<tr class="odd">
					<td><?php echo help::render('reporting_period') ?></td>
					<td><?php echo $label_report_period ?></td>
					<td><?php echo form::dropdown(array('name' => 'report_period'), $report_periods, $selected); ?></td>
				</tr>
				<tr id="display" style="display: none; clear: both;" class="even">
					<td>&nbsp;</td>
					<td colspan="2">
						<table summary="Reporting time" style="margin-left: -4px">
							<tr>
								<td style="width: 279px;"><?php echo $label_startdate ?></td>
								<td>
									<span style="width: 115px; margin-left: -4px" id="start_time_tmp"><?php echo $label_click_calendar ?></span>
									<input type="text" id="cal_start" name="cal_start" maxlength="10" autocomplete="off" class="date-pick" title="<?php echo $label_startdate_selector ?>" />
									<input type="hidden" name="start_time" id="start_time" value=""/>
								</td>
							</tr>
							<tr>
								<td><?php echo $label_enddate ?></td>
								<td>
									<span style="width: 115px; margin-left: -4px" id="end_time_tmp"><?php echo $label_click_calendar ?></span>
									<input type="text" id="cal_end" name="cal_end" maxlength="10" autocomplete="off" class="date-pick" title="<?php echo $label_enddate_selector ?>" />
									<input type="hidden" name="end_time" id="end_time" value="" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="even">
					<td><?php echo help::render('report_time_period') ?></td>
					<td><?php echo $label_rpttimeperiod ?></td>
					<td>
						<select name="rpttimeperiod">
						<option value=""></option>
							<?php echo $reporting_periods ?>
						</select>
					</td>
				</tr>
				<tr class="odd">
					<td><?php echo help::render('scheduled_downtime') ?></td>
					<td colspan="2">
						<input type="checkbox" class="checkbox" value="1" id="scheduleddowntimeasuptime" name="scheduleddowntimeasuptime"
								onchange="toggle_label_weight(this.checked, 'sched_downt')" <?php echo $scheduled_downtime_as_uptime_checked ?>/>
						<label for="scheduleddowntimeasuptime" id="sched_downt"><?php echo $label_scheduleddowntimeasuptime ?></label>
					</td>
				</tr>
				<tr class="even">
					<td><?php echo help::render('initial_states') ?></td>
					<td colspan="2">
						<input type="checkbox" class="checkbox" value="1" id="assumeinitialstates" name="assumeinitialstates"
								onchange="show_state_options(this.checked);toggle_label_weight(this.checked, 'assume_initial');" <?php print $assume_initial_states_checked ?>/>
						<label for="assumeinitialstates" id="assume_initial"><?php echo $label_assumeinitialstates ?></label>
					</td>
				</tr>
				<tr id="assumed_host_state" class="odd">
					<td><?php echo help::render('first_assumed_host') ?></td>
					<td>&nbsp; &nbsp; &nbsp; &nbsp;<?php echo $label_initialassumedhoststate ?></td>
					<td>
						<select name="initialassumedhoststate">
						<?php
							foreach($initial_assumed_host_states as $host_state_value => $host_state_txt)
							{
								$sel = ($host_state_value == $initial_assumed_host_state_selected ? 'selected="selected"':'');
								print "<option value='$host_state_value' $sel>$host_state_txt</option>";
							}
						 ?>
						</select>
					</td>
				</tr>
				<tr class="even" id="assumed_service_state">
					<td><?php echo help::render('first_assumed_service') ?></td>
					<td>&nbsp; &nbsp; &nbsp; &nbsp;<?php echo $label_initialassumedservicestate ?></td>
					<td>
						<select name="initialassumedservicestate">
						<?php
							foreach($initial_assumed_service_states as $service_state_value => $service_state_txt)
							{
								$sel = ($service_state_value == $initial_assumed_service_state_selected ? 'selected="selected"':'');
								print "<option value='$service_state_value' $sel>$service_state_txt</option>";
							}
						 ?>
						</select>
					</td>
				</tr>
				<tr class="odd">
					<td><?php echo help::render('stated_during_downtime') ?></td>
					<td colspan="2">
						<input type="checkbox" class="checkbox" value="1" id="assumestatesduringnotrunning" name="assumestatesduringnotrunning"
								onchange="toggle_label_weight(this.checked, 'assume_progdown');" <?php echo $assume_states_during_not_running_checked; ?>/>
						<label for="assumestatesduringnotrunning" id="assume_progdown"><?php echo $label_assumestatesduringnotrunning ?></label>
					</td>
				</tr>
				<tr class="even">
					<td><?php echo help::render('include_soft_states') ?></td>
					<td colspan="2">
						<input type="checkbox" class="checkbox" value="1" id="includesoftstates" name="includesoftstates"
								onchange="toggle_label_weight(this.checked, 'include_softstates');" <?php echo $include_soft_states_checked; ?>/>
						<label for="includesoftstates" id="include_softstates"><?php echo $label_includesoftstates ?></label>
					</td>
				</tr>
				<tr class="odd">
					<td><?php echo help::render('use_average') ?></td>
					<td>
						&nbsp;&nbsp;<?php echo $label_sla_calc_method ?>
					</td>
					<td>
						<select name='use_average'>
							<option value='0' <?php print $use_average_no_selected ?>><?php echo $label_avg_sla ?></option>
							<option value='1' <?php print $use_average_yes_selected ?>><?php echo $label_avg ?></option>
						</select>
					</td>
				</tr>
				<tr class="even">
					<td><?php echo help::render('use_alias') ?></td>
					<td colspan="2">
						<input type="checkbox" class="checkbox" value="1" id="use_alias" name="use_alias"
								onchange="toggle_label_weight(this.checked, 'usealias');" <?php print $use_alias_checked; ?>/>
						<label for="use_alias" id="usealias"><?php echo $label_use_alias ?></label>
					</td>
				</tr>
				<tr class="odd">
					<td><?php echo help::render('csv_format') ?></td>
					<td colspan="2">
						<input type="checkbox" class="checkbox" value="1" id="csvoutput" name="csvoutput"
								onchange="toggle_label_weight(this.checked, 'csvout');" <?php print $csv_output_checked; ?>/>
						<label for="csvoutput" id="csvout"><?php echo $label_csvoutput ?></label>
					</td>
				</tr>
				<tr class="even">
					<td><?php echo help::render('save_report') ?></td>
					<td>
						<input type="hidden" name="saved_report_id" value="<?php echo $report_id ?>" />
						<input type="checkbox" class="checkbox" name="save_report_settings" id="save_report_settings" value="1" onclick="toggle_field_visibility(this.checked, 'report_save_information');toggle_label_weight(this.checked, 'save_report_label')" />
						<label for="save_report_settings" id="save_report_label"><?php echo $label_save_report ?></label>
					</td>
					<td>
						<span id="report_save_information">
						<input type="text" name="report_name" id="report_name" value=""  maxlength="255" style="width: 155px" />
						</span>
					</td>
				</tr>
			</table>
		</div>
		<?php if ($type == 'sla') { ?>
		<div class="setup-table" id="enter_sla">
			<table class="white-table" style="margin-top: 10px; border-right: 1px solid #dcdccd">
				<tr class="setup">
					<th colspan="14"><?php echo $label_enter_sla ?></th>
				</tr>

				<tr class="odd">
					<td><?php echo help::render('enter-sla') ?></td>
					<?php foreach ($months as $key => $month) { ?>
					<td style="width: 40px">
						<?php echo html::image($this->add_path('icons/16x16/copy.png'),
							array(
								'id' => 'month_'.($key+1),
								'alt' => $label_propagate,
								'title' => $label_propagate,
								'style' => 'cursor: pointer; margin-bottom: -4px',
								'class' => 'autofill')
							) ?>
						<?php echo $month ?><br />
						<input type="text" size="2" style="width: 30px" name="month_<?php echo ($key+1) ?>"
								value="<?php echo arr::search($report_info, 'month_'.($key + 1))!==false ? $report_info['month_'.($key + 1)] : "" ?>" maxlength="6" /> % &nbsp;
					</td>
					<?php	} ?>
					<td style="width: auto">&nbsp;</td>
				</tr>
			</table>
		</div>
		<?php } ?>

	<div class="setup-table">
		<input id="reports_submit_button" type="submit" name="" value="<?php echo $label_create_report ?>" class="button create-report" style="margin-top: 7px" />
	</div>

	</form>
</div>