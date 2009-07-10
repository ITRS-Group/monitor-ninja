<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<style type="text/css">
	html,
	body,
	#content,
	#content div,
	iframe {
		margin: 0;
		padding: 0;
		height: 100%;
	}
	iframe {
		display: block;
		width: 100%;
	}
</style>

<div style="margin-left: 20px;">
	<iframe name="nagvis" src="/nagvis/nagvis/?map=<?php echo $map; ?>" width="100%" height="100%">
		Error : Can not load NagVis.
	</iframe>
</div>
