<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Listview widget
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
*/
class gridstat_Widget extends widget_Base {
	protected $duplicatable = true;

	protected $settings = false;

	public function __construct($widget_model) {
		parent::__construct($widget_model);
		$this->settings = array();
	}

	/**
	 * Disable everything configurable. This is useful when including the widget with generetated parameters from a controller.
	 */
	public function set_fixed() {
		$this->movable      = false;
		$this->removable    = false;
		$this->closeconfirm = false;
		$this->editable     = false;
		$this->duplicatable = false;
	}

	public function options() {
		$options = parent::options();
		return $options;
	}

	private function grab_filters( $arr ) {
		$filters = array();
		foreach( $arr as $key => $value ) {
			if( is_array( $value ) ) {
				$filters = array_merge($filters, $this->grab_filters($value));
			} else if( $key === 'filter' ) {
				$filters[] = $value;
			}
		}
		return $filters;
	}

	private function apply_filters( $arr, $results ) {
		$filters = array();
		foreach( $arr as $key => $value ) {
			if( is_array( $value ) ) {
				$res = $this->apply_filters($value, $results);

				if( $res !== false ) {
					if( $key === 'fields' ) {
						if( count($res) == 0 ) {
							return false;
						}
					}
					$filters[$key] = $res;
				}
			} else {
				$filters[$key] = $value;

				if( $key === 'filter' ) {
					$filters['count'] = $results[$value];
					if( $results[$value] == 0 ) {
						return false;
					}
				}
			}
		}
		return $filters;
	}

	public function index() {
		try {
			$filters = $this->grab_filters($this->settings);
			$results = array();
			foreach( $filters as $filter ) {
				$results[$filter] = count(ObjectPool_Model::get_by_query($filter));
			}
			$this->data = $this->apply_filters($this->settings, $results);

			$this->model->name = 'gridstat'; // Force it to be a gridstat model
			require($this->view_path('view'));
		} catch( ORMException $e ) {
			require($this->view_path('view_error'));
		} catch( op5LivestatusException $e ) {
			require($this->view_path('view_error'));
		} catch( Exception $e ) {
			print '<pre>';
			print_r($e);
			print '</pre>';
		}
	}
}