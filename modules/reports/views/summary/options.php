<?php defined('SYSPATH') OR die('No direct access allowed.');
echo form::open(url::base(true) . 'summary/generate', array('class' => 'report_form'), array('report_id' => $options['report_id']));
?>
	<div class="standard setup-table">
		<table class="setup-tbl report_block auto_width">
			<tr>
				<td>
					<?php echo help::render('standardreport') ?>
					<label for="standardreport"><?php echo _('Standard type') ?></label>
				</td>
				<td></td>
				<td>
					<?php echo help::render('summary_items') ?>
					<label for="summary_items"><?php echo _('Items to show') ?></label>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo form::dropdown(array('name' => 'standardreport'), $options->get_alternatives('standardreport'), $options['standardreport']); ?>
				</td>
				<td></td>
				<td>
					<?php echo form::input(array('name' => 'summary_items', 'maxlength' => 7), $options['summary_items']) ?>
				</td>
			</tr>
			<tr>
				<td colspan="3"><?php echo form::input(array('id' => 'reports_submit_button', 'type' => 'submit', 'name' => '', 'class' => 'button create-report'), _('Show report')); ?></td>
			</tr>
		</table>
	</div>
<?php echo form::close();

echo form::open(url::base(true) . 'summary/generate', array('class' => 'report_form'), array('report_id' => $options['report_id']));
?>
	<div class="custom setup-table">
		<?php echo new View('reports/objselector'); ?>
		<h2><?php echo _('Report Settings'); ?></h2>
		<hr />
		<table id="report" class="setup-tbl custom report_block">
			<tr>
				<td>
					<label for="summary_type"><?php echo help::render('summary_type').' '._('Summary type') ?></label>
				</td>
				<td></td>
				<td>
					<?php echo help::render('summary_items') ?>
					<label for="summary_items"><?php echo _('Items to show') ?></label>
				</td>
			</tr>
			<tr>
				<td><?php echo form::dropdown('summary_type', $options->get_alternatives('summary_type'), $options['summary_type']) ?></td>
				<td style="width: 10px">&nbsp;</td>
				<td>
					<?php echo form::input(array('name' => 'summary_items', 'size' => 3, 'maxlength' => 3), $options['summary_items']) ?>
				</td>
			</tr>
			<?php echo new View('summary/common-options'); ?>
			<tr>
				<td>
					<?php echo help::render('skin') ?>
					<label for="skin" id="skin_lbl"><?php echo _('Skin') ?></label>
				</td>
				<td>&nbsp;</td>
				<td>
					<?php echo help::render('description') ?>
					<label for="description" id="descr_lbl"><?php echo _('Description') ?></label>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo form::dropdown(array('name' => 'skin'), ninja::get_skins(), $options['skin']); ?>
				</td>
				<td>&nbsp;</td>
				<td>
					<?php echo form::textarea('description', $options['description']); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3"><?php echo form::input(array('id' => 'reports_submit_button', 'type' => 'submit', 'name' => '', 'class' => 'button create-report'), _('Show report')); ?></td>
			</tr>
		</table>
	</div>
</form>
