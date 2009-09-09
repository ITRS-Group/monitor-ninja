<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<iframe id="nagvis" name="nagvis" src="/nagvis/nagvis/" style="display: none;"></iframe>

<style type="text/css">
	ul.thumbnails li {
		display: inline;
		float: left;
		margin: 5px;
		width: 200px;
	}

	ul.thumbnails li a.view {
		display: block;
		width: 200px;
	}

	ul.thumbnails li a.view span {
		display: block;
		width: 200px;
		text-align: center;
		overflow: hidden;
	}

	ul.thumbnails li a.edit {
		position: relative;
		left: 165px;
		top: -15px;
	}

	ul.thumbnails li a.delete {
		position: relative;
		left: 167px;
		top: -15px;
	}
</style>

<div class="left">
	<div>
		<strong>Maps</strong>
		<ul class="thumbnails">
			<?php
			foreach ($maps as $map)
			{
				echo '<li>';
				echo '<a class="view" href="/ninja/index.php/nagvis/view/'.$map.'"><img src="/nagvis/var/'.$map.'-thumb.png" alt="" /><span>'.$map.'</span></a>';
				echo '<a class="edit" href="/ninja/index.php/nagvis/edit/'.$map.'">[edit]</a>';
				echo '<a class="delete" href="/ninja/index.php/nagvis/delete/'.$map.'">[x]</a>';
				echo '</li>';
			}
			?>
			<li><a class="view" href="/ninja/index.php/nagvis/automap"><img src="/nagvis/var/__automap-thumb.png" alt="" /><span>Automap</span></a></li>
			<li><a class="view" href="/ninja/index.php/nagvis/geomap"><img src="/ninja/geomap-thumb.png" alt="" /><span>Geomap</span></a></li>
		</ul>
	</div>
</div>

<div class="left" style="clear: left;">
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
