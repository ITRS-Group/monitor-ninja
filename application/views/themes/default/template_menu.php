<?php

	//include('demolinks.php');
	
	$in_menu = false;

	if (isset($links)) {

		$uri = str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		$uri = str_replace('?', '', $uri);
		$uri = preg_replace('~/+~', '/', $uri);

		foreach ($links as $section => $entry) {
			
			if (empty($entry)) {
					continue;
			}

			$i = 0;

			$in_menu = false;

			$linkstring = '';

			foreach ($entry as $name => $data) {

				$id = strtolower($section)."-".$data[1]."-".$i;

				if ($data[2] == 0) {

					// Do not add white-space, line-feeds or carriage returns in here, it will screw up JavaScript .children's and .nextSibling's

					$siteuri = url::site($data[0], null);
					$siteuri = preg_replace('~/+~', '/', $siteuri);
					
					if (strpos($siteuri, '?')) {
						$siteuri = substr($siteuri, 0, strpos($siteuri, '?'));
					}

					
					if (strpos($uri, $siteuri) === false) {
						$linkstring .= "<li class='nav-seg'>".html::anchor($data[0], 
							"<span class='icon-menu menu-".$data[1]."'></span>".
							"<span class='nav-seg-span'>".ucwords($name)."</span>",
							array('id' => $id, 'title' => ucwords($name), 'class' => 'ninja_menu_links')).'</li>';
					} else {
						$linkstring .= "<li class='active'>".html::anchor($data[0], 
							"<span class='icon-menu-dark menu-dark-".$data[1]."'></span>".
							"<span class='nav-seg-span'>".ucwords($name)."</span></li>",
							array('id' => $id, 'title' => ucwords($name), 'class' => 'ninja_menu_links')).'</li>';
						$in_menu = true;	
					}

					$i++;

				} // common external links
					elseif($data[2] == 1 || 
						($data[2] == 2 && Kohana::config('config.site_domain') == '/monitor/') || 
						($data[2] == 3 && Kohana::config('config.site_domain') != '/monitor/')) {

					$linkstring .= "<li class='nav-seg'><a href='".$data[0]." id='$id' title='".ucwords($name)."' target='_blank' class='ninja_menu_links'>".
						"<span class='icon-menu menu-".$data[1]."'></span>".
						"<span class='nav-seg-span'>".ucwords($name)."</span></a></li>";

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