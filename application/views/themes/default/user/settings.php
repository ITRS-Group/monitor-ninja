<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate;

if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}

echo '<div class="" style="margin:15px">';
if (!empty($updated_str)) {
	echo '<div id="saved_msg">'.$updated_str.'</div><br />';
}
echo html::anchor('change_password', $t->_('Change Password'));
echo "<br /><br />";
if (!empty($available_setting_sections)) {

	echo "<h1>".$title."</h1>";
	echo form::open('user/save', array('id' => 'user_settings'));
	foreach ($available_setting_sections as $name => $setting_key) { ?>
		<div id="settings_<?php echo $name ?>">
			<fieldset style="border:1px solid silver;width:20%;padding:10px">
			<legend><strong><?php echo $name ?></strong></legend>
			<?php
				foreach ($settings[$setting_key] as $setting_name => $cfgkey) {
					echo help::render($cfgkey[0]).' &nbsp;';
					echo $setting_name.'<br />';

					$fieldname = str_replace('.', '_99_', $cfgkey[0]);
					switch($cfgkey[1]) {
						case 'int': case 'string':
							echo form::input($fieldname, $current_values[$cfgkey[0]]).'<br /><br />';
							break;
						case 'bool':
							echo $t->_('ON').': '.form::radio($fieldname, 1, isset($current_values[$cfgkey[0]]) && $current_values[$cfgkey[0]]!=false ? true:false);
							echo " &nbsp; ";
							echo $t->_('OFF').': '.form::radio($fieldname, 0, isset($current_values[$cfgkey[0]]) && $current_values[$cfgkey[0]]!=false ? false:true);
							echo "<br /><br />";
							break;

						case 'select':
							if (isset($cfgkey[2]) && is_array($cfgkey[2])) {
								echo form::dropdown($fieldname, $cfgkey[2], $current_values[$cfgkey[0]]);
							}
							break;
					}
				}
			?>
			</fieldset><br />

		</div>
	<?php
	}
	echo form::submit('save_config', $t->_('Save'));
	echo form::close();
}?>
</div>
