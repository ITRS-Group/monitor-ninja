<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="new_schedule_area">
	<form action="schedule/schedule" id="new_schedule_report_form">

		<table class="setup-tbl padd-table schedule-report-tbl">
			<tr>
				<td>
                    <label for="type"><?php echo help::render('report-type-save', 'reports').' '._('Select report type') ?></label><br />
                    <?php echo form::dropdown(array('name' => 'type'), $defined_report_types); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="saved_report_id"><?php echo help::render('select-report', 'reports').' '._('Select report') ?></label><br />
                    <select name="saved_report_id" id="saved_report_id">
                            <option value=""> - <?php echo _('Select saved report') ?> - </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="recipients"><?php echo help::render('recipents', 'reports').' '._('Recipients') ?></label><br />
                    <input type="text" class="schedule" name="recipients" id="recipients" value="" />
                </td>
            </tr>
            <tr>
                <td>
                	<label for="filename"><?php echo help::render('filename', 'reports').' '._('Filename (defaults to pdf, may end in .csv)') ?></label><br /><input type="text" class="schedule" name="filename" id="filename" value="" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="description"><?php echo help::render('description', 'reports').' '._('Description') ?></label><br />
                    <textarea cols="31" rows="4" id="description" name="description"></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="attach_description"><?php echo help::render('attach_description', 'reports').' '._("Attach description") ?></label><br />
                    <select name="attach_description" id="attach_description">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                    </select>
                </td>
        	</tr>
             <tr>
                <td>
                	<label for="local_persistent_filepath"><?php echo help::render('local_persistent_filepath', 'reports').' '._("Save report on Monitor Server?") ?></label><br /><input type="text" class="schedule" name="local_persistent_filepath" id="local_persistent_filepath" value="" />
                </td>
            </tr>

            <tr>
                <td class="label sub-heading">Schedule</td>
            </tr>
            <tr>
                <td>
                    <div>Create and send at</div>
                    <div class="relative">
                        <input id="schedule-report-sendtime" class="time" name="report_time" value="12:00">
                        <div id="sendtime-options" class="sendtime-quickselect quickselect hide"></div>
                    </div>
                </td>
            </tr>
        	<tr>
                <td>
                    <div>Repeat every</div>
                    <div>
                        <input id="sch-repeat-no-box" class="num" type="number" min="1" value="1" name="every_no">
                        <select id="sch-repeat-text-box" class="repeat-text" name="every_text">
                            <option value="3">Day</option>
                            <option value="1">Week</option>
                            <option value="2">Month</option>
                        </select>
                    </div>
        		</td>
                </tr>
                <tr>
                    <td>
	                    <div id="sch-on" class="hide">On</div>
	                    <div id="sch-week-opt" class="hide">
	                    	<input class="hide" checked="checked" type="radio" name="week_on" value="">
	                        <input wno="1" name="week_on_day[]" tag="week-Monday" type="checkbox" value='{"day":1}' checked>
	                        <span wno="1" tag="Monday" class="selected-weekday">Mon</span>
	                        <input wno="2" name="week_on_day[]" tag="week-Tuesday" type="checkbox" value='{"day":2}'>
	                        <span wno="2" tag="Tuesday">Tue</span>
	                        <input wno="3" name="week_on_day[]" tag="week-Wednesday" type="checkbox" value='{"day":3}'>
	                        <span wno="3" tag="Wednesday">Wed</span>
	                        <input wno="4" name="week_on_day[]" tag="week-Thursday" type="checkbox" value='{"day":4}'>
	                        <span wno="4" tag="Thursday">Thu</span>
	                        <input wno="5" name="week_on_day[]" tag="week-Friday" type="checkbox" value='{"day":5}'>
	                        <span wno="5" tag="Friday">Fri</span>
	                        <input wno="6" name="week_on_day[]" tag="week-Saturday" type="checkbox" value='{"day":6}'>
	                        <span wno="6" tag="Saturday">Sat</span>
	                        <input wno="0" name="week_on_day[]" tag="week-Sunday" type="checkbox" value='{"day":0}'>
	                        <span wno="0" tag="Sunday">Sun</span>
	                    </div>
                        <div id="sch-month-opt" class="hide">
                            <div class="relative"><label><input id="sch-any-day-month" checked="checked" type="radio" name="sch-month-on" value='{"day_no":"1","day":"1"}'> the</label>
                                <select name="sch-on-no-box" id="sch-on-no-box" class="rec-on-no-box" value="">
                                    <option value="1">first</option>
                                    <option value="2">second</option>
                                    <option value="3">third</option>
                                    <option value="4">fourth</option>
                                    <option value="last">last</option>
                                </select>
                                <select name="sch-on-day-box" id="sch-on-day-box" class="rec-on-day-box" value="">
                                    <option value="1">Monday</option>
                                    <option value="2">Tuesday</option>
                                    <option value="3">Wednesday</option>
                                    <option value="4">Thursday</option>
                                    <option value="5">Friday</option>
                                    <option value="6">Saturday</option>
                                    <option value="0">Sunday</option>
                                </select>
                            </div>
                            <div class="relative"><label><input id="sch-first-day-month" type="radio" name="sch-month-on" value='{"day_no":"first","day":"first"}'> the first day of the month</label></div>
                            <div class="relative"><label><input id="sch-last-day-month" type="radio" name="sch-month-on" value='{"day_no":"last","day":"last"}'> the last day of the month</label></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span>
                            <input type="submit" class="button save" value="<?php echo _('Save') ?>" />
                            <input type="reset" class="button clear" value="<?php echo _('Clear') ?>" />
                        </span>
                    </td>
                </tr>			
		</table>

	</form>
</div>
