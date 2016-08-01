<?php defined('SYSPATH') OR die('No direct access allowed.');

$rename_form = new Form_Model(
	LinkProvider::factory()->get_url('tac', 'rename_dashboard'),
	array(
		new Form_Field_Hidden_Model('dashboard_id'),
		new Form_Field_Text_Model('name', 'Name')
	)
);

$rename_form->set_values(array(
	'dashboard_id' => $dashboard->get_id(),
	'name' => $dashboard->get_name()
));

echo '<h1>Rename dashboard</h1>';
echo '<p>Rename the current dashboard</p>';
echo $rename_form->get_view();
