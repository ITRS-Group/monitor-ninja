<?php
/**
 * A mock implementation of MayI, which allows everything with no feedback.
 */
class MockMayI extends op5MayI
{

	/**
	 * Create and return a new MockMayI instance.
	 *
	 * The $config paramater can contain an element "denied_actions" which
	 * contains a list of namespaces which this instance will deny on invocation
	 * of run()
	 *
	 * @param $config array configuration for the new instance
	 * @return MockMayI the constructed instance
	 */
	public function __construct($config) {
		$this->denied_actions = array();
		if (array_key_exists('denied_actions', $config)) {
			$this->denied_actions = $config['denied_actions'];
		}
	}
	/**
	 * Dummy `be` implementation, that just returns this MockMayI instance
	 *
	 * @param $context Unused
	 * @param $actor Unused
	 * @return MockMayI
	 */
	public function be($context, op5MayI_Actor $actor) {
		return $this;
	}

	/**
	 * Dummy `act_upon` implementation, that just returns this MockMayI instance
	 *
	 * @param $constraints Unused
	 * @param $priority Unused
	 * @return MockMayI
	 */
	public function act_upon(op5MayI_Constraints $constraints, $priority = 0) {
		return $this;
	}

	/**
	 * A mocked `run` implementation, that returns true for all
	 * actions that are not listed in the "denied_actions" configuration
	 * passed to the constructor.
	 *
	 * @param $action Unused
	 * @param $override Unused
	 * @param &$messages Always empty
	 * @param &$metrics Always empty
	 * @return bool
	 */
	public function run($action, array $override = array(), &$messages = false, &$metrics = false) {

		$log = op5log::instance('test');
		$metrics = array();
		$messages = array();

		foreach ($this->denied_actions as $daction => $properties) {
			if ($this->is_subset($action, $daction)){
				$log->log('debug', __CLASS__ . " denying access to action $action");
				$messages[] = $properties["message"];
				return false;
			}
		}
		$log->log('debug', __CLASS__ . " allowing access to action $action");
		return true;
	}
}

