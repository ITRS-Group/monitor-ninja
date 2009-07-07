<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div style="margin-left: 20px;">
	<iframe name="nagvis" src="/nagvis" width="100%" height="700" frameborder="no">
		Error : Can not load NagVis.
	</iframe>
	<div>
		<ul>
			<li><a href="/nagvis/nagvis/index.php" target="nagvis">Index</a></li>
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
