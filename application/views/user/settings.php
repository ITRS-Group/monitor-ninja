<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php

if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}

echo '<div>';
if (!empty($updated_str)) {
	echo '<div class="alert success"><span class="icon-16 x16-shield-ok"></span>' . $updated_str . '</div><br />';
}

if (!empty($available_setting_sections)) {

	echo form::open('user/save', array('id' => 'user_settings'));
	foreach ($available_setting_sections as $name => $setting_key) { ?>
		<div id="settings_<?php echo $name ?>">
			<table class="padd-table">
			<tr>
				<th colspan="2">
				<?php if(isset($sub_headings[$setting_key])) { ?>
					<?php echo $name ?> &nbsp; (<a target=_blank href="<?php echo $sub_headings[$setting_key][0]; ?>" title="<?php echo $sub_headings[$setting_key][1]; ?>">Read more here</a>)
				<?php } else { ?>
					<?php echo $name ?>
				<?php } ?>
				</th>
			</tr>
			<?php
				$i = 0;
				foreach ($settings[$setting_key] as $setting_name => $cfgkey) {
					$i++;
					$fieldname = str_replace('.', '_99_', $cfgkey[0]);
					echo '<tr class="'.($i%2 == 0 ? 'odd' : 'even').'"><td style="width: 200px">'.help::render($cfgkey[0])." &nbsp; <label for='$fieldname'>$setting_name</label></td><td>";

					switch($cfgkey[1]) {
						case 'int': case 'string':
							echo form::input($fieldname, $current_values[$cfgkey[0]]);
							break;
						case 'textarea':
							echo form::textarea($fieldname, $current_values[$cfgkey[0]], 'rows="6"');
							break;
						case 'bool':
							echo form::radio($fieldname, 1, isset($current_values[$cfgkey[0]]) && $current_values[$cfgkey[0]]!=false, 'id="radio_on_'.$fieldname.'"').' <label for="radio_on_'.$fieldname.'">'._('On').'</label> &nbsp;';
							echo form::radio($fieldname, 0, isset($current_values[$cfgkey[0]]) && $current_values[$cfgkey[0]]==false, 'id="radio_off_'.$fieldname.'"').' <label for="radio_off_'.$fieldname.'">'._('Off').'</label>';
							break;

						case 'select':
							if (isset($cfgkey[2]) && is_array($cfgkey[2])) {
								echo form::dropdown($fieldname, $cfgkey[2], $current_values[$cfgkey[0]]);
							}
							break;
					}
					echo '</td></tr>';
				}
			?>
			</table><br />
		</div>
	<?php
	}
	echo form::submit('save_config', _('Save'));
	echo form::close();
}?>
<br />

</div>
