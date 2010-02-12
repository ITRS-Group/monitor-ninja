<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
#if (isset($trends_data_str)) {
#	echo $trends_data_str;
#}
?>

<div id="trends_content">
	<div class="footnotes">
	<strong><?php echo $title ?></strong>: <?php
		if (count($objects) <= 5) {
			echo implode(', ', $objects);
		} else {
			# make list hidden by default ?>
		<span id="trends_show_hide_objects"><?php echo $label_click_to_view ?></span>
		<div id="trends_many_objects">
			<ul class="trends_objects">
			<?php
				foreach ($objects as $obj) { ?>
				<li><?php echo $obj ?></li>
				<?php
				}
			?>
			</ul>
		</div>
		<?php
		}

		?><br />

		<strong><?php echo $str_start_date ?> - <?php echo $str_end_date ?></strong> <br />
		<strong><?php echo $label_duration ?></strong>: <?php echo $duration ?>
	</div>

	<div id="tl" class="timeline-default" style="height: 300px;"></div>

	<div class="controls" id="controls"></div>
</div>

<?php echo form::open('', array('id' => 'dummy_form')); ?>
<table id="trends_filter_table">
<tr>
	<td><?php echo help::render('filter') ?></td>
	<td><?php echo $label_only_hard_events.":" ?></td>
	<td><?php echo form_Core::checkbox(array('name' => 'hard_filter'), 1, false) ?></td>
</tr>
<tr>
	<td colspan="2"><?php echo $label_filter_states.': '; ?></td>
	<td><?php echo form::dropdown(array('name' => 'filter_states'), $filter_states); ?></td>
</tr>
</table>
<?php echo form::close(); ?>

<?php echo (isset($avail_template) && !empty($avail_template)) ? $avail_template : ''; ?>
