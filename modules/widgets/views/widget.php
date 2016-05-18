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
				if ($option instanceof Fieldset_Model) {
					$fieldset = $option; // less confusing, I hope
					if(count($fieldset)) {
						$attributes = $fieldset->get_attributes();
						echo form::open_fieldset($attributes);

						echo "<h2><label>".html::specialchars($fieldset->get_legend());
						if(array_key_exists('class', $attributes)
							&& preg_match('/\bcan_be_toggled\b/', $attributes['class'])
						) {
							$toggle = new option('notused', 'toggle_me', 'notused', 'checkbox', array(), 1);
							echo $toggle->render_widget($key, $setting);
						}
						echo "</label></h2>";

						foreach($fieldset as $option) {
							if($option->is_hidden()) {
								echo $option->render_widget($key, $setting);
							} else {
								echo "<div>";
								echo $option->render_label($key);
								echo $option->render_widget($key, $setting);
								echo $option->render_help();
								echo "</div>";
							}
						}
						echo "</fieldset>";
					}
				} elseif ($option instanceof option) {
					if($option->is_hidden()) {
						echo $option->render_widget($key, $setting);
					} else {
						echo "<div>";
						echo "<fieldset>";
						echo $option->render_label($key);
						echo $option->render_widget($key, $setting);
						echo $option->render_help();
						echo "</fieldset>";
						echo "</div>";
					}
				} elseif (is_string($option)) {
					echo $option;
				} else {
					echo _("Could not render option");
				}
			}
			echo form::close();
			}
		?>
		</div>
	<div class="widget-content" style="overflow: auto;">
		<?php echo $content; ?>
	</div>
</div>
