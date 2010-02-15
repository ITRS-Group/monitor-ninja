<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
#if (isset($trends_data_str)) {
#	echo $trends_data_str;
#}
?>

<div id="trends_content">
	<div class="footnotes" style="background-color: #ffffff; padding: 0px">
	<h1><?php echo $title ?>: <?php
		if (count($objects) <= 5) {
			echo implode(', ', $objects).'</h1>';
		} else {
			# make list hidden by default ?>
		<span id="trends_show_hide_objects"><?php echo $label_click_to_view ?></span></h1>
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

		?>

		<p style="margin-top: -13px; margin-bottom: 10px"><?php echo $str_start_date ?> - <?php echo $str_end_date ?>
		(<?php echo $label_duration ?>: <?php echo $duration ?>)</p>
	</div>

	<div id="tl" class="timeline-default" style="height: 300px;"></div>

	<div class="controls" id="controls"></div>

	<?php echo form::open('', array('id' => 'dummy_form')); ?>
<table id="trends_filter_table" style="float: left">
	<tr>
		<td><?php echo help::render('filter') ?>
		<?php echo form_Core::checkbox(array('name' => 'hard_filter'), 1, false) ?> <?php echo $label_only_hard_events ?></td>
		<td><?php echo $label_filter_states.': '; ?><?php echo form::dropdown(array('name' => 'filter_states','style' => 'margin-top: -2px'), $filter_states); ?></td>
	</tr>
</table>
<?php echo form::close(); ?>
</div>


<div style="clear:both"></div>
<?php echo (isset($avail_template) && !empty($avail_template)) ? $avail_template : ''; ?>
