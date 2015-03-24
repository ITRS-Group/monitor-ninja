<?php

/*
The API for adding a menu item is this:

array(4) {
	["About"]=> <-- category
	array(3) {
	["The Ninja project"]=> <-- label
	array(3) {
		[0]=>
		string(64) "http://www.op5.org/community/plugin-inventory/op5-projects/ninja" <-- absolute or relative url
		[1]=>
		string(5) "ninja" <--- "id", or "geomap.png" (ninja/app..), or array('synergy', 'synergy.png') (ninja/modules/..)
		[2]=>
		int(3)	<---- 0 indicates internal link, /monitor/index.php will be prepended;
				<---- 1 indicates same host, https://monitor-server will be prepended
				<---- 2 indicates same host, but only if site_domain is /monitor/, licensed version
				<---- 3 indicates same host, but only if site_domain is not /monitor/, community version
	}
*/
	$in_menu = false;

	?>

	<div class="main-menu">

		<div class="logo">
			<?php echo brand::get(); ?>
		</div>

	<?php

	if (isset($links)) {
		echo "<ul>";

		foreach ($links as $section => $entry) {
			$normalized_section_name = str_replace(' ','-',strtolower($section));

			?>
				<li title="<?php echo ucwords( $normalized_section_name ); ?>" class="supermenu-button" id="<?php echo $normalized_section_name; ?>-button">
				<span class="icon-32 x32-<?php echo $normalized_section_name; ?>"></span>
			<?php

			$linkstring = '';
			if (strtolower($section) == 'about') {
				$linkstring .= '<li class="meta">'.Kohana::config('config.product_name') . ": " . config::get_version_info().'</li>';
				$linkstring .= '<li class="meta">'._('Page loaded').': '.date(nagstat::date_format()).'</li>';
			} elseif (empty($entry)) {
				continue;
			}

			$in_menu = false;

			if($entry) {
				foreach ($entry as $name => $data) {

					/* Never have a capital o in op5 */
					$formatted_name = preg_replace('/op5/i', 'op5', ucwords($name));

					if( is_array($data[1]) ) {
						$icon_image = $data[1][1];
						$module_name = $data[1][0];
						$id = $normalized_section_name."-".$data[1][0];
					} else {
						$icon_image = $data[1];
						$module_name = false;
						$id = $normalized_section_name."-".$data[1];
					}
					if ($data[2] == 0) {

						// Do not add white-space, line-feeds or carriage returns in here, it will screw up JavaScript .children's and .nextSibling's

						$linkstring .= "<li class='nav-seg'><a href='".rtrim(url::base(true), "/").$data[0]."' id='$id' class='ninja_menu_links'>";
						if (strpos($icon_image, '.') !== false)
							$linkstring .= "<span class='icon-menu' style='background-image: url(".ninja::add_path('icons/menu/'.$icon_image, $module_name).")'></span>";
						else
							$linkstring .= "<span class='icon-menu menu-".$icon_image."'></span>";
						$linkstring .= "<span class='nav-seg-span'>".$formatted_name."</span></a></li>";


					} elseif($data[2] == 1) {
						$linkstring .= "<li class='nav-seg'><a href='".$data[0]."' id='$id' class='ninja_menu_links'>";
						if (strpos($icon_image, '.') !== false)
							$linkstring .= "<span class='icon-menu' style='background-image: url(".ninja::add_path('icons/menu/'.$icon_image, $module_name).")'></span>";
						else
							$linkstring .= "<span class='icon-menu menu-".$icon_image."'></span>";
						$linkstring .= "<span class='nav-seg-span'>".$formatted_name."</span></a></li>";
					} // common external links
						elseif(($data[2] == 2 && Kohana::config('config.site_domain') == '/monitor/') ||
							($data[2] == 3 && Kohana::config('config.site_domain') != '/monitor/')) {

						$linkstring .= "<li class='nav-seg'><a href='".$data[0]."' id='$id' target='_blank' class='ninja_menu_links'>";
							if (strpos($icon_image, '.') !== false)
								$linkstring .= "<img src='".ninja::add_path('icons/menu/'.$icon_image, $module_name)."' />";
							else
								$linkstring .= "<span class='icon-menu menu-".$icon_image."'></span>";
							$linkstring .= "<span class='nav-seg-span'>".$formatted_name."</span></a></li>";

					}


				}
			}

			if ($in_menu == true) {
				echo "<ul id='$normalized_section_name-menu' class='current-sup-menu' style='display: block'>";
			} else {
				echo "<ul id='$normalized_section_name-menu'>";
			}

			echo $linkstring;

			echo "</ul></li>";

		}

		echo "</ul>";

	}

	?>

	</div>
