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

			$continue_fieldset = false;
			foreach ($options as $option) {
				if(
					$option_groups
					&& isset($option_groups['option_groups'])
					&& array_key_exists(
						$option->name,
						$option_groups['option_groups']
					)
				) {
					if($continue_fieldset ==
						$option_groups['option_groups'][$option->name]) {
						// do nothing, we're already in
						// the correct fieldset
					} else {
						// let's add a fancy legend
						echo "</fieldset>";
						$continue_fieldset =
							$option_groups['option_groups'][$option->name];
						$fieldset_classes = array();

						if(
							array_key_exists('classes', $option_groups)
							&& array_key_exists($continue_fieldset, $option_groups['classes'])
							&& is_array($option_groups['classes'][$continue_fieldset])
						) {
							$fieldset_classes = $option_groups['classes'][$continue_fieldset];
						}
						echo form::open_fieldset(
							array('class' => implode(" ", $fieldset_classes)));
						echo "<h2><label>".html::specialchars($continue_fieldset);
						if(in_array('can_be_toggled', $fieldset_classes, true)) {
							$toggle = new option('notused', 'toggle_me', 'notused', 'checkbox', array(), 1);
							echo $toggle->render_widget($key, $setting);
						}
						echo "</label></h2>";
					}
				} elseif($continue_fieldset !== false) {
					echo "</fieldset>";
					echo "<fieldset>";
				} else {
					echo "<fieldset>";
				}

				// wanted output:
				// label input
				// label input
				//
				// unwanted output:
				// label input label
				// input
				echo "<div>";
				if ($option instanceof option) {
					if($option->is_hidden()) {
						echo $option->render_widget($key, $setting);
					} else {
						echo $option->render_label($key);
						echo $option->render_widget($key, $setting);
						echo $option->render_help();
					}
				} elseif (is_string($option)) {
					echo $option;
				} else {
					echo _("Could not render option");
				}
				echo "</div>";

				if($continue_fieldset === false) {
					echo "</fieldset>";
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
