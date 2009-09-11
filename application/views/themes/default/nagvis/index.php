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

	ul.thumbnails li a.view span,
	ul.thumbnails li a.create {
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

	ul.thumbnails li.create {
		padding-top: 59px;
		text-align: center;
	}

	ul.thumbnails li.create form {
		margin: 35px 0 4px;
	}

	ul.thumbnails li a.create {
		display: block;
		width: 200px;
	}
</style>

<script type="text/javascript">
	function createmap()
	{
		var rxMapName = /^([-_0-9A-Za-z]+)$/;

		if (!$('#mapname').val().length)
			alert('Please enter the name of a map');
		else if (!rxMapName.test($('#mapname').val()))
			alert('Invalid map name');
		else
			$('#createmap').submit();
	}
</script>

<div class="left">
	<div>
		<strong>Maps</strong> <a class="view" href="/ninja/index.php/nagvis/configure">[configure]</a>
		<ul class="thumbnails">
			<?php
			foreach ($maps as $map)
			{
				echo '<li>';
				echo '<a class="view" href="/ninja/index.php/nagvis/view/'.$map.'"><img src="/nagvis/var/'.$map.'-thumb.png" alt="" /><span>'.$map.'</span></a>';
				echo '<a class="edit" href="/ninja/index.php/nagvis/edit/'.$map.'">[edit]</a>';
				echo '<a class="delete" href="/ninja/index.php/nagvis/delete/'.$map.'" onclick="return confirm(\'Are you sure you want to delete map '.$map.'?\')">[x]</a>';
				echo '</li>';
			}
			?>
			<li><a class="view" href="/ninja/index.php/nagvis/automap"><img src="/nagvis/var/__automap-thumb.png" alt="" /><span>Automap</span></a></li>
			<li><a class="view" href="/ninja/index.php/nagvis/geomap"><img src="/ninja/geomap-thumb.png" alt="" /><span>Geomap</span></a></li>
			<li class="create"><?php echo html::image($this->add_path('icons/add.png')); ?>
				<form id="createmap" action="/ninja/index.php/nagvis/create" method="post">
					<input type="text" id="mapname" name="name" maxlength="25" />
				</form>
				<a class="create" href="#" onclick="createmap(); return false">Create</a></li>
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
