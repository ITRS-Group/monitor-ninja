<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
 * This page is loaded via ajax from fancybox when selecting "new dashboard"
 */

$lp = LinkProvider::factory();
$form = new Form_Model(
	$lp->get_url('tac', 'new_dashboard'),
	array(
		new Form_Field_Group_Model('dashboard', array(
			new Form_Field_Text_Model('name', 'Name'),
			new Form_Field_Option_Model('layout', 'Layout', array(
				'3,2,1' => '321',
				'1,3,2' => '132'
			))
		))
	)
);

$form->set_values(array(
	'name' => $username . ' dashboard ',
	'layout' => '3,2,1'
));

$form->add_button(
	new Form_Button_Confirm_Model('save', 'Save')
);

$form->add_button(
	new Form_Button_Cancel_Model('cancel', 'Cancel')
);

echo '<h1>New dashboard</h1>';
echo $form->get_view();

