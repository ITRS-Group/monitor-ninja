<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php

if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}

echo '<div>';
if (!empty($updated_str)) {
	echo '<div id="saved_msg">'.html::image($this->add_path('/icons/16x16/shield-ok.png'),array('alt' => '', 'style' => 'margin-bottom: -3px; margin-right: 4px')).$updated_str.'</div><br />';
}

echo "<h1>".$title."</h1><br />";

if ($is_admin) {
	echo '<div style="padding-bottom:20px">'.help::render('edit_menu').' &nbsp;'.
	html::anchor(Router::$controller.'/menu_edit', _('Edit user menu')).'</div>';
}

if (Auth::instance()->authorized_for('own_user_change_password')) {
	echo '<div>'.html::anchor('change_password', _('Change Password')).'</div><br />';
}

if (!empty($available_setting_sections)) {	

	echo form::open('user/save', array('id' => 'user_settings'));
	foreach ($available_setting_sections as $name => $setting_key) { ?>
		<div id="settings_<?php echo $name ?>">
			<table>
			<tr><th colspan="2"><?php echo $name ?></th></tr>
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
							echo form::textarea($fieldname, $current_values[$cfgkey[0]]);
							break;
						case 'bool':
							echo form::radio($fieldname, 1, isset($current_values[$cfgkey[0]]) && $current_values[$cfgkey[0]]!=false ? true:false, 'id="radio_on_'.$fieldname.'"').' <label for="radio_on_'.$fieldname.'">'._('On').'</label> &nbsp;';
							echo form::radio($fieldname, 0, isset($current_values[$cfgkey[0]]) && $current_values[$cfgkey[0]]!=false ? false:true, 'id="radio_off_'.$fieldname.'"').' <label for="radio_off_'.$fieldname.'">'._('Off').'</label>';
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
