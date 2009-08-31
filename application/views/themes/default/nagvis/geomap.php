<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<!--
<style type="text/css">
	/* this is the iframe 100% height workaround used to fix object height in Firefox */
	html,
	body,
	#content,
	#content div,
	object {
		margin: 0;
		padding: 0;
		height: 100%;
	}
	object {
		display: block;
		width: 100%;
	}
</style>
-->

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
	{
		window.document["geomap"].addEventListener("DOMMouseScroll", handleWheel, false);
	}

	function handleWheel(event)
	{
		var app = window.document["geomap"];
		if (app)
		{
			var o = {x: event.clientX - parseInt($("#geomap").offset().left),
				y: event.clientY - parseInt($("#geomap").offset().top),
				delta: event.detail,
				ctrlKey: event.ctrlKey, altKey: event.altKey,
				shiftKey: event.shiftKey
			};

			app.handleWheel(o);
			event.preventDefault();
		}
	}
</script>
