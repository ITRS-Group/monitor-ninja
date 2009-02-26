<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="box">
	<p>Welome to the forum</p>

	<p>
		Blaj, blaj...
	</p>
</div>
<ul>
<?php foreach ($links as $title => $url): ?>
	<li><?php echo html::anchor($url, html::specialchars($title)) ?></li>
<?php endforeach ?>
</ul>