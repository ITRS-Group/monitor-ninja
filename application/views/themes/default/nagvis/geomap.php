<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div style="margin: 0 14px; padding: 14px 0;">
	<object	type="application/x-shockwave-flash" data="/nagvis/netmap/shell.swf"
		id="geomap" width="100%" height="625">
		<param name="base" value="/nagvis/netmap" />
		<param name="movie" value="/nagvis/netmap/shell.swf" />
		<param name="bgcolor" value="#ffffff" />
		<param name="wmode" value="opaque" />
		<param name="allowScriptAccess" value="sameDomain" />
	</object>
</div>

<script type="text/javascript">
	if (!(document.attachEvent))
		window.document["geomap"].addEventListener("DOMMouseScroll", handleWheel, false);
	else
		window.document["geomap"].onmousewheel = handleWheel;

	function handleWheel(event)
	{
		var app = window.document["geomap"];
		if (app)
		{
			if (!event)
				event = window.event;

			var o = {x: event.clientX - parseInt($("#geomap").offset().left),
				y: event.clientY - parseInt($("#geomap").offset().top),
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
