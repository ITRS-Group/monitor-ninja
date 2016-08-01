<?php defined('SYSPATH') OR die('No direct access allowed.');

$delete_form = new Form_Model(
	LinkProvider::factory()->get_url('tac', 'delete_dashboard'),
	array(
		new Form_Field_Hidden_Model('dashboard_id')
	)
);

$delete_form->set_values(array(
	'dashboard_id' => $dashboard->get_id()
));

echo '<h1>Delete dashboard</h1>';
echo '<p>Are you sure you want to delete the "<b>' . html::specialchars($dashboard->get_name()) . '</b>" dashboard?<br>';
echo 'Deleting a dashboard cannot be undone.</p><br>';
echo $delete_form->get_view();
