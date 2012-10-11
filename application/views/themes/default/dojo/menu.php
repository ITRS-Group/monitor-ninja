<?php

	//include('demolinks.php');
	
	if (isset($links)) {

		foreach ($links as $section => $entry) {
			echo "<ul id='".strtolower($section)."-menu'>";
			
			if (empty($entry)) {
					continue;
			}

			$i = 0;

			foreach ($entry as $name => $data) {

				//if (gettype(da) == 'array') {

				$id = strtolower($section)."-".$data[1]."-".$i;

				if ($data[2] == 0) {
					
					$query_string = explode('&',Router::$query_string);
					$unhandled_string = array(
						'?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_PENDING),
						'?hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED),
						'?service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED),
						'?hoststatustypes='.(nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE)
					);

					// Do not add white-space, line-feeds or carriage returns in here, it will scres up JavaScript .children's and .nextSibling's

					echo html::anchor($data[0], "<li class='nav-seg'>".
						html::image('application/views/themes/default/icons/menu/'.$data[1].'.png', array('class' => 'nav-seg-img')).
						"<span class='nav-seg-span'>".ucwords($name)."</span></li>",
						array('id' => $id, 'title' => ucwords($name), 'class' => 'ninja_menu_links'));

					$i++;

				} // common external links
						elseif($data[2] == 1) {

							echo "<a href='".$data[0]." id='$id' title='".ucwords($name)."' target='_blank' class='ninja_menu_links'><li class='nav-seg'>".
								html::image('application/views/themes/default/icons/menu/'.$data[1].'.png', array('class' => 'nav-seg-img')).
								"<span class='nav-seg-span'>".ucwords($name)."</span></li></a>";

						}
						// local external links
						elseif($data[2] == 2 && Kohana::config('config.site_domain') == '/monitor/') {
							
							echo "<a href='".$data[0]." id='$id' title='".ucwords($name)."' target='_blank' class='ninja_menu_links'><li class='nav-seg'>".
								html::image('application/views/themes/default/icons/menu/'.$data[1].'.png', array('class' => 'nav-seg-img')).
								"<span class='nav-seg-span'>".ucwords($name)."</span></li></a>";

						}
						// ninja external links
						elseif ($data[2] == 3 && Kohana::config('config.site_domain') != '/monitor/') {
							echo "<a href='".$data[0]." id='$id' title='".ucwords($name)."' target='_blank' class='ninja_menu_links'><li class='nav-seg'>".
								html::image('application/views/themes/default/icons/menu/'.$data[1].'.png', array('class' => 'nav-seg-img')).
								"<span class='nav-seg-span'>".ucwords($name)."</span></li></a>";
						}

				/*} elseif (gettype($entries[$i]) == 'string') {
					
					echo "<li class='list-separator'></li>";
				
				}*/

			}

			echo "</ul>";

		}

		
	}