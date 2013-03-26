<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network health widget
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
*/
class Netw_health_Widget extends widget_Base {
	protected $duplicatable = true;

	# define warning/critical limit
	private $health_warning_percentage = 90;
	private $health_critical_percentage = 75;
	private $visible_precision = 2;
	private $host_img = false;
	private $service_img = false;
	private $crit_img = '/images/thermcrit.png';
	private $warn_img = '/images/thermwarn.png';
	private $ok_img = '/images/thermok.png';
	private $host_val = false;
	private $service_val = false;
	
	private $netw_health_config = <<<EOC
- HOSTS
[hosts] all
[hosts] state = 0
- SERVICE
[services] all
[services] state = 0
EOC;

	public function __construct($model)
	{
		parent::__construct($model);

		$this->health_warning_percentage =
		isset($this->model->setting['health_warning_percentage'])
		? $this->model->setting['health_warning_percentage']
		: $this->health_warning_percentage;

		$this->health_critical_percentage =
		isset($this->model->setting['health_critical_percentage'])
		? $this->model->setting['health_critical_percentage']
		: $this->health_critical_percentage;

		$this->visible_precision =
		isset($this->model->setting['visible_precision'])
		? $this->model->setting['visible_precision']
		: $this->visible_precision;
/* Remove this configuration for now... due to the awfulness of the
 * configuration interface, this shouldn't be nessecary to support in the
 * future.
 * 
 * There isn't a feature request with this, so let's remove it...
		$this->netw_health_config =
		isset($this->model->setting['netw_health_config'])
		? $this->model->setting['netw_health_config']
		: $this->netw_health_config;
*/
	}

	public function options()
	{
		$options = parent::options();
		$options[] = new option($this->model->name, 'health_warning_percentage', 'Warning Percentage Level', 'input', array(
			'style' => 'width:20px',
			'title' => sprintf(_('Default value: %s%%'), 90)), $this->health_warning_percentage);
		$options[] = new option($this->model->name, 'health_critical_percentage', 'Critical Percentage Level', 'input', array(
			'style' => 'width:20px',
			'title' => sprintf(_('Default value: %s%%'), 75)), $this->health_warning_percentage);
		$options[] = new option($this->model->name, 'visible_precision', 'Precision', 'input', array(
			'style' => 'width:20px',
			'title' => sprintf(_('Default value: %d'), 1)), $this->visible_precision);
/*
 * Because the configuration is to hard for now to support, this is commented out.
 * 
 * Otherwise this can be configured here...
 */
//		$options[] = new option($this->model->name, 'netw_health_config', 'Configuration', 'textarea', array(), $this->netw_health_config);

		return $options;
	}

	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');

		$health_warning_percentage = $this->health_warning_percentage;
		$health_critical_percentage = $this->health_critical_percentage;
		
		$visible_precision = intval( $this->visible_precision );
		if( $visible_precision < -1 ) $visible_precision = -1;
		if( $visible_precision > 10 ) $visible_precision = 10;

		/*
		 * Parse configuration text
		 * 
		 * Sets $bars to an array where each element is a array of three values, containing:
		 * - Name of bar
		 * - Query matching elements defining the set to measure on
		 * - Query matching which elements is included in the percentatage
		 * 
		 * Both queries needs to work on the same table. 
		 */
		$blocks = array();
		$bar_configs = array();
		foreach( explode("\n",$this->netw_health_config) as $line ) {
			$line = trim($line);
			if( $line == '' )
				continue;
			if( $line[0] == '-' ) {
				if( count($blocks) == 3 ) {
					$bar_configs[] = $blocks;
				}
				$blocks = array(trim(substr($line,1)));
			} else {
				$blocks[] = $line;
			}
		}
		if( count($blocks) == 3 ) {
			$bar_configs[] = $blocks;
		}
		
		/* Calculate stats */
		$bars = array();
		foreach($bar_configs as $bar) {
			list($name, $all_query, $sel_query) = $bar;
			$set_all = ObjectPool_Model::get_by_query($all_query);
			$set_sel = ObjectPool_Model::get_by_query($sel_query);
			list($count_all, $count_sel) = $set_all->stats(array($set_all, $set_sel));
			if( $count_all == 0 ) $count_all = 1;
			$bars[] = array(
				'label' => $name,
				'value' => 100.0*$count_sel/$count_all
				);
		}

		# set required extra resources
		require($view_path);
	}
}
