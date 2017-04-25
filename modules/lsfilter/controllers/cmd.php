<?php

/**
 * Execute commands on ORM objects
 */
class Cmd_Controller extends Ninja_Controller {

	/**
	 * Show a form for submitting a command on a single object
	 */
	public function index() {
		$this->template->content = $this->add_view('cmd/index');
		$this->template->content->error = false;

		/* Accept both get and post, get has precedance */
		$command = $this->input->get('command', $this->input->post( 'command', false ) );
		$table = $this->input->get('table', $this->input->post( 'table', false ) );
		$object_keys = $this->input->get('object', $this->input->post( 'object', false ) );

		/* Object keys can be both array or single, if single, convert ot array */
		if(!is_array($object_keys)) {
			$object_keys = array($object_keys);
		}

		try {
			$pool = ObjectPool_Model::pool($table);
			/* @var $pool ObjectPool_Model */
		} catch(ORMException $e) {
			request::send_header(400);
			$this->template->content->error = $e->getMessage();
			return;
		}

		$set = $pool->none();
		/* @var $set ObjectSet_Model */
		foreach($object_keys as $key) {
			$set = $set->union($pool->set_by_key($key));
		}

		if (count($set) === 0) {
			$this->template->content->error_level = 'info';
			$this->template->content->error = "The " . $pool->get_table() . " you were trying to execute '" . $command . "' on were not found. Attempted to find " . $pool->get_table() . " with the names: " . html::get_delimited_string($object_keys);

			return;
		}

		$this->template->content->set = $set;
		$this->template->content->table = $table;
		$this->template->content->command = $command;

		$widget = widget::get(new Ninja_widget_Model(array(
			'page' => Router::$controller,
			'name' => 'listview',
			'widget' => 'listview',
			'username' => op5auth::instance()->get_user()->get_username(),
			'friendly_name' => 'Objects',
			'setting' => array(
				'query'=>$set->get_query(),
				'limit' => intval(config::get('pagination.default.items_per_page'))
			)
		)));
		$widget->set_fixed(array(
			'listview_link'=>false
		));
		widget::set_resources($widget, $this);

		$this->template->content->objs_widget = $widget;

		$count = count($set);

		$commands = array();
		if($count == 1) {
			$object = $set->one();
			$commands = $object->list_commands();
		} else {
			$obj_class = $set->class_obj();
			$commands = $obj_class::list_commands_static();
		}
		if(!array_key_exists($command, $commands)) {
			request::send_header(400);

			// Technically, the command might not exist, but the only way
			// to reach this code is to
			//  - submit a command by manually typing the address/
			//    modifying the data from within a POST form
			//  - we supply incorrect links in the GUI
			//  - have too little command rights and click a link
			//    which is not based on the currently logged in user's
			//    authorization levels (e.g. through a link pasted
			//    by a colleague, or whatever)
			//
			// We want to optimize for the path that is the most common
			// and useful for users, which is the last one.
			$error_message = "You have insufficient rights to ".
				"submit the command '$command' on table ".
				"'$table'. Please verify your user's rights ".
				"for '$table' commands. Aborting without any ".
				"commands applied.";
			op5log::instance('ninja')->log('warning', $error_message);
			$this->template->content->error = $error_message;
			$this->template->content->error_level = 'notice';
			return;
		}
		if(isset($commands[$command]['redirect']) && $commands[$command]['redirect']) {
			// All commands that have the 'redirect' flag set
			// wants us to skip the regular command form view
			// and provide its own. For example: locate host
			// on map (Nagvis)
			$result = $object->$command();
			return url::redirect($result);
		}

		$command_definition = $commands[$command];
		if (isset($command_definition['params'])) {
			foreach ($command_definition['params'] as $param => $data) {
				$override = config::get('nagdefault.' . $param);
				if (!($override === null)) {
					$command_definition['params'][$param]['default'] = $override;
				}
			}
		}

		$this->template->content->command_info = $command_definition;

	}

	/**
	 * Send a command for a specific object via AJAX
	 */
	public function ajax_command () {

		$this->template = new View('json');
		$this->template->success = false;
		$this->template->value = array();

		$command = $this->input->post('command', false);
		$query = $this->input->post('query', false);

		if ($command == false) {
			$this->template->value['message'] = 'Missing command!';
		} elseif ($query == false) {
			$this->template->value['message'] = 'Missing query!';
		}

		try {

			// validate table name
			$set = ObjectPool_Model::get_by_query($query);
			/* @var $set ObjectPool_Model */

			// validate command
			$obj_class = $set->class_obj();
			$commands = $obj_class::list_commands_static(true);

			if(!array_key_exists($command, $commands)) {
				$this->template->value['message'] = "Tried to submit command '$command' but that command does not exist for that kind of objects. Aborting without any commands applied";
				return;
			}

			// Unpack params
			$params = array();
			foreach($commands[$command]['params'] as $pname => $pdef) {
				$params[intval($pdef['id'])] = $this->input->post($pname, null);
			}

			// Depend on order of id instead of order of occurance
			ksort($params);
			$results = array();

			foreach($set as $object) {
				$result = call_user_func_array(array($object, $command), $params);
				if(isset($result['status']) && !$result['status']) {
					$output = "";
					if(isset($result['output'])) {
						$output = " Output: ".$result['output'];
					}
					op5log::instance('ninja')->log('warning', "Failed to submit command '$command' on (".$object->get_table().") object '".$object->get_key()."'".$output);
				}

				$results[] = array(
					'object' => $object->get_key(),
					'result' => $result
				);
			}

			$this->template->value['results'] = $results;
			$this->template->success = true;

		} catch(ORMException $e) {
			$this->template->value['message'] = 'Failed to submit command';
		}
	}

	/**
	 * Send a command for a specific object
	 */
	public function obj() {
		// TODO Don't use ORMException in this code...

		$template = $this->template->content = $this->add_view('cmd/exec');

		$command = $this->input->post('command', false);
		$query = $this->input->post('query', false);

		try {
			// validate input parameters presence
			if($command == false) {
				throw new ORMException('Missing command');
			}
			if($query == false) {
				throw new ORMException('Missing query');
			}

			// validate table name
			$set = ObjectPool_Model::get_by_query($query);
			/* @var $set ObjectPool_Model */

			// validate command
			$obj_class = $set->class_obj();
			$commands = $obj_class::list_commands_static(true);

			if(!array_key_exists($command, $commands))
				throw new ORMException("Tried to submit command '$command' but that command does not exist for '" . $set->get_table() . "'. Aborting without any commands applied");

			// Unpack params
			$params = array();
			foreach($commands[$command]['params'] as $pname => $pdef) {
				$params[intval($pdef['id'])] = $this->input->post($pname, null);
			}

			// Depend on order of id instead of order of occurance
			ksort($params);

			$results = array();

			foreach($set as $object) {
				// Don't set $this->template->content directly, since command might throw exceptions
				$command_template = $this->add_view($commands[$command]['view']);
				$result = call_user_func_array(array($object, $command), $params);
				if(isset($result['status']) && !$result['status']) {
					$output = "";
					if(isset($result['output'])) {
						$output = " Output: ".$result['output'];
					}
					op5log::instance('ninja')->log('warning', "Failed to submit command '$command' on (".$object->get_table().") object '".$object->get_key()."'".$output);
				}
				$command_template->result = $result;
				$command_template->object = $object;
				$results[] = $command_template;
			}

			$template->results = $results;

		} catch(ORMException $e) {
			request::send_header(400);
			$error_message = $e->getMessage();
			op5log::instance('ninja')->log('warning', $error_message);
			if(request::is_ajax()) {
				$this->template = new View('json');
				$this->template->success = false;
				$this->template->value = array('error' => $error_message);
				return;
			}
			$template->result = false;
			$template->error = $error_message;
		}
	}
}
