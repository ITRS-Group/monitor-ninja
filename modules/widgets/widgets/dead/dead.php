<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Placeholder widget implementation for widgets that fail to load, or otherwise
 * instatiate
 */
class Dead_Widget extends widget_Base {
	/**
	 * Builds a new Dead_Widget
	 **/
	public function __construct($model, Exception $exc = null) {
		$this->exc = $exc;
		parent::__construct($model);
	}

	/**
	 * @return string
	 */
	public function get_exception_message() {
		return $this->exc->getMessage();
	}

	public function get_metadata() {
		return array(
			'instanceable' => false
		);
	}

	public function index() {
		echo '<div class="alert error"><h3>This widget failed to load</h3>';
		echo '<p>This may be a temporary problem. If the problem persists, please contact your administrator.</p>';
		if($this->exc !== null && $this->exc->getMessage()) {
			echo '<p>Additional troubleshooting information: <strong>' . get_class($this->exc) . '</strong><em>(' . $this->exc->getMessage() . ')</em></p>';
			if(!IN_PRODUCTION) {
				echo "<p>Also displaying full stack trace because <strong>IN_PRODUCTION</strong> is off:</p>";
				echo "<pre>";
				/*
				 *  getTraceAsString() displays much less
				 * information, do not use it
				 * Also, don't use var_export, since $this->exc contains a
				 * reference to $this, due to being part of the trace, thus
				 * it will lead to an infinite recursion
				 */
				var_dump($this->exc->getTrace());
				echo "</pre>";
			}
		}
		echo '</div>';
	}
}
