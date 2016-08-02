<?php defined('SYSPATH') OR die('No direct access allowed.');

$form = new Form_Model(
	LinkProvider::factory()->get_url('tac', 'delete_dashboard'),
	array(
		new Form_Field_Hidden_Model('dashboard_id')
	)
);

$form->set_values(array(
	'dashboard_id' => $dashboard->get_id()
));

$form->add_button(
	new Form_Button_Confirm_Model('yes', 'Yes')
);

$form->add_button(
	new Form_Button_Cancel_Model('cancel', 'Cancel')
);


echo '<h1>Delete dashboard</h1>';
echo '<p>Are you sure you want to delete the "<b>' . html::specialchars($dashboard->get_name()) . '</b>" dashboard?<br>';
echo 'Deleting a dashboard cannot be undone.</p><br>';
echo $form->get_view();
