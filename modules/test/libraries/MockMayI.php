<?php
/**
 * A mock implementation of MayI, which allows everything with no feedback.
 */
class MockMayI
{
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
	 * Dummy `run` implementation, that always returns true
	 *
	 * @param $action Unused
	 * @param $override Unused
	 * @param &$messages Always empty
	 * @param &$metrics Always empty
	 * @return bool
	 */
	public function run($action, array $override = array(), &$messages = false, &$metrics = false) {
		$messages = array();
		$metrics = array();
		return true;
	}
}

