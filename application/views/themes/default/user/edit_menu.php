<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate;

# don't allow access for non admins
if (!$is_admin) { ?>
	<div class="left"><?php
	echo $noadmin_msg;
	?></div><?php
} else {
	# let admin select user
	echo form::open('user/menu_edit', array('id' => 'editmenu_form')); ?>
	<div class="left">
	<div>
		<br />
		<?php echo html::anchor(Router::$controller, $this->translate->_('Back')) ?>
		<br />
		<h3><?php echo $this->translate->_('Edit user menu') ?></h3>
	</div>
	<?php
	if (!empty($limited_users)) {
		echo $select_user_message.'<br />';
		echo $this->translate->_('Username').': ';
		echo form::dropdown(array('name' => 'username', 'id' => 'editmenu_username', 'style' => 'padding-right:10px'), $limited_users, $selected_user);
	} else {
		echo $this->translate->_("No limited users found in the system so there's nothing we can do here.");
	}
	echo form::close();

	# display menu with checkboxes to be able to remove menu items
	if (isset($selected_user) && !empty($selected_user)) {
		echo '<br /><p style="padding-top:5px">'.$description.'</p>';
		echo form::open('user/menu_update');
		echo form::hidden('username', $selected_user);
		?>
	<table style="width:250px;padding-top:10px;">
	<tr>
		<th><?php echo $t->_('Menu item') ?></th>
		<th><?php echo $t->_('Remove') ?></th>
	</tr><?php
	foreach ($sections as $section) {
		$section_str = 'section_'.$section;
		if (isset($menu[$section_str]) && !empty($menu[$section_str])) {
			echo '<tr><td colspan="2"><li style="list-style-type:square">
				<cite>'.html::specialchars($menu_items[$section_str]).'</cite>
			</li></td></tr>'."\n";

			foreach ($menu[$section_str] as $pages) {
				if (!isset($menu_items[$pages])) {
					continue;
				}

				if ((isset($remove_items[$section]) && in_array($pages, $remove_items[$section])) || !isset($menu_base[$menu_items[$section_str]][$menu_items[$pages]])) {
					# removed items - dim out
					$url = $all_items[$menu_items[$section_str]][$menu_items[$pages]];
					$title = $menu_items[$pages];
					echo '<tr style="color:#c0c0c0" id="'.$pages.'"><td style="border-bottom:1px dotted black"><li style="list-style-type:none">'.
						html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title), 'style' => 'padding-right:5px')).
						' '.html::specialchars($title).'</li></td>'."\n";
					echo '<td>'.form::checkbox(array('name' => 'remove_items['.$section.'][]', 'id' => 'checkbox_'.$pages, 'class' => 'menubox'), $pages, true).'</td></tr>';
				} else {
					# visible items
					$cb_settings = array('name' => 'remove_items['.$section.'][]', 'id' => 'checkbox_'.$pages, 'class' => 'menubox');

					# check if anything should be disabled
					if (isset($untouchable_items) && is_array($untouchable_items) && in_array($pages, $untouchable_items)) {
						$cb_settings['disabled'] = 1;
					}
					$url = $menu_base[$menu_items[$section_str]][$menu_items[$pages]];
					$title = $menu_items[$pages];

					echo '<tr id="'.$pages.'"><td style="border-bottom:1px dotted black"><li style="list-style-type:none">'.
						html::image($this->add_path('icons/menu/'.$url[1].'.png'),array('title' => html::specialchars($title), 'alt' => html::specialchars($title), 'style' => 'padding-right:5px')).
						' '.html::specialchars($title).'</li></td>'."\n";
					echo '<td>'.form::checkbox($cb_settings, $pages).'</td></tr>';
				}
			}
		}
	} ?>
		<tr>
			<td colspan="2"><br />
				<?php echo form::submit('s1', $t->_('Save')) ?>
			</td>
		</tr>
	</table>
	<br />
	<?php echo form::close();
	} ?>
	</div>
<?php
}
