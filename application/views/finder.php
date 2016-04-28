<div class="finder">
<?php
	$lp = LinkProvider::factory();
	$lp->get_url('search', 'lookup');

	echo form::open($lp->get_url('search', 'lookup'), array('method' => 'get'));

	if ($query !== false && Router::$controller == 'search' && Router::$method == 'lookup') {
		echo form::input('query', html::specialchars($query));
	} else {
		echo form::input(array('name' => 'query', 'placeholder' => 'Find...'));
	}
?>
	<span class="finder-help" data-popover="help:search.search_help">?</span>
<?php
	echo form::close();
?>
</div>

