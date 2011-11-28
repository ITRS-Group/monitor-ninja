<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if ($arguments['geomap_map'] == 'geomap') { ?>
<object	type="application/x-shockwave-flash" data="/nagvis/netmap/shell.swf"
		id="nagvis" width="100%" <?php if ($arguments['geomap_height'] > 0) echo 'style="height: '.$arguments['geomap_height'].'px;"'?>>
	<param name="base" value="/nagvis/netmap" />
	<param name="movie" value="/nagvis/netmap/shell.swf" />
	<param name="bgcolor" value="#ffffff" />
	<param name="wmode" value="opaque" />
	<param name="allowScriptAccess" value="sameDomain" />
</object>

<script type="text/javascript">
if (!(document.attachEvent))
	window.document["nagvis"].addEventListener("DOMMouseScroll", handleWheel, false);
else
	window.document["nagvis"].onmousewheel = handleWheel;

function handleWheel(event)
{
	var app = window.document["nagvis"];
	if (app)
	{
		if (!event)
			event = window.event;

		var o = {x: event.clientX - parseInt($("#nagvis").offset().left),
			y: event.clientY - parseInt($("#nagvis").offset().top),
			delta: (event.detail ? event.detail : -event.wheelDelta/40),
			ctrlKey: event.ctrlKey, altKey: event.altKey,
			shiftKey: event.shiftKey
		};

		app.handleWheel(o);
		if (event.preventDefault)
			event.preventDefault();
		else
			event.returnValue = false;
	}
}
</script>
<?php } else if ($arguments['geomap_map'] == 'automap') { ?>
<iframe name="nagvis" id="nagvis" src="/nagvis/nagvis/index.php?automap=1" width="100%" <?php if ($arguments['geomap_height'] > 0) echo 'style="height: '.$arguments['geomap_height'].'px;"'?>>
	Error : Can not load NagVis.
</iframe>
<?php } else { ?>
<iframe name="nagvis" id="nagvis" src="/nagvis/nagvis/?map=<?php echo $arguments['geomap_map']; ?>" width="100%" <?php if ($arguments['geomap_height'] > 0) echo 'style="height: '.$arguments['geomap_height'].'px;"'?>>
	Error : Can not load NagVis.
</iframe>
<?php } ?>
