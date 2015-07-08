
<div class="main-menu">

	<?php

		$store_cfg = Op5Config::instance()->getConfig('ninja_menu');
		$groups = op5auth::instance()->get_user()->groups;

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

		$render_menu = function ($menu, $is_root = false) use (&$config, &$render_menu) {

			$branch = $menu->get_branch();
			$attr = $menu->get_attributes();

			$render = "";
			$format = "";
			$icon = "";

			$icon = ($menu->get_icon()) ? sprintf('<span class="%s"></span>', htmlentities($menu->get_icon())) : "";

			if (is_null($menu->get_href())) {

				$format = '<a%s>%s<span>%s</span></a>';

			} else {

				$format = '<a%s>%s<span>%s</span></a>';
				$href = $menu->get_href();

				if (!preg_match('/^http/', $href) && !preg_match('/^\//', $href)) {
					$href = url::base(true) . $href;
				}

				$attr['href'] = $href;

			}

			$attributes = "";
			foreach ($attr as $name => $value) {
				$attributes .= sprintf(" %s=\"%s\"", htmlentities($name), htmlentities($value));
			}

			$render .= sprintf($format, $attributes, $icon, $menu->get_label());

			if ($menu->has_children()) {
				$render .= '<ul>';
				foreach ($branch as $child) {

					if (in_array($child->get_id(), $config)) { continue; }

					$cAttributes = $child->get_attributes();
					$render .= '<li tabindex="1">' . $render_menu($child, false) . '</li>';

				}
				$render .= '</ul>';
			}

			return $render;

		};

		echo $render_menu($menu);

	?>

</div>
