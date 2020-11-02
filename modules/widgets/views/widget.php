<?php
$refresh = 60;
if (isset($setting['refresh_interval']) && is_numeric($setting['refresh_interval'])) {
	$refresh = $setting['refresh_interval'];
}
?>
<div <?php if ($has_refresh) { echo ' data-refresh-interval="' . $refresh . '" '; }
	?> class="widget <?php echo $classes; ?>" id="widget-<?php echo $key; ?>" data-key="<?php echo $key; ?>" <?php echo $data_attributes; ?>>
	<div class="widget-header">
		<span class="widget-title"><?php echo $title; ?></span>
	</div>
	<?php if (!empty($options) && $editable) { ?>
		<div class="clear"></div>
	<?php } ?>
		<div class="widget-editbox">
<?php
			if($options instanceof Form_Model) {
				echo $options->get_view();
			} elseif (count($options) > 0) {
				echo form::open('widget/save_widget_setting', array(
					'class' => 'renderable widget-setting-form',
					'onsubmit' => 'return false;'
				));

				foreach ($options as $option) {
					if ($option instanceof option) {
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
				?>
				<fieldset>
				<input class="info state-background" type="submit" value="Save">
				<input class="info state-background cancel" type="reset" value="Cancel">
				</fieldset>
				<?php
				echo form::close();
			}

		?>
		</div>
	<div class="widget-content" style="overflow: auto;">
		<?php echo $content; ?>
	</div>
</div>
