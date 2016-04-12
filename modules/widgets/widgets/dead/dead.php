<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Placeholder widget implementation for widgets that fail to load, or otherwise
 * instatiate
 */
class Dead_Widget extends widget_Base {
	/**
	 * Builds a new Dead_Widget
	 **/
	public function __construct($model, Exception $exc) {
		$this->exc = $exc;
		parent::__construct($model);
	}

	public function get_metadata() {
		return array(
			'instanceable' => false
		);
	}

	public function index() {
		echo '<div class="alert error"><h3>This widget failed to load</h3>';
		echo '<p>This may be a temporary problem. If the problem persists, please contact your administrator.</p>';
		if($this->exc->getMessage()) {
			echo '<p>Additional troubleshooting information: <strong>' . get_class($this->exc) . '</strong><em>(' . $this->exc->getMessage() . ')</em></p>';
		}
		echo '</div>';
	}
}
