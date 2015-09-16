<div class="search">
	<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/result" method="get">
		<input autocomplete="off" type="text" name="query" id="query" placeholder="Search..." />
		<ul class="autocomplete"></ul>
		<?php echo help::render('search_help', 'search'); ?>
	</form>
</div>