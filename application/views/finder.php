<div class="finder">
<?php
	$lp = LinkProvider::factory();
	$lp->get_url('search', 'lookup');

	echo form::open($lp->get_url('search', 'lookup'), array('method' => 'get'));

	if ($query !== false && Router::$controller == 'search' && Router::$method == 'lookup') {
		echo form::input('query', html::specialchars($query));
	} else {
		echo form::input(array('name' => 'query', 'placeholder' => 'Search...'));
	}

	echo help::render('search_help', 'search');
	echo form::close();
?>
</div>

