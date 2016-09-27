<?php

/**
 * Thrown when no command definition is found
 */
class UnknownCommandException extends Exception {}

/**
 * Naemon command helper
 */
class cmd
{
	/**
	 * @param $object Object_Model
	 * @param $command string
	 * @param $text string
	 * @return string|null
	 */
	static function cmd_link(Object_Model $object, $command, $text) {
		return html::anchor(
			sprintf(
				"cmd?command=%s&table=%s&object=%s",
				urlencode($command),
				urlencode($object->get_table()),
				urlencode($object->get_key())
			),
			html::specialchars($text)
		);
	}

	/**
	 * Retrieves a command definition from an object-set based on name
	 *
	 * @param $name string
	 * @param $set ObjectSet_Model
	 * @throws UnknownCommandException
	 * @return $command The command definition if found
	 */
	static function get_definition ($name, ObjectSet_Model $set) {

		$commands = array();
		if ($set->count() === 1) {
			$object = $set->one();
			$commands = $object->list_commands();
		} else {
			$obj_class = $set->class_obj();
			$commands = $obj_class::list_commands_static();
		}

		if (!array_key_exists($name, $commands)) {

			/**
			 * Technically, the command might not exist, but the only way
			 * to reach this code is to
			 *  - submit a command by manually typing the address/
			 *    modifying the data from within a POST form
			 *  - we supply incorrect links in the GUI
			 *  - have too little command rights and click a link
			 *    which is not based on the currently logged in user's
			 *    authorization levels (e.g. through a link pasted
			 *    by a colleague, or whatever)
			 *
			 * We want to optimize for the path that is the most common
			 * and useful for users, which is the last one.
			 */

			throw new UnknownCommandException(
				"You have insufficient rights to submit the command '$command'" .
				" on table '$table'. Please verify your user's rights" .
				" for '$table' commands. Aborting without any commands applied."
			);

		}

		return $commands[$name];

	}

	/**
	 * Retrieves the Form_Model for a command definition
	 *
	 * @param $command string The command name
	 * @param $definition array The command definition
	 * @param $set ObjectSet_Model The set to execute the command on
	 * @return Form_Model
	 */
	static function get_form ($command, $definition, $set) {
		$action = LinkProvider::factory()->get_url('cmd', 'obj');
		$parameters = $definition['params'];
		$defaults = array(
			'command' => $command,
			'query' => $set->get_query()
		);

		$form = new Form_Model($action, array(
			new Form_Field_Hidden_Model('command'),
			new Form_Field_Hidden_Model('query'),
			new Form_Button_Confirm_Model('submit', 'Submit command'),
			new Form_Button_Cancel_Model('close', 'Close')
		));

		if (count($set) > 1) {
			$form->add_field(
				new Form_Field_HTMLDecorator_Model(
					"<div class='alert info'>This will run '$command' for " . count($set) . " " . $set->get_table() . "</div>"
				)
			);
		}

		if ($definition['description']) {
			$form->add_field(
				new Form_Field_HTMLDecorator_Model(
					'<p>' . html::specialchars($definition['description']) . '</p>'
				)
			);
		}

		foreach ($parameters as $name => $parameter) {

			$parameter = array_merge(array(
				'name' => $name,
				'description' => '',
				'option' => array(),
				'default' => false
			), $parameter);

			$group = new Form_Field_Group_Model('Groupname');
			if ($parameter['description']) {
				$group->add_field(
					new Form_Field_HTMLDecorator_Model(
						'<p>' . html::specialchars($parameter['description']) . '</p>'
					)
				);
			}

			$defaults[$name] = $parameter['default'];

			switch ($parameter['type']) {
			case 'string':
				$group->add_field(
					new Form_Field_Text_Model($name, $parameter['name'])
				);
				break;
			case 'int':
			case 'float':
			case 'duration':
				$group->add_field(
					new Form_Field_Number_Model($name, $parameter['name'])
				);
				break;
			case 'time':
				if ($parameter['default']) {
					$defaults[$name] = date('Y-m-d H:i:s', strtotime($parameter['default']));
				}
				$group->add_field(
					new Form_Field_Datetime_Model($name, $parameter['name'])
				);
				break;
			case 'bool':
				$group->add_field(
					new Form_Field_Boolean_Model($name, $parameter['name'])
				);
				break;
			case 'select':
				$group->add_field(
					new Form_Field_Option_Model($name, $parameter['name'], $parameter['option'])
				);
				break;
			case 'object':
				$set = ObjectPool_Model::get_by_query($parameter['query']);
				$group->add_field(
					new Form_Field_ORMObject_Model($name, $parameter['name'], array($set->get_table()))
				);
				break;
			}

			if (isset($parameter['condition'])) {
				list($relation, $value) = explode('=', $parameter['condition']);
				if ($value === 'true') $value = true;
				elseif ($value === 'false') $value = false;
				elseif (is_numeric($value)) $value = floatval($value);
				$group = new Form_Field_Conditional_Model($relation, $value, $group);
			}

			$form->add_field($group);
		}

		$form->set_values($defaults);
		return $form;

	}

}
