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
	 * @param $config string
	 * @return op5MayI
	 */
	public static function instance($config = null) {
		return op5objstore::instance()->obj_instance(__CLASS__, $config);
	}

	/**
	 * Helper function to explode namespace set string for passing to
	 * is_subset_exploded(). Unless you're calling this from a very hot
	 * codepath and caching the result, you probably want is_subset(), instead.
	 *
	 * @param $namespace string A namespace set string of the form "a.b:c.d"
	 * @return array
	 */
	public static function explode_namespace_set($namespace) {
		return array_map ( function ($field) {
			return array_filter( explode ( ".", $field ) );
		}, explode ( ':', $namespace) );
	}

	/**
	 * This function takes a namespace subset and a namespace world and
	 * determines whether the former is a (strict or non-strict) subset of the
	 * latter according to the following rules:
	 *
	 * A namespace set consists of two partitions, separated by a colon (:) .
	 * Each partition consists of zero or more segments, separated by a period
	 * (.).  Both partitions have an implicit root, which is the superset of
	 * all other sets in its partition.
	 *
	 * A namespace set S is said to be a subset of a namespace set W iff for
	 * both partitions:
	 *
	 * for each segment in W; S has an identical corresponding segment on the
	 * same depth, where depth is determined by the number of segments preceding
	 * this one.
	 *
	 * Examples:
	 *    'a:b' is a subset of ':'
	 *    'a.b:c.d' is a subset of 'a:c'
	 *    'b:c' is NOT a subset of 'a:c'
	 *    'a:c.d.e.f.g' is a subset of 'a:c'
	 *    'a:c' is NOT a subset of 'a.b:c.d'
	 *
	 *
	 * @param $subset string
	 * @param $world string 
	 * @return true if $subset is a subset of $world, false otherwise
	 */
	public static function is_subset($subset, $world) {
		return self::is_subset_exploded(
			self::explode_namespace_set($subset),
			self::explode_namespace_set($world)
		);
	}

	/**
	 * Like is_subset, but takes two arrays of size 2 representing the subset and the
	 * world, respectively.
	 *
	 * For example to determine the answer for is_subset("a.b:c.d", "b:c"), you
	 * would invoke this function as
	 *
	 *    is_subset_exploded(
	 *        array(
	 *            array("a", "b"),
	 *            array("c", "d")
	 *        ),
	 *        array(
	 *            array("b")
	 *            array("c")
	 *       )
	 *    );
	 *
	 * This function is public because of its use in hot codepaths. For most
	 * cases, you probably want to use is_subset() instead.
	 *
	 * @param array $subset
	 * @param array $world
	 * @return true if $subset is a subset of the world $world, false otherwise
	 *
	 * see documentation for is_subset() for more information
	 */
	public static function is_subset_exploded($subset, $world) {
		if (count($subset) != 2 || count($world) != 2)
			return false;

		for($i = 0; $i < 2; $i ++) {
			$subset_attr = $subset[$i];
			$world_attr = $world[$i];

			/* If this part isn't a subset bail out */
			if (array_slice($subset_attr, 0, count( $world_attr) ) != $world_attr) {
				return false;
			}
		}

		/* We passed all parts, accept */
		return true;
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
	 * Priorities controls which constraints are allowed to export messgaes.
	 *
	 * Given that at least one constraint denies access, only messages from
	 * the denying constraints with highest available priority are returned.
	 * Several constraints can have equal, and thus highest, priority at the
	 * same time.
	 *
	 * Given that all constaints allows access, all messages from all
	 * constraints are returned.
	 *
	 * metrics is passed through in the same way as messages.
	 *
	 * @param op5MayI_Constraints $constraints
	 * @param integer $priority The priority of the constraint, default to 0
	 * @return op5MayI
	 */
	public function act_upon(op5MayI_Constraints $constraints, $priority = 0) {
		$this->constraints[] = array($constraints,$priority);
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
		$environment = $this->get_environment($override);

		$constr_res = array();
		$allow = true;

		$log = op5log::instance('mayi');

		$log_debug = $log->loggable_level('debug');
		$log_notice = $log->loggable_level('notice');

		foreach ($this->constraints as $i => $rs) {
			list($obj, $priority) = $rs;
			$cur_messages = array();
			$cur_metrics = array();
			$cur_result = $obj->run($action, $environment, $cur_messages, $cur_metrics);
			$constr_res[] = array($cur_result, $priority, $cur_messages, $cur_metrics);
			if(!$cur_result) {
				if($log_debug)
					$log->log('debug', get_class($obj)." denies '$action'\n".Spyc::YAMLDump(array('environment' => $environment)));
				if($log_notice)
					$log->log('notice', get_class($obj)." denies '$action'\n".Spyc::YAMLDump(array('messages' => $cur_messages)));
				$allow = false;
			}
		}

		/* Filter out messages for the current result */
		$filt_res = array_filter($constr_res, function($r) use($allow) {return $r[0] == $allow;});

		/* Filter out messages of highest priority, if deny */
		if (! $allow) {
			$highest_prio = count( $filt_res ) > 0 ? max( array_map( function ($r) {
				return $r[1];
			}, $filt_res ) ) : 0;
			$filt_res = array_filter( $filt_res, function ($r) use($highest_prio) {
				return $r[1] == $highest_prio;
			} );
		}

		$messages = array ();
		$metrics = array ();
		foreach($filt_res as $res) {
			list($cur_result, $priority, $cur_messages, $cur_metrics) = $res;
			foreach($cur_messages as $message) {
				$messages[] = $message;
			}
			foreach($cur_metrics as $name => $metric) {
				$metrics[$name] = $metric;
			}
		}

		return $allow;
	}
}
