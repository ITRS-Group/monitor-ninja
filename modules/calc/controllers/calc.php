<?php defined('SYSPATH') OR die('No direct access allowed.');

class Calc_Controller extends Ninja_Controller {
	public function index() {
		$lp = LinkProvider::factory();
		$form = new Form_Model(
			$lp->get_url('calc'),
			array(
				new Form_Field_Text_Model('expr', 'Expression', 'Something')
			)
		);
		$form->add_button(new Form_Button_Confirm_Model('submit', 'Submit'));

		try {
			$data = $form->process_data($_POST);
			$form->set_values($data);
		} catch(MissingValueException $e) {
			/* Don't care... */
		}

		$this->template->content = new View('calc', array(
			'form' => $form,
			'expr' => $form->get_value('expr', '')
		));
	}
}
