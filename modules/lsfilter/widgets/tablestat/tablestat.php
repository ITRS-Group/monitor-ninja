<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * ORM tablestat widget
 *
 * @author     op5 AB
 */
class tablestat_Widget extends widget_Base {
	/**
	 * Set if this widget is duplicatable
	 */
	protected $duplicatable = true;

	/**
	 * An array containing the settings for this widget; which filters to use
	 */
	protected $settings = array(
			'services' => array(
					'columns' => array(
							array(
									'name' => '%d Critical',
									'filter' => '[services] state = 2 and has_been_checked = 1',
									'icon' => 'shield-critical',
									'na_icon' => 'shield-not-critical'
							),
							array(
									'name' => '%d Warning',
									'filter' => '[services] state = 1 and has_been_checked = 1',
									'icon' => 'shield-warning',
									'na_icon' => 'shield-not-warning'
							),
							array(
									'name' => '%d Unknown',
									'filter' => '[services] state = 3 and has_been_checked = 1',
									'icon' => 'shield-unknown',
									'na_icon' => 'shield-not-unknown'
							),
							array(
									'name' => '%d OK',
									'filter' => '[services] state = 0 and has_been_checked = 1',
									'icon' => 'shield-ok',
									'na_icon' => 'shield-not-ok'
							),
							array(
									'name' => '%d Pending',
									'filter' => '[services] has_been_checked = 0',
									'icon' => 'shield-pending',
									'na_icon' => 'shield-not-pending'
							)
					),
					'rows' => array(
							array(
									'name' => '%d OK',
									'filter' => '[services] state = 0 and has_been_checked = 1',
									'icon' => 'shield-ok'
							),
							array(
									'name' => '%d Pending',
									'filter' => '[services] has_been_checked = 0',
									'icon' => 'shield-pending'
							),
							array(
									'name' => '%d Unhandled Problems',
									'filter' => '[services] state != 0 and (scheduled_downtime_depth = 0 and acknowledged = 0) and (host.scheduled_downtime_depth = 0 and host.acknowledged = 0) and (host.has_been_checked = 1 and host.state = 0)'
							),
							array(
									'name' => '%d on Problem Hosts',
									'filter' => '[services] host.has_been_checked = 1 and host.state != 0'
							),
							array(
									'name' => '%d in Scheduled Downtime',
									'filter' => '[services] scheduled_downtime_depth > 0',
									'icon' => 'scheduled-downtime'
							),
							array(
									'name' => '%d Acknowledged',
									'filter' => '[services] acknowledged = 1',
									'icon' => 'acknowledged'
							),
					)
			),
			'hosts' => array(
					'columns' => array(
							array(
									'name' => '%d Down',
									'filter' => '[hosts] state = 1 and has_been_checked = 1',
									'icon' => 'shield-down',
									'na_icon' => 'shield-not-down'
							),
							array(
									'name' => '%d Unreachable',
									'filter' => '[hosts] state = 2 and has_been_checked = 1',
									'icon' => 'shield-unreachable',
									'na_icon' => 'shield-not-unreachable'
							),
							array(
									'name' => '%d Up',
									'filter' => '[hosts] state = 0 and has_been_checked = 1',
									'icon' => 'shield-up',
									'na_icon' => 'shield-not-up'
							),
							array(
									'name' => '%d Pending',
									'filter' => '[hosts] has_been_checked = 0',
									'icon' => 'shield-pending',
									'na_icon' => 'shield-not-pending'
							)
					),
					'rows' => array(
							array(
									'name' => '%d Up',
									'filter' => '[hosts] state = 0 and has_been_checked = 1',
							),
							array(
									'name' => '%d Pending',
									'filter' => '[hosts] has_been_checked = 0',
									'icon' => 'shield-pending'
							),
							array(
									'name' => '%d Unhandled Problems',
									'filter' => '[hosts] state != 0 and scheduled_downtime_depth = 0 and acknowledged = 0'
							),
							array(
									'name' => '%d in Scheduled Downtime',
									'filter' => '[hosts] scheduled_downtime_depth > 0',
									'icon' => 'scheduled-downtime'
							),
							array(
									'name' => '%d Acknowledged',
									'filter' => '[hosts] acknowledged = 1',
									'icon' => 'acknowledged'
							),
					)
			),
			'downtimes' => array(
					'columns' => array(
							array(
									'name' => '%d Hosts',
									'filter' => '[downtimes] is_service = 0',
									'icon' => 'scheduled-downtime',
									'na_icon' => 'scheduled-downtime'
							),
							array(
									'name' => '%d Services',
									'filter' => '[downtimes] is_service = 1',
									'icon' => 'scheduled-downtime',
									'na_icon' => 'scheduled-downtime'
							)
					),
					'rows' => array(
							array(
									'name' => '%d Down',
									'filter' => '[downtimes] is_service = 0 and host.state = 1 and host.has_been_checked = 1',
//									'icon' => 'shield-down',
							),
							array(
									'name' => '%d Up',
									'filter' => '[downtimes] is_service = 0 and host.state = 0 and host.has_been_checked = 1',
//									'icon' => 'shield-up',
							),
							array(
									'name' => '%d Unreachable',
									'filter' => '[downtimes] is_service = 0 and host.state = 2 and host.has_been_checked = 1',
//									'icon' => 'shield-unreachable',
							),
							array(
									'name' => '%d Pending',
									'filter' => '[downtimes] is_service = 0 and host.has_been_checked = 0',
//									'icon' => 'shield-pending',
							),

							array(
									'name' => '%d Critical',
									'filter' => '[downtimes] is_service = 1 and service.state = 2 and service.has_been_checked = 1',
//									'icon' => 'shield-critical',
							),
							array(
									'name' => '%d Warning',
									'filter' => '[downtimes] is_service = 1 and service.state = 1 and service.has_been_checked = 1',
//									'icon' => 'shield-warning',
							),
							array(
									'name' => '%d Ok',
									'filter' => '[downtimes] is_service = 1 and service.state = 0 and service.has_been_checked = 1',
//									'icon' => 'shield-ok',
							),
							array(
									'name' => '%d Unknown',
									'filter' => '[downtimes] is_service = 1 and service.state = 3 and service.has_been_checked = 1',
//									'icon' => 'shield-unknown',
							),
							array(
									'name' => '%d Pending',
									'filter' => '[downtimes] is_service = 1 and service.has_been_checked = 0',
//									'icon' => 'shield-pending',
							),
					)
			),
			'comments' => array(
					'columns' => array(
							array(
									'name' => '%d Hosts',
									'filter' => '[comments] is_service = 0',
									'icon' => 'add-comment',
									'na_icon' => 'add-comment'
							),
							array(
									'name' => '%d Services',
									'filter' => '[comments] is_service = 1',
									'icon' => 'add-comment',
									'na_icon' => 'add-comment'
							)
					),
					'rows' => array(
							array(
									'name' => '%d User comments',
									'filter' => '[comments] entry_type=1',
									'icon' => 'add-comment',
							),
							array(
									'name' => '%d Downtimes',
									'filter' => '[comments] entry_type=2',
									'icon' => 'scheduled-downtime',
							),
							array(
									'name' => '%d Flapping',
									'filter' => '[comments] entry_type=3',
									'icon' => 'flapping',
							),
							array(
									'name' => '%d Acknowledgements',
									'filter' => '[comments] entry_type=4',
									'icon' => 'acknowledged',
							),
					)
			)
	);

	/**
	 * A list of the columns to show in the widget
	 */
	protected $columns = array(
			array(
					'name' => '%d objects',
					'icon' => 'shield-info',
					'na_icon' => 'shield-not-info'
			)
	);
	/**
	 * A list of the rows to show in the widget
	 */
	protected $rows = array(
			array(
					'name' => '%d objects'
			)
	);

	/**
	 * The fetched table
	 */
	protected $table = false;

	/**
	 * The set representing whichs objects this widget should work within.
	 */
	protected $universe = false;
	/**
	 * The set representing all objects of the type this widget works with.
	 */
	protected $all = false;

	/**
	 * Constructor. This should be overloaded, to upadte the settings-attribute
	 * when making a custom widget of this type
	 */
	public function __construct($widget_model) {
		parent::__construct($widget_model);
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
		array_unshift($options, new option('tablestat','filter',_('Filter'),'textarea',array(),'[services] all'));
		return $options;
	}

	/**
	 * Fetch the data and show the widget
	 */
	public function index() {
		try {
			$this->args = $this->get_arguments();

			$this->universe = ObjectPool_Model::get_by_query($this->args['filter']);
			$this->all = ObjectPool_Model::pool($this->universe->get_table())->all();

			if( isset($this->settings[$this->universe->get_table()]) ) {
				$settings = $this->settings[$this->universe->get_table()];
				$this->columns = $settings['columns'];
				$this->rows = $settings['rows'];
			}

			$stats = array();
			foreach( $this->columns as $ci => $col ) {
				if( isset($col['filter']) ) {
					$col_filter = ObjectPool_Model::get_by_query( $col['filter'] );
				} else {
					$col_filter = $this->all;
				}
				$stats[$ci.';_HEAD_'] = $col_filter;
				foreach( $this->rows as $ri => $row ) {
					if( isset($col['filter']) ) {
						$row_filter = ObjectPool_Model::get_by_query( $row['filter'] );
					} else {
						$row_filter = $this->all;
					}
					$cell_filter = $row_filter->intersect( $col_filter );
					$stats[$ci.';'.$ri] = $cell_filter;
				}
			}
			$result = $this->universe->stats($stats);

			$this->table = array();
			foreach( $this->columns as $ci => $col ) {
				$cells = array();
				$col_count = $result[$ci.';_HEAD_'];
				$col_filter = $this->universe->intersect($stats[$ci.';_HEAD_'])->get_query();
				foreach( $this->rows as $ri => $row ) {
					$count = $result[$ci.';'.$ri];
					$filter = $this->universe->intersect($stats[$ci.';'.$ri])->get_query();
					$cells[$ri] = array(
							'text' => sprintf(_($row['name']), $count),
							'count' => $count,
							'filter' => $filter,
							'icon' => isset($row['icon'])?$row['icon']:$col['icon'],
							'hide' => $count == 0 || (isset($row['hide']) && in_array($col['name'],$row['hide']))
					);
				}
				$this->table[$ci] = array(
						'name' => sprintf(_($col['name']),$col_count),
						'cells' => $cells,
						'icon' => $col['icon'],
						'na_icon' => $col['na_icon'],
						'filter' => $col_filter,
				);
			}

			require('view.php');
		} catch( ORMException $e ) {
			require('view_error.php');
		} catch( op5LivestatusException $e ) {
			require('view_error.php');
		}
	}
}