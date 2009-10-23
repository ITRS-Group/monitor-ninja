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
		height: 180px;
		border: 1px solid #cdcdcd;
  }
	ul.thumbnails li.create {
		height: auto;
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
	ul.thumbnails li span.create {
		display: block;
		font-weight: bold;
		width: 203px;
		overflow: hidden;
		background: #cdcdcd url('/ninja/application/views/themes/default/css/default/images/bg.png') repeat-x;
		border-bottom: 1px solid #cdcdcd;
		margin:0px;
		padding: 3px 0px 3px 3px;
	}
	input { width: 128px }
	a.button {
    border: 1px solid #cdcdcd;
    padding: 3px 5px;
    font-weight: bold;
    background: #cdcdcd url('/ninja/application/views/themes/default/css/default/images/bg.png') repeat-x;
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

<div class="left" style="height: auto; margin-left: 1%">
	<div>
		<strong><?php echo $this->translate->_('Maps') ?> &nbsp;(<a class="view" href="/ninja/index.php/nagvis/configure"><?php echo $this->translate->_('configure') ?></a>)</strong>
		<ul class="thumbnails">
			<?php
			foreach ($maps as $map){
				echo '<li>';
				echo '<a class="edit" href="/ninja/index.php/nagvis/edit/'.$map.'" style="border: 0px">'.html::image($this->add_path('icons/12x12/box-config.png'),$this->translate->_('Edit')).'</a>';
				echo '<a class="delete" href="/ninja/index.php/nagvis/delete/'.$map.'" onclick="return confirm(\''.$this->translate->_('Are you sure you want to delete map '.$map.'?').'\')" style="border: 0px">'.html::image($this->add_path('icons/12x12/box-close.png'),$this->translate->_('Delete')).'</a>';
				echo '<a class="view" href="/ninja/index.php/nagvis/view/'.$map.'" style="border: 0px"><span>'.$map.'</span><img src="/nagvis/var/'.$map.'-thumb.png" alt="" /></a>';
				echo '</li>';
			}
			?>
			<li><a class="view" href="/ninja/index.php/nagvis/automap" style="border: 0px"><span><?php echo $this->translate->_('Automap') ?></span><img src="/nagvis/var/__automap-thumb.png" alt="" /></a></li>
			<li><a class="view" href="/ninja/index.php/nagvis/geomap" style="border: 0px"><span><?php echo $this->translate->_('Geomap') ?></span><img src="/ninja/geomap-thumb.png" alt="" /></a></li>
			<li class="create">
				<span class="create" href="#" style="border-bottom: 1px solid #cdcdcd"><?php echo $this->translate->_('Create map') ?></span>
				<form id="createmap" action="/ninja/index.php/nagvis/create" method="post">
					<input type="text" id="mapname" name="name" maxlength="25" />
					<a class="button" href="#" onclick="createmap(); return false" style="border: 1px solid #cdcdcd"><?php echo $this->translate->_('Create') ?></a></li>
				</form>
			</li>
		</ul>
	</div>
</div>

<div class="left" style="clear: left; margin-left: 1%">
	<strong><?php echo $this->translate->_('Rotation pools') ?></strong>
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