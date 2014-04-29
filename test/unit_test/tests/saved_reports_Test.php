<?php
class Saved_reports_Test extends PHPUnit_Framework_TestCase
{
	function setUp()
	{
		// sweep up leftover crap from other runs
		$db = Database::instance();
		$db->query('DELETE FROM saved_reports_objects');
		$db->query('DELETE FROM saved_reports_options');
		$db->query('DELETE FROM saved_reports');

		// categorize all non-custom SLA periods, so we have easy-ish access to them from SLA tests
		$opts = new Sla_options();
		$props = $opts->properties();
		$valid_periods = $props['report_period']['options'];
		unset($valid_periods['custom']);
		$valid_periods = array_keys($valid_periods);
		sort($valid_periods);

		$this->length = array(
			1 => array('lastmonth'),
			3 => array('last3months', 'lastquarter'),
			6 => array('last6months'),
			12 => array('lastyear', 'last12months'),
			-1 => array('thisyear')
		);

		$lenghts = array();
		foreach ($this->length as $items)
			$lenghts = array_merge($lenghts, $items);
		sort($lenghts);
		$this->assertEquals($valid_periods, $lenghts, 'Expected all non-custom periods to be categorized in a length - did you add a new report period?');

		$this->type = array(
			'rotating' => array('lastmonth', 'last3months', 'last6months'),
			'special_rotating' => array('lastquarter'),
			'static' => array('thisyear', 'lastyear', 'last12months'),
		);
		$types = array();
		foreach ($this->type as $items)
			$types = array_merge($types, $items);
		sort($types);
		$this->assertEquals($valid_periods, $types, 'Expected all non-custom periods to be categorized in a type - did you add a new report period?');
	}

	function tearDown()
	{
		Report_options::$now = null;
	}

	function test_CRUD()
	{
		$the_opts = array(
			'report_name' => 'TEST_REPORT',
			'report_type' => 'hosts',
			'objects' => array('monitor'),
			'report_period' => 'custom',
			'start_time' => time() - 3600,
			'end_time' => time(),
		);
		$opts = Report_options::setup_options_obj('avail', $the_opts);

		$this->assertSame(true, $opts->save($msg), "Failed to save report: $msg");
		$orig_report_id = $opts['report_id'];
		$this->assertGreaterThanOrEqual(0, $opts['report_id']);
		$this->assertNotEquals(false, $opts['report_id']);
		$res = Avail_options::get_all_saved();
		$this->assertContains($opts['report_id'], array_keys($res), "Expected to find report id {$opts['report_id']} in " . var_export($res, true));
		$this->assertSame($opts['report_name'], $res[$opts['report_id']]);

		$loaded = Report_options::setup_options_obj('avail', array('report_id' => $opts['report_id']));
		$loaded->expand();
		$this->assertEquals($opts, $loaded);
		$this->assertSame($orig_report_id, $loaded['report_id']);

		$opts['report_name'] = 'TEST_REPORT2';
		$this->assertNotEquals($opts, $loaded);

		$this->assertSame($orig_report_id, $opts['report_id']);
		$this->assertSame(true, $opts->save());
		$loaded = Report_options::setup_options_obj('avail', array('report_id' => $opts['report_id']));
		$this->assertSame($orig_report_id, $loaded['report_id']);
		$loaded->expand();
		$this->assertEquals($opts, $loaded);

		$this->assertSame(true, $opts->delete());
		$loaded = Report_options::setup_options_obj('avail', array('report_id' => $opts['report_id']));
		$this->assertSame(false, $loaded['report_id'], "Expected report_id to be set to false, because loading should fail");
		$res = Avail_options::get_all_saved();
		$this->assertNotContains($opts['report_name'], $res);
	}

	/**
	 * Check for bugs like #6821
	 */
	function test_add_object()
	{
		$the_opts = array(
			'report_name' => 'TEST_REPORT',
			'report_type' => 'hosts',
			'objects' => array('monitor'),
			'report_period' => 'custom',
			'start_time' => time() - 3600,
			'end_time' => time(),
		);
		$opts = Report_options::setup_options_obj('avail', $the_opts);

		$this->assertSame(true, $opts->save());
		$orig_report_id = $opts['report_id'];
		$loaded = Report_options::setup_options_obj('avail', array('report_id' => $opts['report_id']));
		$loaded->expand();
		$this->assertEquals($opts, $loaded);
		$opts->options['objects'][] = 'host_pending';
		$this->assertSame(true, $opts->save());
		$loaded = Report_options::setup_options_obj('avail', array('report_id' => $opts['report_id']));
		$loaded->expand();
		sort($opts->options['objects']);
		$this->assertEquals($opts, $loaded);
		$this->assertSame($orig_report_id, $loaded['report_id']);
		$this->assertSame(true, $opts->delete());
	}

	/**
	 * Relevant, as it's supposed to be an array that's stored serialized, which makes it a special case
	 *
	 * Yay, special case!
	 */
	function test_host_filter_status()
	{
		$the_opts = array(
			'report_name' => 'TEST_REPORT',
			'report_type' => 'hosts',
			'objects' => array('monitor'),
			'report_period' => 'custom',
			'start_time' => time() - 3600,
			'end_time' => time(),
			'host_filter_status' => array(Reports_Model::HOST_DOWN)
		);
		$opts = Report_options::setup_options_obj('avail', $the_opts);

		$this->assertSame(true, $opts->save());
		$orig_report_id = $opts['report_id'];
		$loaded = Report_options::setup_options_obj('avail', array('report_id' => $opts['report_id']));
		$loaded->expand();
		$this->assertEquals($opts, $loaded);
		$this->assertEquals($the_opts['host_filter_status'], $loaded['host_filter_status']);
		$this->assertSame(true, $opts->delete());
	}

	function test_sla_months()
	{
		$the_opts = array(
			'report_name' => 'TEST_REPORT',
			'report_type' => 'hosts',
			'objects' => array('monitor'),
			'report_period' => 'custom',
			'start_time' => time() - 3600,
			'end_time' => time(),
			'months' => array(date('n') => 99)
		);
		$opts = Report_options::setup_options_obj('sla', $the_opts);

		$this->assertSame(true, $opts->save());
		$orig_report_id = $opts['report_id'];
		$loaded = Report_options::setup_options_obj('sla', array('report_id' => $opts['report_id']));
		$this->assertNotEquals(null, $loaded);
		$loaded->expand();
		$this->assertEquals($opts, $loaded);
		$this->assertEquals($the_opts['months'], $loaded['months']);
		$this->assertSame(true, $opts->delete());
	}

	function test_wrong_type()
	{
		$the_opts = array(
			'report_name' => 'TEST_REPORT',
			'report_type' => 'hosts',
			'objects' => array('monitor'),
			'report_period' => 'custom',
			'start_time' => time() - 3600,
			'end_time' => time(),
			'months' => array(1 => 99)
		);
		$opts = Report_options::setup_options_obj('sla', $the_opts);

		$this->assertSame(true, $opts->save());
		$orig_report_id = $opts['report_id'];
		$loaded = Report_options::setup_options_obj('avail', array('report_id' => $opts['report_id']));
		$this->assertEquals(false, $loaded['report_id']);
		$this->assertSame(true, $opts->delete());
	}

	function test_overwrite()
	{
		$the_opts = array(
			'report_name' => 'TEST_REPORT',
			'report_type' => 'hosts',
			'objects' => array('monitor'),
			'report_period' => 'custom',
			'start_time' => time() - 3600,
			'end_time' => time(),
		);
		$opts = Report_options::setup_options_obj('avail', $the_opts);

		$this->assertSame(true, $opts->save($msg), $msg);
		unset($opts['report_id']);
		$this->assertSame(false, $opts->save($msg), $msg);
	}

	/*
	 * The following is intended to verify that saved reports that only covers part of
	 * the year will have defined SLA month values for all months - bug #7897
	 *
	 * In accordance with the old code, last12months will not rotate, which
	 * makes very little sense to me, but then the whole "rotation with varying
	 * SLA values" concept seems busted in the first place - why would it be OK
	 * in April to have 2% downtime in February, if it wasn't OK in Mars?
	 */

	/**
	 * This test *does* change indexes every month. That's awkward and
	 * annoying, but this is supposed to be the simple, "obvious" case
	 * that never fails - the special cases go below. Hence, if this fails
	 * only certain months, then those months belongs in the suite below.
	 */
	function test_sla_create_now()
	{
		$thismonth = date('n');
		foreach ($this->length as $months => $periods) {
			foreach ($periods as $period) {
				$the_opts = array(
					'report_name' => 'TEST_REPORT',
					'report_type' => 'hosts',
					'objects' => array('monitor'),
					'report_period' => $period,
				);
				$opts = Report_options::setup_options_obj('sla', $the_opts);
				for ($i = -5; $i < 15; $i++) {
					$ary = $opts['months'];
					$ary[$i] = $i;
					$opts['months'] = $ary;
				}
				if ($months == -1)
					$months = (int)date('n');
				$this->assertCount($months, $opts['months'], "Expected there to be $months elements for $period in ".var_export($opts['months'], true));

				$this->assertTrue($opts->save());

				$loaded = Report_options::setup_options_obj('sla', array('report_id' => $opts['report_id']));
				$this->assertCount($months, $loaded['months'], "Expected there to be $months elements for $period in ".var_export($loaded['months'], true));
				foreach($loaded['months'] as $key => $val) {
					$this->assertEquals($key, $val);
				}
				$this->assertTrue($opts->delete());
			}
		}
	}

	function test_sla_create_special()
	{
		$constant_times = array(
			'all the same year' => array(
				'time' => strtotime('2013-05-01 08:00:00'),
				'period' => 'last3months',
				'expected' => array(2 => 2, 3 => 3, 4 => 4)
			),
			'wrapped across year boundary' => array(
				'time' => strtotime('2013-02-01 08:00:00'),
				'period' => 'last6months',
				'expected' => array(8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 1 => 1)
			),
		);
		foreach ($constant_times as $case) {
			Report_options::$now = $case['time'];
			$the_opts = array(
				'report_name' => 'TEST_REPORT',
				'report_type' => 'hosts',
				'objects' => array('monitor'),
				'report_period' => $case['period'],
			);
			$opts = Report_options::setup_options_obj('sla', $the_opts);
			for ($i = -5; $i < 15; $i++) {
				$ary = $opts['months'];
				$ary[$i] = $i;
				$opts['months'] = $ary;
			}
			$this->assertSame($case['expected'], $opts['months']);

			$this->assertTrue($opts->save());

			Report_options::$now = $case['time'];
			$loaded = Report_options::setup_options_obj('sla', array('report_id' => $opts['report_id']));
			$this->assertCount(count($case['expected']), $loaded['months'], "Expected there to be {$case['expected']} elements for {$case['period']} in ".var_export($loaded['months'], true));
			foreach($loaded['months'] as $key => $val) {
				$this->assertEquals($key, $val, "$key should have value $val in a {$case['period']} report loaded on " . date('c', $case['time']));
			}
			$this->assertTrue($opts->delete());
		}
	}

	function test_sla_regular_rotation()
	{
		$rotation_cases = array(
			'loaded after saved unwrapped' => array(
				'save_time' => strtotime('2012-07-25 08:00:00'),
				'load_time' => strtotime('2012-08-01 12:00:00'),
				'converter' => function ($key) {
					return $key - 1;
				},
			),
			'loaded after saved, wrapped before, not wrapped after' => array(
				'save_time' => strtotime('2012-01-25 08:00:00'),
				'load_time' => strtotime('2012-08-01 12:00:00'),
				'converter' => function ($key) {
					$res = $key - 7;
					if ($res < 1)
						$res += 12;
					return $res;
				},
			),
			'loaded after saved, wrapped after, not wrapped before' => array(
				'save_time' => strtotime('2012-07-25 08:00:00'),
				'load_time' => strtotime('2013-02-01 12:00:00'),
				'converter' => function ($key) {
					$res = $key - 7;
					if ($res < 1)
						$res += 12;
					return $res;
				},
			),
			'loaded after saved, both wrapped' => array(
				'save_time' => strtotime('2012-02-25 08:00:00'),
				'load_time' => strtotime('2013-01-01 12:00:00'),
				'converter' => function ($key) {
					$res = $key - 11;
					if ($res < 1)
						$res += 12;
					return $res;
				},
			),
		);
		foreach ($rotation_cases as $descr => $case) {
			foreach ($this->type['rotating'] as $period) {
				Report_options::$now = $case['save_time'];
				$the_opts = array(
					'report_name' => 'TEST_REPORT',
					'report_type' => 'hosts',
					'objects' => array('monitor'),
					'report_period' => $period,
				);
				$opts = Report_options::setup_options_obj('sla', $the_opts);
				for ($i = -5; $i < 15; $i++) {
					$ary = $opts['months'];
					$ary[$i] = (float)$i;
					$opts['months'] = $ary;
				}

				$this->assertTrue($opts->save());

				Report_options::$now = $case['load_time'];
				$loaded = Report_options::setup_options_obj('sla', array('report_id' => $opts['report_id']));
				$this->assertCount(count($opts['months']), $loaded['months']);
				foreach ($loaded['months'] as $key => $val) {
					$this->assertEquals(call_user_func($case['converter'], $key), $val, "The callback for '$descr' over '$period' disagrees with the loaded value for month $key, was $val. Months: ".var_export($loaded['months'], true));
				}
				$this->assertTrue($opts->delete());
			}
		}
	}

	function test_sla_no_rotation()
	{
		$rotation_cases = array(
			'loaded after saved unwrapped' => array(
				'save_time' => strtotime('2012-07-25 08:00:00'),
				'load_time' => strtotime('2012-08-01 12:00:00'),
				'converter' => function ($key) {
					return $key - 1;
				},
			),
			'loaded after saved, wrapped before, not wrapped after' => array(
				'save_time' => strtotime('2012-01-25 08:00:00'),
				'load_time' => strtotime('2012-08-01 12:00:00'),
				'converter' => function ($key) {
					$res = $key - 7;
					if ($res < 1)
						$res += 12;
					return $res;
				},
			),
			'loaded after saved, wrapped after, not wrapped before' => array(
				'save_time' => strtotime('2012-07-25 08:00:00'),
				'load_time' => strtotime('2013-02-01 12:00:00'),
				'converter' => function ($key) {
					$res = $key - 7;
					if ($res < 1)
						$res += 12;
					return $res;
				},
			),
			'loaded after saved, both wrapped' => array(
				'save_time' => strtotime('2012-02-25 08:00:00'),
				'load_time' => strtotime('2013-01-01 12:00:00'),
				'converter' => function ($key) {
					$res = $key - 11;
					if ($res < 1)
						$res += 12;
					return $res;
				},
			),
		);
		foreach ($rotation_cases as $descr => $case) {
			foreach ($this->type['static'] as $period) {
				Report_options::$now = $case['save_time'];
				$the_opts = array(
					'report_name' => 'TEST_REPORT',
					'report_type' => 'hosts',
					'objects' => array('monitor'),
					'report_period' => $period,
				);
				$opts = Report_options::setup_options_obj('sla', $the_opts);
				for ($i = -5; $i < 15; $i++) {
					$ary = $opts['months'];
					$ary[$i] = (float)$i;
					$opts['months'] = $ary;
				}

				$this->assertTrue($opts->save());

				Report_options::$now = $case['load_time'];
				$loaded = Report_options::setup_options_obj('sla', array('report_id' => $opts['report_id']));
				if ($period == 'thisyear') {
					foreach ($opts['months'] as $idx => $val) {
						if (isset($loaded['months'][$idx]))
							$this->assertEquals($val, $loaded['months'][$idx]);
					}
				} else {
					$this->assertEquals($opts['months'], $loaded['months'], "Got unexpected array for test '$descr' over '$period'");
				}
				$this->assertTrue($opts->delete());
			}
		}
	}

	function test_sla_special_rotation()
	{
		$rotation_cases = array(
			'one month, one quarter' => array(
				'save_time' => strtotime('2012-03-25 08:00:00'),
				'load_time' => strtotime('2012-04-01 12:00:00'),
				'converter' => function ($key) {
					return $key - 3 + 12;
				},
			),
			'one month, zero quarters' => array(
				'save_time' => strtotime('2012-02-25 08:00:00'),
				'load_time' => strtotime('2012-03-01 12:00:00'),
				'converter' => function ($key) {
					return $key;
				},
			),
			'two month, one quarter' => array(
				'save_time' => strtotime('2012-02-25 08:00:00'),
				'load_time' => strtotime('2012-04-01 12:00:00'),
				'converter' => function ($key) {
					return $key - 3 + 12;
				},
			),
			'two month, zero quarters' => array(
				'save_time' => strtotime('2012-01-25 08:00:00'),
				'load_time' => strtotime('2012-03-01 12:00:00'),
				'converter' => function ($key) {
					return $key;
				},
			),
			'eleven months, one quarter' => array(
				'save_time' => strtotime('2012-04-25 08:00:00'),
				'load_time' => strtotime('2012-03-01 12:00:00'),
				'converter' => function ($key) {
					return $key - 9;
				},
			),
			'eleven months, zero quarters' => array(
				'save_time' => strtotime('2012-03-25 08:00:00'),
				'load_time' => strtotime('2012-02-01 12:00:00'),
				'converter' => function ($key) {
					return $key;
				},
			),
		);
		foreach ($rotation_cases as $descr => $case) {
			foreach ($this->type['special_rotating'] as $period) {
				Report_options::$now = $case['save_time'];
				$the_opts = array(
					'report_name' => 'TEST_REPORT',
					'report_type' => 'hosts',
					'objects' => array('monitor'),
					'report_period' => $period,
				);
				$opts = Report_options::setup_options_obj('sla', $the_opts);
				for ($i = -5; $i < 15; $i++) {
					$ary = $opts['months'];
					$ary[$i] = (float)$i;
					$opts['months'] = $ary;
				}
				$this->assertCount(3, $opts['months'], "Failure in $descr");

				$this->assertTrue($opts->save());

				Report_options::$now = $case['load_time'];
				$loaded = Report_options::setup_options_obj('sla', array('report_id' => $opts['report_id']));
				$this->assertCount(3, $loaded['months']);
				$this->assertCount(count($opts['months']), $loaded['months']);
				foreach ($loaded['months'] as $key => $val) {
					$this->assertEquals(call_user_func($case['converter'], $key), $val, "The callback for '$descr' over '$period' disagrees with the loaded value for month $key, was $val. Months: ".var_export($loaded['months'], true));
				}
				$this->assertTrue($opts->delete());
			}
		}
	}
}
