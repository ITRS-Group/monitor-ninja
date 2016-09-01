<?php

	$store_cfg = Op5Config::instance()->getConfig('ninja_menu');
	$groups = op5auth::instance()->get_user()->get_groups();

	/* Use settings */
	$orientation = (isset($orientation)) ? $orientation : 'left';

	if (!$store_cfg)
		$store_cfg = array();

	$config = array();
	foreach ($store_cfg as $group => $sections) {
		if (in_array($group, $groups)) {
			foreach ($sections as $section => $items) {
				$config = array_merge($config, $items);
			}
		}
	}

	$render_menu = function ($menu, $parent_style = 'normal', $is_root = false) use (&$config, &$render_menu) {

		$branch = $menu->get_branch();
		$attr   = $menu->get_attributes();
		$icon   = $menu->get_icon();
		$style  = $menu->get_style();
		if($style === null)
			$style = $parent_style;
		$render = "";
		$format = "";

		if ($menu->is_separator()) {
			$render .= '<li class="menu-separator">' . $menu->get_label_as_html() . '</li>';
			return $render;
		}

		if (substr($icon, -4) == '.png')
			$icon = sprintf('<img src="%s">', htmlentities($icon));
		else if ($icon != '')
			$icon = sprintf('<span class="%s"></span>', htmlentities($menu->get_icon()));

		if (is_null($menu->get_href())) {
			$format = '<a%s>%s<span>%s</span></a>';
		} else {
			$format = '<a%s>%s<span>%s</span></a>';
			$href = $menu->get_href();

			if (!preg_match('/^http/', $href) && !preg_match('/^\//', $href) && !preg_match('/^#/', $href)) {
				$href = url::base(true) . $href;
			}
			$attr['href'] = $href;
		}

		$attributes = "";
		foreach ($attr as $name => $value) {
			$attributes .= sprintf(" %s=\"%s\"", htmlentities($name), htmlentities($value));
		}

		switch($style) {
			case 'image':
				/* Render an image menu */
				if (!is_null($menu->get_href())) {
					$render = sprintf('<a%s>%s</a>', $attributes, $icon);
				} else {
					$render = sprintf(
						'<a%s>%s<span>%s</span></a>',
						$attributes,
						$icon,
						$menu->get_label_as_html()
					);
				}
				break;
			default:
				$render .= sprintf($format, $attributes, $icon, $menu->get_label_as_html());
		}

		if ($menu->has_children()) {
			$class = $style . '-menu';
			$render .= "<ul class=\"$class\">";
			foreach ($branch as $child) {
				if (in_array($child->get_id(), $config)) { continue; }
				$cAttributes = $child->get_attributes();
				$render .= '<li tabindex="1">' . $render_menu($child, $style, false) . '</li>';
			}
			$render .= '</ul>';
		}
		return $render;
	};

	if(!isset($class))
		$class = "";

	$style = $menu->get_style();
	if($style === null)
		$style = 'normal';

	echo "<div class=\"$class menu menu-$orientation\">";
	echo $render_menu($menu, $style);
	echo "</div>";
