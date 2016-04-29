<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Getting started widget
 *
 * @author     op5 AB
 */
class gettingstarted_Widget extends widget_Base {
	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'Getting started with op5 Monitor',
			'css' => array(
				'style.css'
			)
		));
	}

	/**
	 * Load the options for this widget.
	 *
	 * override default. We don't need any auto refresh
	 */
	public function options() {
		return array();
	}

	/**
	 * Just show the view
	 */
	public function index() {
		require('view.php');
	}
}
