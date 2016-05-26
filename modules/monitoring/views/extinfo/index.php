<?php defined('SYSPATH') OR die('No direct access allowed.');

	$linkprovider = LinkProvider::factory();
	View::factory('extinfo/components/statebox', array(
		'object' => $object,
		'linkprovider' => $linkprovider
	))->render(true);

?>

<div class="left width-80 information-content">
<?php

	View::factory('extinfo/components/performance', array(
		'object' => $object
	))->render(true);

	if ($object->get_table() === 'hosts') {
		View::factory('extinfo/components/service_states', array(
			'object' => $object
		))->render(true);
	}

	View::factory('extinfo/components/output', array(
		'object' => $object
	))->render(true);

	View::factory('extinfo/components/operating', array(
		'object' => $object
	))->render(true);

	if (count($object->get_custom_variables())) {
		View::factory('extinfo/components/customvars', array(
			'object' => $object
		))->render(true);
	}

	View::factory('extinfo/components/timestamps', array(
		'object' => $object
	))->render(true);

	View::factory('extinfo/components/check', array(
		'object' => $object
	))->render(true);

/* @var $widgets widget_Base[] */
foreach ($widgets as $title => $widget) {
	echo '<div class="information-component information-component-fullwidth">';
    echo '<div class="information-component-title">' . $title . '</div>';
	echo $widget->render('index', false);
	echo '</div>';
}

?>
</div>

