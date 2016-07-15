<?php

$manifest = array_merge_recursive($manifest, array(
	"saved_filters" => array(
		array(
			'display_column' => 'filter_name',
			'query' => '[saved_filters] filter_name ~~ "%s"'
		)
	),
));
