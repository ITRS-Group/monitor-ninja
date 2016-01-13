<?php defined('SYSPATH') or die('No direct access allowed.'); ?>
<div class="report_block">
	<h2><?php echo _('Report Mode'); ?></h2>
	<hr/>
	<table class="setup-tbl"><!--id="main_table"-->
		<tr>
			<td colspan="3">
			<input type="checkbox" name="host_name" id="show_all" value="<?php echo Report_options::ALL_AUTHORIZED ?>" <?php echo $options['objects'] === Report_options::ALL_AUTHORIZED?'checked="checked"':''?>/>
				<label for="show_all">Show all</label>
			</td>
		</tr>
	</table>
	<?php echo new View('reports/objselector'); ?>
</div>
<div class="report_block">
	<h2><?php echo _('Report Options'); ?></h2>
	<hr/>
	<table class="setup-tbl">
		<?php echo new View('summary/common-options'); ?>
		<tr>
			<td><?php echo help::render('include_downtime').' <label>'.form::checkbox('include_downtime', 1, $options['include_downtime']).' '._('Show downtime alerts'); ?></label></td>
			<td>&nbsp;</td>
			<td><?php echo help::render('include_flapping').' <label>'.form::checkbox('include_flapping', 1, $options['include_flapping']).' '._('Show flapping alerts'); ?></label></td>
		</tr>
		<tr>
			<td><?php echo help::render('include_process').' <label>'.form::checkbox('include_process', 1, $options['include_process']).' '._('Show process messages').'</label>'; ?></td>
		</tr>
		<tr>
			<td>
				<?php echo help::render('filter_output') ?>
				<label for="filter_output"><?php echo _('Filter output') ?></label><br />
				<input type="text" name="filter_output" id="filter_output" value="<?php echo $options['filter_output'] ?>" />
			</td>
			<td>&nbsp;</td>
			<td>
				<?php echo help::render('oldest_first').' <label>'.form::checkbox('oldest_first', 1, $options['oldest_first']).' '._('Older entries first').'</label>'; ?>
			</td>
		</tr>
		<tr>
			<td>
				<label for="summary_items"><?php echo _('Items to show') ?></label>
				<input type="text" name="summary_items" id="summary_items" value="<?php echo $options['summary_items'] ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="3">
			<?php echo form::submit('Update', 'Update'); ?>
			</td>
		</tr>
	</table>
</div>
