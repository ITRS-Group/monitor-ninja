<?php

/**
 * Execute commands on ORM objects
 */
class Cmd_Controller extends Ninja_Controller {

	/**
	 * Show a form for submitting a command on a single object
	 * Sets template to an HTML fragment
	 */
	public function index() {

		// todo mayi

		/* Accept both get and post, get has precedence */
		$command = $this->input->get('command', $this->input->post('command', false));
		$table = $this->input->get('table', $this->input->post('table', false));
		$object_keys = $this->input->get('object', $this->input->post('object', false));

		/* Object keys can be both array or single, if single, convert to array */
		if(!is_array($object_keys)) {
			$object_keys = array($object_keys);
		}

		try {
			$pool = ObjectPool_Model::pool($table);
			/* @var $pool ObjectPool_Model */
		} catch(ORMException $e) {
			return $this->template = new View('error', array(
				'title' => "Error retrieving object to run command on",
				'message' => $e->getMessage()
			));
		}

		$set = $pool->none();
		/* @var $set ObjectSet_Model */
		foreach($object_keys as $key) {
			$set = $set->union($pool->set_by_key($key));
		}

		$count = count($set);
		$commands = array();

		if ($count === 0) {
			return $this->template = new View('error', array(
				'title' => "Could not find $table",
				'message' => "The $table you were trying to execute '$command' on were not" .
				" found Attempted to find $table with the names: " . html::get_delimited_string($object_keys)
			));
		}

		try {
			$definition = cmd::get_definition($command, $set);
		} catch (UnknowCommandException $e) {
			op5log::instance('ninja')->log('warning', $e->getMessage());
			return $this->template = new View('error', array(
				'title' => "Could not execute command",
				'message' => $e->getMessage(),
				'code' => 403
			));
		}

		/**
		 * Redirect commands do not supply a form, simply go to the URL given,
		 * however, we are in AJAX land now so return the redirect URL to the
		 * client as JSON.
		 */
		if (isset($definition['redirect']) && $definition['redirect']) {
			$result = $object->$command();
			return url::redirect($result);
		}

		if (isset($definition['params'])) {
			foreach ($definition['params'] as $param => $data) {
				$override = config::get('nagdefault.' . $param);
				if (!($override === null)) {
					$definition['params'][$param]['default'] = $override;
				}
			}
		}

		$this->template = cmd::get_form(
			$command, $definition, $set
		)->get_view();

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

			try {
				$definition = cmd::get_definition($command, $set);
			} catch (UnknowCommandException $e) {
				op5log::instance('ninja')->log('warning', $e->getMessage());
				$this->template->value['message'] = $e->getMessage();
				return;
			}

			// Unpack params
			$params = array();
			foreach($definition['params'] as $pname => $pdef) {
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

		$this->template = new View('cmd/exec');

		$command = $this->input->post('command', false);
		$query = $this->input->post('query', false);

		try {

			if ($command == false) {
				return $this->template = new View('error', array(
					'title' => "Unknown command",
					'message' => 'Missing command parameter for command execution'
				));
			} elseif ($query == false) {
				return $this->template = new View('error', array(
					'title' => "Unknown objects",
					'message' => 'Missing query parameter for command execution'
				));
			}

			$set = ObjectPool_Model::get_by_query($query);
			$table = $set->get_table();

			$this->template->count = count($set);
			$this->template->success = 0;
			$this->template->command = $command;
			$this->template->table = $table;

			try {
				$definition = cmd::get_definition($command, $set);
			} catch (UnknowCommandException $e) {
				op5log::instance('ninja')->log('warning', $e->getMessage());
				return $this->template = new View('error', array(
					'title' => "Could not execute command",
					'message' => $e->getMessage(),
					'code' => 403
				));
			}

			// Unpack params
			$params = array();
			foreach($definition['params'] as $pname => $pdef) {
				$params[intval($pdef['id'])] = $this->input->post($pname, null);
			}

			// Depend on order of id instead of order of occurance
			ksort($params);
			$results = array();

			foreach($set as $object) {

				$result = call_user_func_array(array($object, $command), $params);
				$output = "No output";

				if (isset($result['status']) && !$result['status']) {

					if(isset($result['output'])) {
						$output = $result['output'];
					}

					op5log::instance('ninja')->log(
						'warning', "Failed to submit command '$command' on ".
						"($table) object '" . $object->get_key() . "'" . $output
					);

				} else {
					$this->template->success++;
				}

				$results[] = array(
					'object' => $object->get_key(),
					'result' => $result,
					'output' => $output
				);
			}

			$this->template->results = $results;

		} catch(ORMException $e) {
			op5log::instance('ninja')->log('warning', $e->getMessage());
			return $this->template = new View('error', array(
				'title' => "Could not execute command",
				'message' => $e->getMessage()
			));
		}
	}
}
