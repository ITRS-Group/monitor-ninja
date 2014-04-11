<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * ORM gridstat widget
 *
 * @author     op5 AB
 */
class gridstat_Widget extends widget_Base {
	/**
	 * Set if this widget is duplicatable
	 */
	protected $duplicatable = true;

	/**
	 * An array containing the settings for this widget; which filters to use
	 */
	protected $settings = false;

	/**
	 * Constructor. This should be overloaded, to upadte the settings-attribute
	 * when making a custom widget of this type
	 */
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

	/**
	 * Load the options for this widget.
	 */
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

	/**
	 * Fetch the data and show the widget
	 */
	public function index() {
		try {
			$filters = $this->grab_filters($this->settings);
			$results = array();
			foreach( $filters as $filter ) {
				$results[$filter] = count(ObjectPool_Model::get_by_query($filter));
			}
			$this->data = $this->apply_filters($this->settings, $results);
			if(empty($this->data)) {
				$this->data = array(
					array(
						'icon' => 'shield-not-disabled',
						'title' => _('N/A'),
						'fields' => array()
					)
				);
			}
			require('view.php');
		} catch( ORMException $e ) {
			require('view_error.php');
		} catch( op5LivestatusException $e ) {
			require('view_error.php');
		} catch( Exception $e ) {
			print '<pre>';
			print_r($e);
			print '</pre>';
		}
	}
}