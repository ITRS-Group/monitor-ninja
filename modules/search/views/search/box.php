<div class="search">
	<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/result" method="get">
		<input autocomplete="off" type="text" name="query" id="query" placeholder="Search..." /><span data-popover="help:search.search_help" class="icon-16 x16-use_search"></span>
		<ul class="autocomplete"></ul>
	</form>
</div>