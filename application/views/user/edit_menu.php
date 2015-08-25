<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

	<style>

		.menu-editor ul {
			margin-left: 24px;
		}

		.menu-editor ul li {
			margin: 4px;
		}

		.menu-editor ul li input {
			margin-right: 4px;
		}

		.menu-editor ul li input:checked + span {
			opacity: 0.5;
		}

		.menu-editor > ul > li {
			margin-left: 24px;
			float: left;
		}

		.menu-editor li > label > span > span {
			vertical-align: middle;
		}

	</style>

	<?php

	echo form::open('user/menu_edit', array('style' => 'margin: 16px', 'id' => 'editmenu_form', 'method' => 'get'));

	if ($groups) {

			echo '<label>' . _('Group').': ';

			echo form::dropdown(
				array(
					'name' => 'usergroup',
					'id' => 'usergroup',
					'style' => 'padding-right:10px'
				), array_merge(
					array('' => _('Select group')),
					array_combine(array_keys($groups), array_keys($groups))
				), $selected_group
			);

			echo '</label>';

	} else {

		echo _("No limited usergroups found in the system, onnly limited usergroups can have configured menues so there's nothing we can do here.");

	}

	echo form::close();

	if (isset($selected_group) && !empty($selected_group)) {

		echo '<p>Check the menu items that the should not be visible to the users of the <strong>' . $selected_group . '</strong> group.</p>';
		echo '<p><strong>Note that this will not restrict access, only hide the item in the menu.</strong></p>';

		echo form::open('user/menu_update', array( 'style' => 'margin: 16px 8px' ));
		echo form::hidden('group', $selected_group);

		$render_edit_menu = function ($menu, $is_root = false) use (&$render_edit_menu, &$untouchable, &$config, &$dynamics) {

			$branch = $menu->get_branch();
			$attr = $menu->get_attributes();

			$render = "";
			$icon = ($menu->get_icon()) ? sprintf('<span class="%s"></span>', htmlentities($menu->get_icon())) : "";

			$attributes = "";
			foreach ($attr as $name => $value) {
				$attributes .= sprintf(" %s=\"%s\"", htmlentities($name), htmlentities($value));
			}

			if ($is_root) {
				$format = '<label><span %s>%s <span>%s</span></span></label>';
			} else {
				if (in_array($menu->get_id(), $config)) {
					$format = '<label><input value="' . $menu->get_id() . '" name="removed[]" checked="checked" type="checkbox" /><span %s>%s <span>%s</span></span></label>';
				} else {
					$format = '<label><input value="' . $menu->get_id() . '" name="removed[]" type="checkbox" /><span %s>%s <span>%s</span></span></label>';
				}
			}

			$render .= sprintf($format, $attributes, $icon, $menu->get_label_as_html());

			if ($menu->has_children()) {
				if (!in_array($menu->get_id(), $dynamics)) {

					$render .= '<ul>';
					foreach ($branch as $child) {
						if (in_array($child->get_id(), $untouchable)) continue;
						$cAttributes = $child->get_attributes();
						$render .= '<li tabindex="1">' . $render_edit_menu($child, false) . '</li>';
					}
					$render .= '</ul>';

				}
			}

			return $render;

		};

		echo '<div class="menu-editor">';
		echo $render_edit_menu($menu, true);
		echo '</div>';

		echo '<input type="submit" value="Save new settings!">';
		echo form::close();

	}
