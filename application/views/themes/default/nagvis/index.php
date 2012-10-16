<?php defined('SYSPATH') OR die('No direct access allowed.');
$site = rtrim(Kohana::config('config.site_domain'), '/') . '/index.php/nagvis/';
$img_url = Kohana::config('config.site_domain').$this->add_path('css/default/images');
$acl = Nagvis_acl_Model::getInstance();
$m = new Nagvis_Maps_Model();
?>

<iframe style="display: none;" src="/nagvis/frontend/nagvis-js/index.php"></iframe>

<style type="text/css">
ul li.pool {
	border: 1px solid #cdcdcd;
}
ul li.pool a {
	display: block;
	width: 206px;
	padding: 3px 0px 3px 3px;
	font-weight: bold
}

strong {
	margin: 2px 0px 10px 0px;
	display: block;
}
input { width: 128px }
a.button {
	border: 1px solid #cdcdcd;
	padding: 3px 5px;
	font-weight: bold;
	background: #cdcdcd url('<?php echo $img_url; ?>/bg.png') repeat-x;
}

.widget {
	width: 200px;
	float: left;
	margin: 0 10px 10px 0;
}

.widget-content a {
	border: 0 !important;
}
</style>

<div class="left" style="height: auto; margin-left: 1%">
	<strong><?php echo _('Maps') ?> &nbsp;(<a class="view" href="<?php echo $site; ?>configure"><?php echo _('configure') ?></a>)</strong>
	<?php foreach ($maps as $map) { ?>
	<div class="widget">
		<div class="widget-header"><span><a href="<?php print $site."view/$map" ?>"><?php print $map ?></a></span><span class="widget-menu"><?php if($acl->isPermitted('Map', 'edit')) { ?><a class="widget-editlink" title="Edit this map" href="<?php print $site . "edit/$map" ?>"><img  alt="<?php echo _('Edit') ?>" src="/monitor/application/views/themes/default/icons/12x12/box-config.png"></a><?php } ?><?php if($acl->isPermitted('Map', 'delete')) { ?><a class="widget-closelink" title="Delete" onclick="return confirm('<?php print _("Are you sure you want to delete map $map?") ?>')" href="<?php print $site."delete/$map" ?>"><img alt="Delete" src="/monitor/application/views/themes/default/icons/12x12/box-close.png"></a><?php } ?></span></div>
		<div class="widget-content">
			<a href="<?php print $site."view/$map" ?>"><img src="<?php print $m->get_thumbnail($map); ?>" alt="" /></a>
		</div>
	</div>
	<?php } ?>
	<?php if($acl->isPermitted('AutoMap', 'view')) { ?>
	<div class="widget">
		<div class="widget-header"><span><a href="<?php echo $site; ?>automap"><?php echo _('Automap') ?></a></span></div>
		<div class="widget-content">
			<a href="<?php echo $site; ?>automap"><img src="/nagvis/var/__automap-thumb.png" alt="" /></a>
		</div>
	</div>
	<?php } ?>

	<?php if(!empty($pools)) { ?>
	<div class="widget" style="clear: left;">
		<strong><?php echo _('Rotation pools') ?></strong>
		<div>
			<ul>
				<?php foreach ($pools as $pool => $first_map) { ?>
					<li class="pool">
						<a href="<?php print "$site"."rotate/$pool/$first_map" ?>" style="border: 0"><?php print $pool ?></a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<?php } ?>

	<?php if($acl->isPermitted('Map', 'add')) { ?>
	<div class="widget" style="clear: left">
		<strong><?php echo _('Create map') ?></strong>
		<div>
			<form id="createmap" action="<?php echo $site; ?>create" method="post">
				<input type="text" id="mapname" name="name" maxlength="25" />
				<input type="submit" class="button" style="width: auto" value="<?php echo _('Create') ?>" />
			</form>
		</div>
	</div>
	<?php } ?>
</div>
<script type="text/javascript">
	$('#createmap').submit(function(evt) {
		var rxMapName = /^([-_0-9A-Za-zåäöÅÄÖ]+)$/;

		if (!$('#mapname').val().length)
			alert('Please enter the name of a map');
		else if (!rxMapName.test($('#mapname').val()))
			alert('Invalid map name');
		else
			return true;
		evt.preventDefault();
		return false;
	});
</script>
