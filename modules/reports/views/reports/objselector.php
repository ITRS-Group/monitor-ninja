<table class="setup-tbl obj_selector">
	<tr>
		<td colspan="3">
			<label for="report_type"><?php echo help::render('report-type').' '._('Report type'); ?></label><br />
			<select id="report_type" name="report_type">
				<option value="hostgroups" <?php echo $options['report_type'] === 'hostgroups' ? 'selected="selected"' : ''; ?>><?php echo _('Hostgroups') ?></option>
				<option value="hosts" <?php echo $options['report_type'] === 'hosts' ? 'selected="selected"' : ''; ?>><?php echo _('Hosts') ?></option>
				<option value="servicegroups" <?php echo $options['report_type'] === 'servicegroups' ? 'selected="selected"' : ''; ?>><?php echo _('Servicegroups') ?></option>
				<option value="services" <?php echo $options['report_type'] === 'services' ? 'selected="selected"' : ''; ?>><?php echo _('Services') ?></option>
			</select>
			<input type="button" id="sel_report_type" class="button select20" value="<?php echo _('Select') ?>" />
			<div id="progress"></div>
			&nbsp;
		</td>
	</tr>
	<tr id="filter_row">
		<td colspan="3">
			<?php echo help::render('filter').' '._('Filter:') ?><br />
			<input type="text" name="filter_field" id="filter_field" autocomplete=off size="10" value="">
			<input type="button" name="clear_filter" id="clear_filter" value="<?php echo _('Clear') ?>">
		</td>
	</tr>
	<tr>
		<td colspan="3">

		<div class="left" style="width: 40%">
			<label for="objects_tmp"><?php echo _('Available <span class="object-list-type">hostgroups</span>') ?></label><br />
			<select id="objects_tmp" multiple="multiple" size='8' style="width: 100%;" class="multiple">
			</select>
		</div>
		<div class="left" style="padding-top: 16px;">
			<input type="button" value="&gt;" id="mv_hg_r" class="button arrow-right" /><br />
			<input type="button" value="&lt;" id="mv_hg_l" class="button arrow-left" />
		</div>
		<div class="left" style="width: 40%">
			<label for="objects"><?php echo _('Selected <span class="object-list-type">hostgroups</span>') ?></label><br />
			<select name="objects[]" id="objects" multiple="multiple" size="8" style="width: 100%;" class="multiple">
			<?php
			$objs = $options['objects'];
			if (is_array($objs)) {
				foreach ($objs as $object) {
					echo '<option>' . $object . '</option>';
				}
			}
			?>
			</select>
		</div>
		<div class="clear"></div>
		</td>
	</tr>
</table>
