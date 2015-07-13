<?php

/**
 * Execute commands on ORM objects
 */
class Exec_cmd_Controller extends Ninja_Controller {

	/**
	 * Show a form for submitting a command on a single object
	 */
	public function index() {
		// todo mayi
		$this->template->content = $this->add_view('command/cmd_exec');
		$this->template->disable_refresh = true;
		$this->template->content->error = false;
		$command = $this->input->get('command');
		$table = $this->input->get('table');
		$object_key = $this->input->get('object');

		try {
			$pool = ObjectPool_Model::pool($table);
		} catch(ORMException $e) {
			$this->template->content->error = $e->getMessage();
			return;
		}

		$object = $pool->fetch_by_key($object_key);
		if($object === false) {
			$this->template->content->error = "Could not find object '$object_key'";
			return;
		}
		$this->template->content->object = $object;
		$this->template->content->table = $table;
		$this->template->content->command = $command;

		// TODO differentiate hg service and h service
		$commands = $object->list_commands();
		if(!array_key_exists($command, $commands)) {
			$error_message = "Tried to submit command '$command' on table '$table' but that command does not exist for that kind of objects. Aborting without any commands applied";
			op5log::instance('ninja')->log('warning', $error_message);
			$this->template->content->error = "Could not find object '$error_message'";
			return;
		}
		$this->template->content->command_info = $commands[$command];
	}

	/**
	 * Send a command for a specific object
	 */
	public function obj() {
		// todo mayi
		// TODO maybe you don't wanna reuse the old view
		$this->template->content = $this->add_view('command/commit');
		$command = $this->input->post('c', false);
		$table = $this->input->post('t', false);
		$key = $this->input->post('o', false);

		// validate input parameters presence
		$errors = array();
		if($command == false) {
			$errors[] = 'Missing command (the c parameter)';
		}
		if($table == false) {
			$errors[] = 'Missing table (the t parameter)';
		}
		if($key == false) {
			$errors[] = 'Missing object name (the o parameter)';
		}
		if($errors) {
			if(request::is_ajax()) {
				return json::fail(array('error' => $errors));
			}
			$this->template->content->result = false;
			$this->template->content->error = implode("<br>", $errors);
		}

		// validate table name
		try {
			$pool = ObjectPool_Model::pool($table);
		} catch(ORMException $e) {
			$error_message = $e->getMessage();
			if(request::is_ajax()) {
				return json::fail(array('error' => $error_message));
			}
			$this->template->content->result = false;
			$this->template->content->error = $error_message;
			return;
		}

		// validate object by primary key
		$object = $pool->fetch_by_key($key);
		if($object === false) {
			$error_message =  "Could not find object '$key'";
			if(request::is_ajax()) {
				return json::fail(array('error' => $error_message));
			}
			$this->template->content->result = false;
			$this->template->content->error = $error_message;
			return;
		}

		// TODO differentiate hg service and h service
		// validate command
		$commands = $object->list_commands();
		if(!array_key_exists($command, $commands)) {
			$error_message = "Tried to submit command '$command' on table '$table' but that command does not exist for that kind of objects. Aborting without any commands applied";
			op5log::instance('ninja')->log('warning', $error_message);
			if(request::is_ajax()) {
				return json::fail(array('error' => $error_message));
			}
			$this->template->content->result = false;
			$this->template->content->error = $error_message;
			return;
		}

		// validate mayi
		if(isset($commands[$command]['mayi_resource']) && $commands[$command]['mayi_resource']) {
			// the command specified its own mayi_resource
			$this->_verify_access($commands[$command]['mayi_resource']);
		} else {
			// fallback to using the command name, avoids
			// lengthy command definitions in the objects
			// themselves
			$this->_verify_access("monitor.monitoring.$table.commands.$command:create");
		}


		$params = array();
		foreach($commands[$command]['parameters'] as $parameter => $type) {
			$params[] = $this->input->post($parameter, null);
		}
		// every command takes a reference to an error as its
		// last argument
		$params[] = &$error_string;

		$result = call_user_func_array(array($object, $command), $params);
		if($result) {
			if(request::is_ajax()) {
				return json::ok(array('message' =>  'Your command was successfully submitted'));
			}
			$this->template->content->result = true;
			return;
		}

		if(request::is_ajax()) {
			return json::fail(array('error' => $error_string));
		}
		$this->template->content->result = false;
		$this->template->content->error = $error_string;
	}
}
