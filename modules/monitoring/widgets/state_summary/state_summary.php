<?php

/**
 * Intent: visualize a filter's summarized state
 * Input: any saved filter, whose table is configured in $states_per_table
 * Output: decorated stats for the saved filter
 */
class State_summary_Widget extends widget_Base {

	private $states_per_table = array();

	private $default_filter = -200;
	private $hardcoded_filters = array(
		-200 => array(
			'name' => 'All hosts',
			'filter' => '[hosts] all'
		),
		-100 => array(
			'name' => 'All services',
			'filter' => '[services] all'
		)
	);

	/**
	 * @param $widget_model Ninja_Widget_Model
	 */
	public function __construct(Ninja_Widget_Model $widget_model) {
		parent::__construct($widget_model);
		$this->states_per_table = array(
			'hosts' => array(
				'label' => 'Hosts',
				'states' => array(
					array(
						'subset_query' => '[hosts] has_been_checked = 1 and state = 0',
						'label' => 'Up',
						'css_class' => function($count) {
							if($count === 0) {
								return 'gray';
							}
							return 'green';
						},
					),
					array(
						'subset_query' => '[hosts] has_been_checked = 1 and state = 1',
						'label' => 'Down',
						'css_class' => function($count) {
							if($count === 0) {
								return 'gray';
							}
							return 'red';
						},
					),
					array(
						'subset_query' => '[hosts] has_been_checked = 1 and state = 2',
						'label' => 'Unreachable',
						'css_class' => function($count) {
							if($count === 0) {
								return 'gray';
							}
							return 'yellow';
						},
					),
					array(
						'subset_query' => '[hosts] has_been_checked = 0',
						'label' => 'Pending',
						'css_class' => function($count) {
							if($count === 0) {
								return 'no-display';
							}
							return 'gray';
						},
					),
				)
			),
			'services' => array(
				'label' => 'Services',
				'states' => array(
					array(
						'subset_query' => '[services] host.has_been_checked = 1 and has_been_checked = 1 and state = 0',
						'label' => 'OK',
						'css_class' => function($count) {
							if($count === 0) {
								return 'gray';
							}
							return 'green';
						},
					),
					array(
						'subset_query' => '[services] host.has_been_checked = 1 and has_been_checked = 1 and state = 1',
						'label' => 'Warning',
						'css_class' => function($count) {
							if($count === 0) {
								return 'gray';
							}
							return 'yellow';
						},
					),
					array(
						'subset_query' => '[services] host.has_been_checked = 1 and has_been_checked = 1 and state = 2',
						'label' => 'Critical',
						'css_class' => function($count) {
							if($count === 0) {
								return 'gray';
							}
							return 'red';
						},
					),
					array(
						'subset_query' => '[services] host.has_been_checked = 1 and has_been_checked = 1 and state = 3',
						'label' => 'Unknown',
						'css_class' => function($count) {
							if($count === 0) {
								return 'no-display';
							}
							return 'orange';
						},
					),
					array(
						'subset_query' => '[services] has_been_checked = 0',
						'label' => 'Pending',
						'css_class' => function($count) {
							if($count === 0) {
								return 'no-display';
							}
							return 'gray';
						},
					),
				)
			)
		);
	}

	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => "State Summary",
			'instanceable' => true,
			'css' => array('state_summary.css'),
		));
	}

	public function options() {

		$options = parent::options();
		$filters_that_has_gui_defined = SavedFilterPool_Model::none();

		foreach($this->states_per_table as $table => $_) {
			$filters_that_has_gui_defined = $filters_that_has_gui_defined
				->union(
					SavedFilterPool_Model::all()
						->reduce_by('filter_table', $table, '=')
				);
		}

		$all_filters = array();

		foreach ($this->hardcoded_filters as $id => $filter) {
			$all_filters[$id] = $filter['name'];
		}

		$saved_filters = array();
		foreach($filters_that_has_gui_defined->it(false,
			array('filter_table ASC')) as $filter) {
			$saved_filters[$filter->get_id()] = $filter->get_filter_name();
		}

		$all_filters['Saved filters ('.count($saved_filters).')'] = $saved_filters;

		$options[] = new option(
			__CLASS__,
			'filter_id', _('Filter'),
			'dropdown', array(
				'options' => $all_filters
			),
			$this->default_filter
		);
		return $options;
	}

	protected function get_suggested_title () {

		$args = $this->get_arguments();
		$filter_id = $args['filter_id'];
		$title = 'State Summary of "%s"';

		if (isset($this->hardcoded_filters[$filter_id])) {
			return sprintf($title, $this->hardcoded_filters[$filter_id]['name']);
		} else {
			$filter = SavedFilterPool_Model::all()
				->reduce_by('id', $filter_id, '=')
				->one();
			if(!$filter) {
				throw new WidgetSettingException(
					sprintf(
						"Filter with id '%d' does not exist",
						$filter_id
					)
				);
			}
			return sprintf($title, $filter->get_filter_name());
		}

	}

	/**
	 * @param $filter_id int
	 * @return array
	 */
	public function get_filtered_data($filter_id) {
		if (isset($this->hardcoded_filters[$filter_id])) {
			$object_set = ObjectPool_Model::get_by_query(
				$this->hardcoded_filters[$filter_id]['filter']
			);
		} else {
			$filter = SavedFilterPool_Model::all()
				->reduce_by('id', $filter_id, '=')
				->one();
			if(!$filter) {
				throw new WidgetSettingException(
					sprintf(
						"Filter with id '%d' does not exist, please select another filter",
						$filter_id
					)
				);
			}
			$object_set = ObjectPool_Model::get_by_query(
				$filter->get_filter()
			);
		}

		//doesn't work too well when changing options without reloading
		//the whole page that the widget is rendered on:
		$state_definitions = $this->states_per_table[$object_set->get_table()];
		$intersections = array();
		$queries = array();

		foreach($state_definitions['states'] as $state) {
			$subset = ObjectPool_Model::get_by_query($state['subset_query']);
			$queries[] = $object_set->intersect($subset)->get_query();
			$intersections[] = $subset;
		}

		$stats = $object_set->stats($intersections);
		return array(
			'queries' => $queries,
			'state_definitions' => $state_definitions,
			'stats' => $stats,
		);
	}

	public function index() {
		$args = $this->get_arguments();
		$data = $this->get_filtered_data($args['filter_id']);
		$stats = $data['stats'];
		$state_definitions = $data['state_definitions'];
		$queries = $data['queries'];
		unset($data);
		require __DIR__.'/view.php';
	}
}
