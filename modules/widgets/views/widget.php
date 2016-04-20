<div class="widget <?php echo $classes; ?>" id="widget-<?php echo $widget_id; ?>" data-name="<?php echo $name; ?>" <?php echo $data_attributes; ?> data-instance_id="<?php echo $instance_id; ?>">

	<div class="widget-header">
		<span class="<?php echo $widget_id; ?>_editable" id="<?php echo $widget_id.'_title'; ?>"><?php echo $title; ?></span>
	</div>
	<?php if (!empty($options) && $editable) { ?>
		<div class="clear"></div>
		<div class="widget-editbox">
		<?php
			echo form::open('widget/save_widget_setting', array(
				'id' => $widget_id . '_form',
				'class' => 'renderable',
				'onsubmit' => 'return false;'
			));
			foreach ($options as $option) {
				echo "<fieldset>";
				if ($option instanceof option) {
					echo $option->render_label($instance_id);
					echo $option->render_widget($instance_id, $setting);
				} elseif (is_string($option)) {
					echo $option;
				} else {
					echo _("Could not render option");
				}

				echo "</fieldset>";
			}
			echo form::close();
		?>
		</div>
	<?php } ?>
	<div class="widget-content" style="overflow: auto;">
		<?php echo $content; ?>
	</div>
</div>
