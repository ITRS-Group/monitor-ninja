<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<iframe id="nagvis" name="nagvis" src="/nagvis/nagvis/" style="display: none;"></iframe>

<div class="left">
	<div>
		<strong>Maps</strong>
		<ul>
			<?php
			foreach ($maps as $map)
			{
				echo '<li>';
				echo '<a href="/ninja/index.php/nagvis/view/'.$map.'" onmouseover="show_thumbnail($(this));" onmouseout="hide_thumbnail();">'.$map.'</a>';
				echo '&nbsp;';
				echo '(<a href="/ninja/index.php/nagvis/edit/'.$map.'">edit</a>)';
				echo '</li>';
			}
			?>
			<li><a href="/ninja/index.php/nagvis/automap" onmouseover="show_thumbnail($(this));" onmouseout="hide_thumbnail();">Automap</a></li>
		</ul>
	</div>
</div>

<div class="left">
	<strong>Rotation pools</strong>
	<div>
		<ul>
			<?php
			foreach ($pools as $pool => $first_map)
			{
				echo '<li>';
				echo '<a href="/ninja/index.php/nagvis/rotate/'.$pool.'/'.$first_map.'">'.$pool.'</a>';
				echo '</li>';
			}
			?>
		</ul>
	</div>
</div>
