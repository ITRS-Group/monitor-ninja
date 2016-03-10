<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network health widget
 *
 * @author op5 AB
*/
class Big_Number_Widget extends widget_Base {

	protected $duplicatable = true;

	protected $big_number_name = "Big Number";
	protected $big_number_description = "in DOWN state";
	protected $big_number_table = "hosts";

	protected $big_number_superset = "all";
	protected $big_number_subset = "state = 1";

	protected $threshold_type = "limit";
	protected $threshold_warning = '1';
	protected $threshold_critical = '2';

	public function __construct ($model) {

		parent::__construct($model);

		$this->big_number_name = $this->get_setting('big_number_name', $this->big_number_name);
		$this->big_number_description = $this->get_setting('big_number_description', $this->big_number_description);
		$this->big_number_table = $this->get_setting('big_number_table', $this->big_number_table);
		$this->big_number_superset = $this->get_setting('big_number_superset', $this->big_number_superset);
		$this->big_number_subset = $this->get_setting('big_number_subset', $this->big_number_subset);

		$this->threshold_type = $this->get_setting('threshold_type', $this->threshold_type);
		$this->threshold_warning = $this->get_setting('threshold_warning', $this->threshold_warning);
		$this->threshold_critical = $this->get_setting('threshold_critical', $this->threshold_critical);

		$this->model->friendly_name = $this->get_setting('big_number_name', $this->big_number_name);

	}

	/**
	 * Gets the setting if one has been set, otherwize return the default
	 *
	 * @param $setting string
	 * @param $default mixed
	 * @return mixed
	 */
	private function get_setting ($setting, $default = null) {
		return isset($this->model->setting[$setting])
			? $this->model->setting[$setting]
			: $default;
	}

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => $this->big_number_name,
			'instanceable' => true
		));
	}

	public function options() {

		$options = parent::options();

		$options[] = new option(
			$this->model->name, 'big_number_name', 'Name', 'input',
			array(
				'style' => 'width: 128px',
				'title' => sprintf(_('Default value: %s%%'), $this->big_number_name)
			),
			$this->big_number_name
		);

		$options[] = new option(
			$this->model->name, 'big_number_description', 'Description', 'textarea',
			array(
				'title' => sprintf(_('Default value: %s%%'), $this->big_number_description)
			),
			$this->big_number_description
		);

		$options[] = new option(
			$this->model->name, 'big_number_table', 'Table', 'dropdown',
			array(
				'title' => _('Object type'),
				'options' => array(
					'hosts' => 'Hosts',
					'services' => 'Services',
					'hostgroups' => 'Hostgroups',
					'servicegroups' => 'Servicegroups'
				)
			),
			$this->big_number_table
		);

		$options[] = new option(
			$this->model->name, 'big_number_superset', 'Superset', 'textarea',
			array(
				'title' => _('Filter')
			),
			$this->big_number_superset
		);

		$options[] = new option(
			$this->model->name, 'big_number_subset', 'Subset', 'textarea',
			array(
				'title' => _('Filter')
			),
			$this->big_number_subset
		);

		$options[] = new option(
			$this->model->name, 'threshold_type', 'Type', 'dropdown',
			array(
				'options' => array(
					'percentage' => "Percentage",
					'percentage_inverse' => "Percentage (inverse)",
					'limit' => "Limit",
					'range' => "Range"
				),
				'title' => sprintf(_('Default value: %s%%'), $this->threshold_type)
			),
			$this->threshold_type
		);

		$options[] = new option(
			$this->model->name, 'threshold_warning', 'Warning Level', 'input',
			array(
				'style' => 'width: 32px',
				'title' => sprintf(_('Default value: %s%%'), $this->threshold_warning)
			),
			$this->threshold_warning
		);

		$options[] = new option(
			$this->model->name, 'threshold_critical', 'Critical Level', 'input',
			array(
				'style' => 'width: 32px',
				'title' => sprintf(_('Default value: %s%%'), $this->threshold_critical)
			),
			$this->threshold_critical
		);

		return $options;

	}

	private function parse_range ($range) {
		if (preg_match("/^(\d+)\-(\d+)$/", $range, $matches)) {
			return array(intval($matches[1], $matches[2]));
		} elseif (preg_match("/^\-(\d+)$/", $range, $matches)) {
			return array(0, intval($matches[1]));
		} elseif (preg_match("/^(\d+)\-$/", $range, $matches)) {
			return array(intval($matches[1]), PHP_INT_MAX);
		}
	}

	private function get_percentage_inverse (ObjectSet_Model $superset, ObjectSet_Model $subset) {

			$superset_count = count($superset);
			$subset_count = count($subset);

			$number = ($subset_count / $superset_count) * 100;

			if ($number <= $this->threshold_critical) {
				$state = 'critical';
			} elseif ($number <= $this->threshold_warning) {
				$state = 'warning';
			} else $state = 'ok';

			$number = number_format($number, 2);
			return array($number, $state);

	}

	private function get_percentage (ObjectSet_Model $superset, ObjectSet_Model $subset) {

			$superset_count = count($superset);
			$subset_count = count($subset);

			$number = ($subset_count / $superset_count) * 100;

			if ($number >= $this->threshold_critical) {
				$state = 'critical';
			} elseif ($number >= $this->threshold_warning) {
				$state = 'warning';
			} else $state = 'ok';

			$number = number_format($number, 2);
			return array($number, $state);

	}

	private function get_limit (ObjectSet_Model $superset, ObjectSet_Model $subset) {

			$count = count($subset);
			if ($count >= $this->threshold_critical)
				$state = "critical";
			elseif ($count >= $this->threshold_warning)
				$state = "warning";
			else $state = "ok";

			return array($count, $state);

	}

	private function get_range (ObjectSet_Model $superset, ObjectSet_Model $subset) {

			list($warning_min, $warning_max) = $this->parse_range($this->threshold_warning);
			list($critical_min, $critical_max) = $this->parse_range($this->threshold_critical);

			$count = count($subset);
			if ($count >= $critical_min && $count <= $critical_max)
				$state = "critical";
			elseif ($count >= $warning_min && $count <= $warning_max)
				$state = "warning";
			else $state = "ok";

			return array($count, $state);

	}

	public function index() {

		$type = $this->big_number_table;
		$superset = ObjectPool_Model::get_by_query("[$type] " . $this->big_number_superset);
		$subset = $superset->intersect(
			ObjectPool_Model::get_by_query("[$type] " . $this->big_number_subset)
		);

		$number = "N/A";
		$state = "pending";
		$uom = "";

		$description = $this->big_number_description;
		$query = $subset->get_query();

		if ($this->threshold_type === 'percentage_inverse') {
			$uom = "%";
			list($number, $state) = $this->get_percentage_inverse($superset, $subset);
		} if ($this->threshold_type === 'percentage') {
			$uom = "%";
			list($number, $state) = $this->get_percentage($superset, $subset);
		} elseif ($this->threshold_type === 'range') {
			list($number, $state) = $this->get_range($superset, $subset);
		} elseif ($this->threshold_type === 'limit') {
			list($number, $state) = $this->get_limit($superset, $subset);
		}

		$view_path = $this->view_path('view');
		require($view_path);

	}
}
