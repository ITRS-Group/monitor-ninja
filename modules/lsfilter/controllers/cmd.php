<?php

/**
 * Execute commands on ORM objects
 */
class Cmd_Controller extends Ninja_Controller {

	/**
	 * Show a form for submitting a command on a single object
	 */
	public function index() {
		// todo mayi
		$this->template->content = $this->add_view('cmd/index');
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
	public function obj($resp_type = 'html') {
		// TODO Don't use ORMException in this code...
		// TODO maybe you don't wanna reuse the old view

		$template = $this->template->content = $this->add_view('cmd/exec');

		$command = $this->input->post('command', false);
		$table = $this->input->post('table', false);
		$key = $this->input->post('object', false);

		try {
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

			if($errors)
				throw new ORMException(implode("<br>", $errors));

			// validate table name
			$pool = ObjectPool_Model::pool($table);

			// validate object by primary key
			$object = $pool->fetch_by_key($key);
			if($object === false)
				throw new ORMException("Could not find object '$key'", $table, false);
			/* @var $object Object_Model */

			// validate command
			$commands = $object->list_commands(true);
			if(!array_key_exists($command, $commands))
				throw new ORMException("Tried to submit command '$command' but that command does not exist for that kind of objects. Aborting without any commands applied", $table, false);


			$params = array();
			$cmdparams = $commands[$command]['param'];
			foreach($commands[$command]['param'] as $param_def) {
				list($param_type,$param_name) = $param_def;
				$params[] = $this->input->post($param_name, null);
			}

			// Don't set $this->template->content directly, since command might throw exceptions
			$command_template = $this->add_view($commands[$command]['view']);
			$command_template->result = call_user_func_array(array($object, $command), $params);
			$this->template->content = $command_template;

		} catch(ORMException $e) {
			$error_message = $e->getMessage();
			op5log::instance('ninja')->log('warning', $error_message);
			if(request::is_ajax()) {
				return json::fail(array('error' => $error_message));
			}
			$template->result = false;
			$template->error = $error_message;
			return;
		}

	}
}
