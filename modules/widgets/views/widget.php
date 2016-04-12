<div class="widget <?php echo $classes; ?>" id="widget-<?php echo $key; ?>" data-key="<?php echo $key; ?>" <?php echo $data_attributes; ?>>
	<div class="widget-header">
		<span class="widget-title"><?php echo $title; ?></span>
	</div>
	<?php if (!empty($options) && $editable) { ?>
		<div class="clear"></div>
	<?php } ?>
		<div class="widget-editbox">
<?php
			if (count($options) > 0) {
			echo form::open('widget/save_widget_setting', array(
				'class' => 'renderable widget-setting-form',
				'onsubmit' => 'return false;'
			));
			foreach ($options as $option) {
				echo "<fieldset>";
				if ($option instanceof option) {
					echo $option->render_label($key);
					echo $option->render_widget($key, $setting);
				} elseif (is_string($option)) {
					echo $option;
				} else {
					echo _("Could not render option");
				}

				echo "</fieldset>";
			}
			echo form::close();
			}
		?>
		</div>
	<div class="widget-content" style="overflow: auto;">
		<?php echo $content; ?>
	</div>
</div>
