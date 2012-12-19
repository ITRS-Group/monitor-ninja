<?php defined('SYSPATH') OR die('No direct access allowed.');

class HttpApiEvent_options_core extends Report_options {

	const MAX_EVENTS = 10000; /**< Pagination limit for events retrieved from HTTP API. Hardcoded, deal with it */

	private $limit;

        /**
         * Means to translate options back and forth between Report_options
         * terms and HTTP API parameters. Handles both input and output translation.
         */
        static $http_api_options = array(
                'alert_types' => array(
                        'options' => array(
                                'both' => 3,
                                'host' => 1,
                                'service' => 2
                        )
                ),
                'state_types' => array(
                        'options' => array(
                                'both' => 3,
                                'hard' => 2,
                                'soft' => 1
			)
                ),
                'host_states' => array(
                        'options' => array(
                                'all' => 7,
                                'problem' => 6,
                                'up' => 1,
                                'down' => 2,
                                'unreachable' => 4
                        )
                ),
                'service_states' => array(
                        'options' => array(
                                'all' => 15,
                                'problem' => 14,
                                'ok' => 1,
                                'warning' => 2,
                                'critical' => 4,
                                'unknown' => 8
                        )
		)
        );

	/**
	 * Specify properties to expose, adjusted for the HTTP API
	 *
	 * @param $options array = false
	 */
	function __construct($options = false)
	{
		parent::__construct(false); // allright, this is a bit hackish, but parent's constructor tries to set options
		// but we're not actually ready for that yet (see below? we modify the properties which are used by set_options

		// whitelist properties to use, reuse the previous definitions
		$this->properties = array_intersect_key(
			$this->properties,
			array_flip(array(
				'report_period',
				'alert_types',
				'state_types',
				'host_states',
				'service_states',
				'host_name',
				'service_description',
				'hostgroup',
				'servicegroup',
				'start_time',
				'end_time',
				'host_filter_status',
				'service_filter_status',
				'include_comments'
			))
		);
		$this->properties['include_comments'] = array(
			'type' => 'bool',
			'default' => false,
			'description' => "Include events' comments"
		);

		$limit = $this->limit = (int) Op5Config::instance()->getConfig('http_api.report.limit');
		if($limit > self::MAX_EVENTS || $limit < 1) {
			$this->limit = self::MAX_EVENTS;
			$limit = $this->limit."; you can decrease this value in http_api.yml";
		} else {
			$limit .= "; you can increase this value in http_api.yml";
		}
		$this->properties['limit'] = array(
			'type' => 'int',
			'default' => null,
			'description' => 'Include at most this many events (between 1 and '.$limit.')'
		);
		$this->properties['offset'] = array(
			'type' => 'int',
			'default' => 0,
			'description' => 'Skip the first <em>offset</em> events matching the rest of the query, well suited for pagination'
		);

		if($options) {
			// finally make the call which *can not* be set in parent::__construct() until all properties and
			// other boilerplate is set up
			$this->set_options($options);
		}
	}

        /**
	 * Listen for "http api" options/properties, instead of "report" options
         *
         * @param $type string
         * @param $report_info array = false
         * @return array
         */
        protected static function discover_options($type, $input = false)
        {
                $options = array();
                if($input) {
                        $options = $input;
                } elseif($_POST) {
                        $options = $_POST;
                } elseif($_GET) {
                        $options = $_GET;
                }
                if(isset($options['start_time']) && !isset($options['end_time'])) {
                        $options['end_time'] = time();
                }
		if(isset($options['start_time']) || isset($options['end_time'])) {
			// @todo workaround a nasty bug, implement this in Report_options directly
			$options['report_period'] = 'custom';
		}
		if(isset($options['host_name'])) {
			$options['host_name'] = (array) $options['host_name'];
		}
		if(isset($options['service_description'])) {
			$options['service_description'] = (array) $options['service_description'];
		}

                // translate "all" to valid int-bitmap, for example
                foreach($options as $key => $value) {
                        if(isset(self::$http_api_options[$key]) &&
                                isset(self::$http_api_options[$key]['options']) &&
                                isset(self::$http_api_options[$key]['options'][$value])
                        ) {
                                $options[$key] = self::$http_api_options[$key]['options'][$value];
                        }
                }
                return $options;
        }

        /**
         * @param $value mixed
         * @param $type string
         * @return string
         */
        function format_default($value, $type)
        {
                if($type == 'bool') {
                        return (int) $value;
                }
                if($type == 'array' || $type == 'objsel') {
                        if(empty($value)) {
                                return "[empty]";
                        }
                        return implode(", ", $value);
                }
                if($type == 'string' && !$value) {
                        return '[empty]';
                }
                if($type == 'enum') {
                        return "'$value'";
                }
		if($type == 'int' && empty($value) && $value !== 0) {
			return "[empty]";
		}
                return (string) $value;
        }

	/**
	 * Not as forgiving as the parent. (Why is parent forgiving?)
	 *
	 * @param $options array
	 * @throws Api_Error_Response
	 */
	function set_options($options) {
		foreach($options as $name => $value) {
			if(!$this->set($name, $value)) {
				throw new Api_Error_Response("Invalid value for option '$name'", 400);
			}
		}
	}

	/**
	 * Final step in the "from merlin.report_data row to API-output" process
	 *
	 * @param $row array
	 * @return array
	 */
	function to_output($row)
	{
		// transform values
		$type = $row['service_description'] ? 'service' : 'host';
		$row['event_type'] = Reports_Model::event_type_to_string($row['event_type'], $type, true);
		$row['state'] = strtolower(Current_status_Model::status_text($row['state'], true, $type));

		// rename properties
		$row['in_scheduled_downtime'] = $row['downtime_depth'];
		unset($row['downtime_depth']);
		if(isset($row['username'])) {
			// comments are included and we've got one of them!
			// let's produce some hierarchy
			$row['comment'] = array(
				'username' => $row['username'],
				'comment' => $row['user_comment'],
				'timestamp' => $row['comment_timestamp'],
			);
		}
		unset($row['username'], $row['user_comment'], $row['comment_timestamp']);

		return $row;
	}

	/**
	 * @todo be able to throw exceptions here to give feedback of
	 * *which* error we experienced, since, you know, there's at
	 * least one user (you) exposed to this API.. Help yourself
	 *
	 * @param $key string
	 * @param $value mixed
	 * @return boolean
	 */
	protected function validate_value($key, &$value)
	{
		if($key == 'limit') {
			if(!$value) {
				$value = $this->limit;
				return true;
			}
			if(!is_numeric($value)) {
				return false;
			}
			$value = (int) $value;
			if($value > $this->limit || $value < 1) {
				return false;
			}
			return true;
		}
		return parent::validate_value($key, $value);
	}
}
