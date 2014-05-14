<?php defined('SYSPATH') OR die('No direct access allowed.');

echo form::open('user/menu_edit', array('style' => 'margin: 16px', 'id' => 'editmenu_form', 'method' => 'get'));

if ($groups) { ?>
	<label><?php echo _('Group').': '; ?>
	<?php echo form::dropdown(array('name' => 'usergroup', 'id' => 'usergroup', 'style' => 'padding-right:10px'), array_merge(array('' => _('Select group')), array_combine(array_keys($groups), array_keys($groups))), $selected_group); ?></label>
<?php
} else {
	echo _("No limited users found in the system so there's nothing we can do here.");
}
echo form::close();

if (isset($selected_group) && !empty($selected_group)) {
	echo '<p style="padding-top:5px">'.$description.'</p>';
	echo form::open('user/menu_update', array( 'style' => 'margin: 16px' ));
	echo form::hidden('group', $selected_group);
	?>
<table style="width: 512px; padding-top: 10px;">
<tr>
	<th><?php echo _('Menu item') ?></th>
	<th style="width: 64px;"><?php echo sprintf(_('Remove for users in %s'), $selected_group) ?></th>
</tr><?php
foreach ($sections as $section) {
	$section_str = 'section_'.$section;
	if (isset($menu[$section_str]) && !empty($menu[$section_str])) {
		echo '<tr><td colspan="2">
			<cite>'.html::specialchars($menu_items[$section_str]).'</cite>
		</td></tr>'."\n";

		foreach ($menu[$section_str] as $pages) {
			if (!isset($menu_items[$pages])) {
				continue;
			}

			if ((isset($remove_items[$section]) && in_array($pages, $remove_items[$section])) || !isset($menu_base[$menu_items[$section_str]][$menu_items[$pages]])) {
				# removed items - dim out
				$url = $all_items[$menu_items[$section_str]][$menu_items[$pages]];
				$title = $menu_items[$pages];

				$icon = 'icons/menu/'.$url[1];
				/* By some reason, icons names containing . is treated as non-tiled images in menues... otherwise icons is avalible as .png */
				if( false === strpos($icon,'.') ) $icon .= '.png';

				echo '<tr id="'.$pages.'"><td style="padding-left: 8px">'.
					html::image($this->add_path($icon),array('title' => html::specialchars($title), 'alt' => html::specialchars($title), 'style' => 'padding-right:5px')).
					' '.html::specialchars($title).'</td>'."\n";
				echo '<td style="text-align: center">'.form::checkbox(array('name' => 'remove_items['.$section.'][]', 'id' => 'checkbox_'.$pages, 'class' => 'menubox'), $pages, true).'</td></tr>';
			} else {
				# visible items
				$cb_settings = array('name' => 'remove_items['.$section.'][]', 'id' => 'checkbox_'.$pages, 'class' => 'menubox');

				# check if anything should be disabled
				if (isset($untouchable_items) && is_array($untouchable_items) && in_array($pages, $untouchable_items)) {
					$cb_settings['disabled'] = 1;
				}
				$url = $menu_base[$menu_items[$section_str]][$menu_items[$pages]];
				$title = $menu_items[$pages];

				$icon = 'icons/menu/'.$url[1];
				/* By some reason, icons names containing . is treated as non-tiled images in menues... otherwise icons is avalible as .png */
				if( false === strpos($icon,'.') ) $icon .= '.png';

				echo '<tr id="'.$pages.'"><td style="padding-left: 8px">'.
					html::image($this->add_path($icon),array('title' => html::specialchars($title), 'alt' => html::specialchars($title), 'style' => 'padding-right:5px')).
					' '.html::specialchars($title).'</td>'."\n";
				echo '<td style="text-align: center">'.form::checkbox($cb_settings, $pages).'</td></tr>';
			}
		}
	}
} ?>
	<tr>
		<td colspan="2"><br />
			<?php echo form::submit('s1', _('Save')) ?>
		</td>
	</tr>
</table>
<br />
<?php echo form::close();
} ?>
