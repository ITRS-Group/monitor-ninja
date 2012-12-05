<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div style="margin-left: 1px;">
	<iframe name="nagvis" id="nagvis" src="/nagvis/frontend/nagvis-js/index.php?mod=Map&act=view&show=automap<?php echo $querystring; ?>" width="100%" onload="highlight();">
		Error : Can not load NagVis.
	</iframe>
</div>
