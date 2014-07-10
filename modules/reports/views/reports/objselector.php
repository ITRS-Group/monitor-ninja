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
			&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<?php
				$datatype = substr( $options['report_type'], 0, -1 );
				if ( strlen( $datatype ) < 1 ) {
					$datatype = "hostgroup";
				}
			?>
			Please select what objects to base the report on<br />
			<select data-filterable data-type="<?php echo $datatype; ?>" name="objects[]" id="objects" multiple="multiple">
				<?php
				//size="8" style="width: 100%;" class="multiple"
				$objs = $options['objects'];
				if (is_array($objs)) {
					foreach ( $objs as $object ) {
						echo '<option value="' . $object . '">' . $object . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
</table>
