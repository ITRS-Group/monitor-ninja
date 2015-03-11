<?php
require_once (__DIR__ . '/config.php');
require_once (__DIR__ . '/objstore.php');

/**
 * Model for defining a metric used by MayI
 */
class op5MayIMetric {
	/**
	 * Value of the metric
	 *
	 * @var float
	 */
	private $value;
	/**
	 * Minimum possible value of the metric
	 *
	 * @var float|false
	 */
	private $min;
	/**
	 * Maximum possible value of the metric
	 *
	 * @var float|false
	 */
	private $max;
	public function __construct($value, $min = false, $max = false) {
		$this->value = $value;
		$this->min = $min;
		$this->max = $max;
	}

	/**
	 * Get the value of the metric
	 *
	 * @return float
	 */
	public function get_value() {
		return $this->value;
	}
	/**
	 * Get the minimum possible value of the metric
	 *
	 * @return float|false
	 */
	public function get_min() {
		return $this->min;
	}
	/**
	 * Get the maximum possible value of the metric
	 *
	 * @return float|false
	 */
	public function get_max() {
		return $this->max;
	}
}

/**
 * Interface for MayI environment providers (actors)
 */
interface op5MayI_Actor {
	/**
	 * Get information from the actor, as an array.
	 *
	 * The informaiton will be available to the contstaints, as a key in the
	 * envioronment array passed to the run method.
	 */
	public function getActorInfo();
}

/**
 * Interface for MayI constraints
 */
interface op5MayI_Constraints {
	/**
	 * Execute a action
	 *
	 * @param string $action
	 *        	name of the action, as "path.to.resource:action"
	 * @param array $env
	 *        	environment variables for the constraints
	 * @param array $messages
	 *        	referenced array to add messages to
	 * @param op5MayIMetric[] $metrics
	 *        	referenced array to add performance data to
	 */
	public function run($action, $env, &$messages, &$metrics);
}

/**
 * Main class for MayI
 *
 * MayI is a singleton, handles generic authorization.
 */
class op5MayI {
	protected $actors = array ();
	protected $constraints = array ();

	/**
	 * Return the active instance of the MayI object
	 *
	 * @param string $config
	 * @return op5MayI
	 */
	public static function instance($config = null) {
		return op5objstore::instance()->obj_instance(__CLASS__, $config);
	}

	/**
	 * Tell something about me (May I be, X?)
	 *
	 * Add some information about the environment. The actor is an object
	 * implementing the interface of op5MayI_Actor, and will be accessable
	 * through the name of the context.
	 *
	 * @param string $context
	 * @param op5MayI_Actor $actor
	 * @return op5MayI
	 */
	public function be($context, op5MayI_Actor $actor) {
		$this->actors[$context] = $actor;
		return $this;
	}

	/**
	 * Adds constaints to act upon, given an action.
	 * Constraints may be authorization, global configuration, or similar.
	 *
	 * Constraints is implemented through the interface of op5MayI_Constraints
	 *
	 * @param op5MayI_Constraints $constraints
	 * @return op5MayI
	 */
	public function act_upon(op5MayI_Constraints $constraints) {
		$this->constraints[] = $constraints;
		return $this;
	}

	/**
	 * Get information from all actors, packed as an array
	 *
	 * To debug and trace the system status, and the current information for
	 * debugging, make it possible to export the environment for debugging.
	 *
	 * @param array $override
	 *        values to override in the environement, which will be replaced
	 *        by the values in this array.
	 * @return array
	 */
	public function get_environment(array $override = array()) {
		$environment = array ();
		foreach ($this->actors as $context => $actor) {
			$contextparts = array_filter(
					explode('.', $context),
					function($val) { return $val !== ''; }
			);
			$envref =& $environment;
			foreach($contextparts as $part) {
				if (!isset($envref[$part]) ) {
					$envref[$part] = array();
				}
				$envref =& $envref[$part];
			}
			$envref = $actor->getActorInfo();
			unset($envref);
		}
		$environment = array_replace_recursive($environment, $override);
		return $environment;
	}

	/**
	 * Run an action, and return if you may do the given action.
	 *
	 * The method returns if an action is allowed to execute given the
	 * circumstanses of the enviornment (see be-method), and the constraints
	 * (see act_upon method)
	 *
	 * @param string $action
	 *        	the action in the format of "path.to.resource:action"
	 * @param array $override
	 *        values to override in the environement, which will be replaced
	 *        by the values in this array.
	 * @param array $messages
	 *        	returns a list of messsages from constraints
	 * @param op5MayIMetric[] $metrics
	 *        	returns a list of perfomrance data from constraints
	 * @return boolean
	 */
	public function run($action, array $override = array(), &$messages = false, &$metrics = false) {
		$messages = array ();
		$metrics = array ();

		$environment = $this->get_environment($override);

		foreach ($this->constraints as $rs) {
			if (!$rs->run($action, $environment, $messages, $metrics)) {
				op5log::instance('mayi')->log('debug', get_class($rs)." denies '$action'\n".Spyc::YAMLDump(array('environment' => $environment)));
				op5log::instance('mayi')->log('notice', get_class($rs)." denies '$action'\n".Spyc::YAMLDump(array('messages' => $messages)));
				return false;
			}
		}

		return true;
	}
}
