<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="menu">
	<div id="close-menu" title="Hide menu" onclick="collapse_menu()"></div>
	<ul>
	<?php
		foreach ($links as $header => $link):
				echo '<li class="header">'.html::specialchars($header).'</li>';
				foreach ($link as $title => $url):
					echo '<li>'.html::image('application/views/themes/default/images/star.png',array('title' => html::specialchars($title), 'alt' => html::specialchars($title))).' '.html::anchor($url, html::specialchars($title)).'</li>';
				endforeach;
			endforeach;
		?>
	</ul>
</div>