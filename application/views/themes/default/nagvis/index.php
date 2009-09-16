<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<iframe id="nagvis" name="nagvis" src="/nagvis/nagvis/" style="display: none;"></iframe>

<style type="text/css">

	strong {
		margin: 2px 0px 10px 0px;
		display: block;
	}
	ul li.pool {
		border: 1px solid #cdcdcd;
	}
	ul li.pool a {
		display: block;
		width: 206px;
		background: #cdcdcd url('/ninja/application/views/themes/default/css/default/images/bg.png') repeat-x;
		padding: 3px 0px 3px 3px;
		font-weight: bold;
	}
	ul.thumbnails li {
		display: inline;
		float: left;
		margin-right: 5px;
		margin-bottom: 5px;
		width: 206px;
		border: 1px solid #cdcdcd;
	}
	ul.thumbnails li a.view {
		display: block;
		width: 206px;
	}
	ul.thumbnails li a.view span,
	ul.thumbnails li a.create {
		display: block;
		font-weight: bold;
		width: 203px;
		overflow: hidden;
		background: #cdcdcd url('/ninja/application/views/themes/default/css/default/images/bg.png') repeat-x;
		border-bottom: 1px solid #cdcdcd;
		margin:0px;
		padding: 3px 0px 3px 3px;
	}
	ul.thumbnails li a.view img,
	ul.thumbnails li a.view2 img,
	ul.thumbnails li a.create img {
		padding: 3px;
		display: block;
	}
	ul.thumbnails li a.delete,
	ul.thumbnails li a.edit {
		position: relative;
		float: right;
		left: -16px;
		margin-top: 3px;
		margin-bottom: -15px;
		z-index: 100;
	}
	ul.thumbnails li a.delete {
		left: 9px;
	}
	ul.thumbnails li.create form {
		margin: 10px 5px;
	}
	ul.thumbnails li a.create {
		display: block;
		width: 203px;
		border-bottom: 1px solid #cdcdcd;
	}
	input { width: 180px }
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
			foreach ($maps as $map){
				echo '<li>';
				echo '<a class="edit" href="/ninja/index.php/nagvis/edit/'.$map.'" style="border: 0px">'.html::image($this->add_path('icons/12x12/box-config.png'),'Edit').'</a>';
				echo '<a class="delete" href="/ninja/index.php/nagvis/delete/'.$map.'" onclick="return confirm(\'Are you sure you want to delete map '.$map.'?\')" style="border: 0px">'.html::image($this->add_path('icons/12x12/box-close.png'),'Delete').'</a>';
				echo '<a class="view" href="/ninja/index.php/nagvis/view/'.$map.'" style="border: 0px"><span>'.$map.'</span><img src="/nagvis/var/'.$map.'-thumb.png" alt="" /></a>';
				echo '</li>';
			}
			?>
			<li><a class="view" href="/ninja/index.php/nagvis/automap" style="border: 0px"><span>Automap</span><img src="/nagvis/var/__automap-thumb.png" alt="" /></a></li>
			<li><a class="view" href="/ninja/index.php/nagvis/geomap" style="border: 0px"><span>Geomap</span><img src="/ninja/geomap-thumb.png" alt="" /></a></li>
			<li class="create">
				<a class="create" href="#" onclick="$('#createmap').submit(); return false" style="border: 0px">Create map</a>
				<?php //echo html::image($this->add_path('icons/add.png')); ?>
				<form id="createmap" action="/ninja/index.php/nagvis/create" method="post">
					<input type="text" id="mapname" name="name" maxlength="25" />
				</form>
				<a class="create" href="#" onclick="createmap(); return false">Create</a></li>
			</li>
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
				echo '<li class="pool">';
				echo '<a href="/ninja/index.php/nagvis/rotate/'.$pool.'/'.$first_map.'" style="border: 0px">'.$pool.'</a>';
				echo '</li>';
			}
			?>
		</ul>
	</div>
</div>