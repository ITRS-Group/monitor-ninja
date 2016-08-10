<?php defined('SYSPATH') OR die('No direct access allowed.');

$form = new Form_Model(
    LinkProvider::factory()->get_url('tac', 'login_dashboard'),
    array(
        new Form_Field_Hidden_Model('dashboard_id')
    )
);

$form->set_values(array(
    'dashboard_id' => $dashboard->get_id()
));

$form->add_button(
    new Form_Button_Confirm_Model('save', 'Save')
);

$form->add_button(
    new Form_Button_Cancel_Model('cancel', 'Cancel')
);

echo '<h1>Set Login dashboard</h1>';
echo '<p>"<b>' . html::specialchars($dashboard->get_name()) . '</b>" set as Login dashboard<br>';
echo $form->get_view();