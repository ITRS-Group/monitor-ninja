<?php
/**
 * Big, fat TODO: Almost every method asks the DB for all data and returns it.
 * Instead, users should subscribe what they're interested in, and be fed that
 * data once the query runs, once.
 */
class Summary_Reports_Model extends Reports_Model
{
	# alert summary options
	private $summary_result = array();

	/**
	 * Used from the HTTP API
	 *
	 * @param $auth Op5Auth
	 * @return array
	 */
	function get_events($auth)
	{
		$querym = new Report_query_builder_Model($this->db_table, $this->options);
		$query = $querym->build_alert_summary_query
			('timestamp, event_type, host_name, service_description, ' .
		     'state, hard, retry, downtime_depth, output');

		// investigate if there are more rows available for this query,
		// with another set of pagination parameters
		$limit = $this->options['limit'] + 1;
		$offset = $this->options['offset'];

		if($this->options['include_comments']) {
			$query = "
			SELECT
				data.timestamp,
				data.event_type,
				data.host_name,
				data.service_description,
				data.state,
				data.hard,
				data.retry,
				data.downtime_depth,
				data.output,
				comments.username,
				comments.user_comment,
				comments.comment_timestamp
			FROM ($query) data
			LEFT JOIN
				ninja_report_comments comments
				ON data.timestamp = comments.timestamp
				AND data.host_name = comments.host_name
				AND data.service_description = comments.service_description
				AND data.event_type = comments.event_type";
		}
		$query .= " LIMIT ".$limit." OFFSET ". $offset;

		$events = $this->db->query($query)->result(false);
		$can_paginate = false;
		if(count($events) > $this->options['limit']) {
			$can_paginate = true;
		}

		return array(
			'can_paginate' => $can_paginate,
			'events' => $events, // note that this is the size you asked for, plus one
			'limit' => (int) $this->options['limit'],
			'offset' => (int) $this->options['offset']
		);
	}

	private function comparable_state($row)
	{
		return $row['state'] << 1 | $row['hard'];
	}

	/**
	 * Get alert summary for "top (hard) alert producers"
	 *
	 * @return Array in the form { rank => array() }
	 */
	public function top_alert_producers()
	{
		$start = microtime(true);
		$host_states = $this->options['host_states'];
		$service_states = $this->options['service_states'];
		$this->options['host_states'] = self::HOST_ALL;
		$this->options['service_states'] = self::SERVICE_ALL;
		$querym = new Report_query_builder_Model($this->db_table, $this->options);
		$query = $querym->build_alert_summary_query();
		$this->options['host_states'] = $host_states;
		$this->options['service_states'] = $service_states;

		$dbr = $this->db->query($query);
		if (!is_object($dbr)) {
			return false;
		}
		$dbr = $dbr->result(false);
		$result = array();
		$pstate = array();
		foreach ($dbr as $row) {
			if (empty($row['service_description'])) {
				$name = $row['host_name'];
				$interesting_states = $host_states;
			} else {
				$name = $row['host_name'] . ';' . $row['service_description'];
				$interesting_states = $service_states;
			}

			# only count true state-changes
			$state = $this->comparable_state($row);
			if (isset($pstate[$name]) && $pstate[$name] === $state) {
				continue;
			}
			$pstate[$name] = $state;

			# if we're not interested in this state, just move along
			if (!(1 << $row['state'] & $interesting_states)) {
				continue;
			}

			if (empty($result[$name])) {
				$result[$name] = 1;
			} else {
				$result[$name]++;
			}
		}

		# sort the result and return only the necessary items
		arsort($result);
		if ($this->options['summary_items'] > 0) {
			$result = array_slice($result, 0, $this->options['summary_items'], true);
		}

		$i = 1;
		$this->summary_result = array();
		foreach ($result as $obj => $alerts) {
				$ary = array();
			if (strstr($obj, ';')) {
				$obj_ary = explode(';', $obj);
				$ary['host_name'] = $obj_ary[0];
				$ary['service_description'] = $obj_ary[1];
				$ary['event_type'] = self::SERVICECHECK;
			} else {
				$ary['host_name'] = $obj;
				$ary['event_type'] = self::HOSTCHECK;
			}
			$ary['total_alerts'] = $alerts;
			$this->summary_result[$i++] = $ary;
		}
		return $this->summary_result;
	}

	private function set_alert_total_totals(&$result)
	{
		foreach ($result as $name => $ary) {
			$ary['total'] = 0;
			foreach ($ary as $type => $state_ary) {
				if ($type === 'total')
					continue;
				$ary[$type . '_totals'] = array('soft' => 0, 'hard' => 0);
				$ary[$type . '_total'] = 0;
				foreach ($state_ary as $sh) {
					$ary[$type . '_totals']['soft'] += $sh[0];
					$ary[$type . '_totals']['hard'] += $sh[1];
					$ary[$type . '_total'] += $sh[0] + $sh[1];
					$ary['total'] += $sh[0] + $sh[1];
				}
			}
			$result[$name] = $ary;
		}
	}

	private function alert_totals_by_host($dbr)
	{
		$template = $this->summary_result;
		$result = array();
		foreach ($this->options['objects'] as $hn) {
			$result[$hn] = $template;
		}
		$pstate = array();
		foreach ($dbr as $row) {
			if (empty($row['service_description'])) {
				$type = 'host';
				$sname = $row['host_name'];
			} else {
				$type = 'service';
				$sname = $row['host_name'] . ';' . $row['service_description'];
			}

			# only count real state-changes
			$state = $this->comparable_state($row);
			if (isset($pstate[$sname]) && $pstate[$sname] === $state) {
				continue;
			}
			$pstate[$sname] = $state;

			$name = $row['host_name'];
			$result[$name][$type][$row['state']][$row['hard']]++;
		}

		return $result;
	}

	private function alert_totals_by_service($dbr)
	{
		$template = $this->summary_result;
		$result = array();
		foreach ($this->options['objects'] as $name) {
			list($host, $svc) = explode(';', $name);
			# Assign host first, so it's position in the array is before services
			$result[$host] = $template;
			$result[$name] = $template;
		}
		$pstate = array();
		foreach ($dbr as $row) {
			if (!$row['service_description']) {
				$name = $row['host_name'];
				$type = 'host';
			}
			else {
				$name = $row['host_name'] . ';' . $row['service_description'];
				$type = 'service';
			}
			$state = $this->comparable_state($row);
			if (isset($pstate[$name]) && $pstate[$name] === $state) {
				continue;
			}
			$pstate[$name] = $state;
			$result[$name][$type][$row['state']][$row['hard']]++;
		}

		return $result;
	}


	private function alert_totals_by_hostgroup($dbr, $host_hostgroup)
	{
		# pre-load the result set to keep conditionals away
		# from the inner loop
		$template = $this->summary_result;
		$result = array();
		foreach ($this->options['objects'] as $hostgroup) {
			$result[$hostgroup] = $template;
		}

		$pstate = array();
		foreach ($dbr as $row) {
			if (empty($row['service_description'])) {
				$type = 'host';
				$name = $row['host_name'];
			} else {
				$type = 'service';
				$name = $row['host_name'] . ';' . $row['service_description'];
			}
			$state = $this->comparable_state($row);
			if (isset($pstate[$name]) && $pstate[$name] === $state) {
				continue;
			}
			$pstate[$name] = $state;
			$hostgroups = $host_hostgroup[$row['host_name']];
			foreach ($hostgroups as $hostgroup) {
				$result[$hostgroup][$type][$row['state']][$row['hard']]++;
			}
		}
		return $result;
	}


	private function alert_totals_by_servicegroup($dbr, $service_servicegroup)
	{
		# pre-load the result set to keep conditionals away
		# from the inner loop
		$template = $this->summary_result;
		$result = array();
		foreach ($this->options['objects'] as $servicegroup) {
			$result[$servicegroup] = $template;
		}

		$pstate = array();
		foreach ($dbr as $row) {
			if (empty($row['service_description'])) {
				$type = 'host';
				$name = $row['host_name'];
			} else {
				$type = 'service';
				$name = $row['host_name'] . ';' . $row['service_description'];
			}
			$state = $this->comparable_state($row);
			if (isset($pstate[$name]) && $pstate[$name] === $state) {
				continue;
			}
			$pstate[$name] = $state;

			$servicegroups = $service_servicegroup[$type][$name];
			foreach ($servicegroups as $sg) {
				$result[$sg][$type][$row['state']][$row['hard']]++;
			}
		}
		return $result;
	}

	/**
	 * Get alert totals. This is identical to the toplist in
	 * many respects, but the result array is different.
	 *
	 * @return Array of counts divided by object types and states
	 */
	public function alert_totals()
	{
		$querym = new Report_query_builder_Model($this->db_table, $this->options);
		$query = $querym->build_alert_summary_query();

		$dbr = $this->db->query($query)->result(false);
		if (!is_object($dbr)) {
			echo Kohana::debug($this->db->errorinfo(), explode("\n", $query));
		}

		# preparing the result array in advance speeds up the
		# parsing somewhat. Completing it either way makes it
		# easier to write templates for it as well.
		# We stash it in $this->summary_result so all functions
		# can take advantage of it
		for ($state = 0; $state < 4; $state++) {
			$this->summary_result['host'][$state] = array(0, 0);
			$this->summary_result['service'][$state] = array(0, 0);
		}
		unset($this->summary_result['host'][3]);

		$result = false;
		# groups must be first here, since the other variables
		# are expanded in the build_alert_summary_query() method
		switch ($this->options['report_type']) {
		 case 'servicegroups':
			$result = $this->alert_totals_by_servicegroup($dbr, $querym->service_servicegroup);
			break;
		 case 'hostgroups':
			$result = $this->alert_totals_by_hostgroup($dbr, $querym->host_hostgroup);
			break;
		 case 'services':
			$result = $this->alert_totals_by_service($dbr);
			break;
		 case 'hosts':
			$result = $this->alert_totals_by_host($dbr);
			break;
		}

		$this->set_alert_total_totals($result);
		$this->summary_result = $result;
		return $this->summary_result;
	}

	/**
	 * Find and return the latest $this->options['summary_items'] alert
	 * producers according to the search criteria.
	 */
	public function recent_alerts()
	{
		$querym = new Report_query_builder_Model($this->db_table, $this->options);
		$query = $querym->build_alert_summary_query('*');

		$query .= ' ORDER BY timestamp '.(isset($this->options['oldest_first']) && $this->options['oldest_first']?'ASC':'DESC');
		if ($this->options['summary_items'] > 0) {
			$query .= " LIMIT " . $this->options['summary_items'];
			if (isset($this->options['page']) && $this->options['page'])
				$query .= ' OFFSET ' . ($this->options['summary_items'] * ($this->options['page'] - 1));
		}

		$query = '
			SELECT
				data.*,
				comments.username,
				comments.user_comment
			FROM ('.$query.') data
			LEFT JOIN
				ninja_report_comments comments
				ON data.timestamp = comments.timestamp
				AND data.host_name = comments.host_name
				AND data.service_description = comments.service_description
				AND data.event_type = comments.event_type';

		$dbr = $this->db->query($query)->result(false);
		if (!is_object($dbr)) {
			echo Kohana::debug($this->db->errorinfo(), explode("\n", $query));
		}

		$this->summary_result = array();
		foreach ($dbr as $row) {
			if ($this->timeperiod->inside($row['timestamp']))
				$this->summary_result[] = $row;
		}

		return $this->summary_result;
	}

	/**
	 * Add a new comment to the event pointed to by the timestamp/event_type/host_name/service
	 */
	public static function add_event_comment($timestamp, $event_type, $host_name, $service, $comment, $username) {
		$db = Database::instance();
		$db->query('DELETE FROM ninja_report_comments WHERE timestamp='.$db->escape($timestamp).' AND event_type = '.$db->escape($event_type).' AND host_name = '.$db->escape($host_name).' AND service_description = '.$db->escape($service));
		$db->query('INSERT INTO ninja_report_comments(timestamp, event_type, host_name, service_description, comment_timestamp, username, user_comment) VALUES ('.$db->escape($timestamp).', '.$db->escape($event_type).', '.$db->escape($host_name).', '.$db->escape($service).', UNIX_TIMESTAMP(), '.$db->escape($username).', '.$db->escape($comment).')');
		return true;
	}
	/**
	*	Fetch alert history for histogram report
	* 	@param $slots array with slots to fill with data
	* 	@return array with keys: min, max, avg, data
	*/
	public function histogram($slots=false)
	{
		if (empty($slots) || !is_array($slots))
			return array();

		$breakdown = $this->options['breakdown'];
		$report_type = $this->options['report_type'];
		$newstatesonly = $this->options['newstatesonly'];

		# compute what event counters we need depending on report type
		$events = false;
		switch ($report_type) {
			case 'hosts': case 'hostgroups':
				if (!$this->options['host_states'] || $this->options['host_states'] == self::HOST_ALL) {
					$events = array(0 => 0, 1 => 0, 2 => 0);
				} else {
					$events = array();
					for ($i = 0; $i <= 2; $i++) {
						if (1 << $i & $this->options['host_states']) {
							$events[$i] = 0;
						}
					}
				}
				break;
			case 'services': case 'servicegroups':
				if (!$this->options['service_states'] || $this->options['service_states'] == self::SERVICE_ALL) {
					$events = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
				} else {
					$events = array();
					for ($i = 0; $i <= 3; $i++) {
						if (1 << $i & $this->options['service_states']) {
							$events[$i] = 0;
						}
					}
				}
				break;
		}

		# add event (state) counters to slots
		$data = false;
		foreach ($slots as $s => $l) {
			$data[$l] = $events;
		}

		# fields to fetch from db
		$fields = 'timestamp, event_type, host_name, service_description, state, hard, retry';
		$querym = new Report_query_builder_Model($this->db_table, $this->options);
		$query = $querym->build_alert_summary_query($fields);

		# tell histogram_data() how to treat timestamp
		$date_str = false;
		switch ($breakdown) {
			case 'monthly':
				$date_str = 'n';
				break;
			case 'dayofmonth':
				$date_str = 'j';
				break;
			case 'dayofweek':
				$date_str = 'N';
				break;
			case 'hourly':
				$date_str = 'H';
				break;
		}

		$res = $this->db->query($query)->result(false);
		if (!$res) {
			return array();
		}
		$last_state = null;
		foreach ($res as $row) {
			if ($newstatesonly) {
				if ($row['state'] != $last_state) {
					# only count this state if it differs from the last
					$data[date($date_str, $row['timestamp'])][$row['state']]++;
				}
			} else {
				$data[date($date_str, $row['timestamp'])][$row['state']]++;
			}
			$last_state = $row['state'];
		}

		$min = $events;
		$max = $events;
		$avg = $events;
		$sum = $events;
		if (empty($data))
			return array();

		foreach ($data as $slot => $slotstates) {
			foreach ($slotstates as $id => $val) {
				if ($val > $max[$id]) $max[$id] = $val;
				if ($val < $min[$id]) $min[$id] = $val;
				$sum[$id] += $val;
			}
		}
		foreach ($max as $v => $k) {
			if ($k != 0) {
				$avg[$v] = number_format(($k/count($data)), 2);
			}
		}
		return array('min' => $min, 'max' => $max, 'avg' => $avg, 'sum' => $sum, 'data' => $data);
	}
}
