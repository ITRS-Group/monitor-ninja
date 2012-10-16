<?php

	//include('demolinks.php');
	
	$in_menu = false;

	if (isset($links)) {

		$uri = str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		$uri = str_replace('?', '', $uri);

		foreach ($links as $section => $entry) {
			
			if (empty($entry)) {
					continue;
			}

			$i = 0;

			$in_menu = false;

			$linkstring = '';

			foreach ($entry as $name => $data) {

				//if (gettype(da) == 'array') {

				$id = strtolower($section)."-".$data[1]."-".$i;

				if ($data[2] == 0) {

					// Do not add white-space, line-feeds or carriage returns in here, it will screw up JavaScript .children's and .nextSibling's

					$siteuri = url::site($data[0], null);
					
					if (strpos($siteuri, '?')) {
						$siteuri = substr($siteuri, 0, strpos($siteuri, '?'));
					}

					if ($uri == $siteuri || $uri == $siteuri.'/index') {
						$linkstring .= html::anchor($data[0], "<li class='active'>".
							"<span class='icon-menu-dark menu-dark-".$data[1]."'></span>".
							"<span class='nav-seg-span'>".ucwords($name)."</span></li>",
							array('id' => $id, 'title' => ucwords($name), 'class' => 'ninja_menu_links'));
						$in_menu = true;
					} else {
						$linkstring .= html::anchor($data[0], "<li class='nav-seg'>".
							"<span class='icon-menu menu-".$data[1]."'></span>".
							"<span class='nav-seg-span'>".ucwords($name)."</span></li>",
							array('id' => $id, 'title' => ucwords($name), 'class' => 'ninja_menu_links'));
					}

					$i++;

				} // common external links
					elseif($data[2] == 1 || 
						($data[2] == 2 && Kohana::config('config.site_domain') == '/monitor/') || 
						($data[2] == 3 && Kohana::config('config.site_domain') != '/monitor/')) {

					$linkstring .= "<a href='".$data[0]." id='$id' title='".ucwords($name)."' target='_blank' class='ninja_menu_links'><li class='nav-seg'>".
						"<span class='icon-menu menu-".$data[1]."'></span>".
						"<span class='nav-seg-span'>".ucwords($name)."</span></li></a>";

				}


			}

			if ($in_menu == true) {
				echo "<ul id='".strtolower($section)."-menu' class='current-sup-menu' style='display: block'>";
			} else {
				echo "<ul id='".strtolower($section)."-menu'>";
			}

			echo $linkstring;

			echo "</ul>";

		}

		
	}