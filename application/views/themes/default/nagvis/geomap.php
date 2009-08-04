<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

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

<div style="margin-left: 20px;">
	<object	type="application/x-shockwave-flash" data="/nagvis/netmap/shell.swf" width="100%" height="100%">
		<param name="base" value="/nagvis/netmap" />
		<param name="movie" value="/nagvis/netmap/shell.swf" />
		<!--
		this works in most normal browsers and even MSIE,
		but Firefox (tested in 3.5) wants <embed> - what a shame!
		-->
		<embed src="/nagvis/netmap/shell.swf"
			base="/nagvis/netmap"
			width="100%" height="100%"
			type="application/x-shockwave-flash">
			<p>Adobe Flash Player is not installed</p>
		</embed>
	</object>
</div>
