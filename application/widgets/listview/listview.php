<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Listview widget
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Listview_Widget extends widget_Base {
	protected $movable=false;
	protected $removable=false;
	protected $closeconfirm=false;
	protected $editable=false;

	private $query=false;
	
	public function __construct($model) {
		parent::__construct($model);
		
	}
	
	public function set_fixed() {
	}
	
	public function index() {
		try {
			$set = ObjectPool_Model::get_by_query($this->model->setting['query']);
		} catch(LSFilterException $e) {
			array(
				'status' => 'error',
				'data' => $e->getMessage().' at "'.substr($e->get_query(), $e->get_position()).'"',
				'query' => $e->get_query(),
				'position' => $e->get_position()
				);
		}
		require($this->view_path('view'));
	}
}