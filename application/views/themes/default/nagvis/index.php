<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="left">
	<div>
		<ul>
			<?php
			foreach ($maps as $map)
			{
				echo '<li>';
				echo '<a href="/ninja/index.php/nagvis/view/'.$map.'">'.$map.'</a>';
				echo '&nbsp;';
				echo '(<a href="/ninja/index.php/nagvis/edit/'.$map.'">edit</a>)';
				echo '</li>';
			}
			?>
			<li><a href="/ninja/index.php/nagvis/automap">Automap</a></li>
		</ul>
	</div>
</div>
