<?php defined('SYSPATH') OR die('No direct access allowed.');

$form = new Form_Model(
	LinkProvider::factory()->get_url('tac', 'rename_dashboard'),
	array(
		new Form_Field_Hidden_Model('dashboard_id'),
		new Form_Field_Text_Model('name', 'Name')
	)
);

$form->set_values(array(
	'dashboard_id' => $dashboard->get_id(),
	'name' => $dashboard->get_name()
));

$form->add_button(
	new Form_Button_Confirm_Model('save', 'Save')
);

$form->add_button(
	new Form_Button_Cancel_Model('cancel', 'Cancel')
);

echo '<h1>Rename dashboard</h1>';
echo '<p>Rename the current dashboard</p>';
$form->get_view()->render(TRUE);
