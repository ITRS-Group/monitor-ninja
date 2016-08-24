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
		// sweep up leftover crap from other runs
		$db = Database::instance();
		$db->query('DELETE FROM saved_reports_objects');
		$db->query('DELETE FROM saved_reports_options');
		$db->query('DELETE FROM saved_reports');

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

	function test_cannot_reuse_already_saved_report_name()
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

	/**
	 * @dataProvider fill_sla
	 * @group MON-6154
	 * @group time::start_and_end_of_report_period
	 */
	function test_sla_create_special($input, $expected, $time)
	{
		Report_options::$now = $time;
		$the_opts = array(
			'report_name' => 'TEST_REPORT',
			'report_type' => 'hosts',
			'objects' => array('monitor'),
			'months' => $input
		);
		$opts = Report_options::setup_options_obj('sla', $the_opts);
		$this->assertTrue($opts->save());

		Report_options::$now = $time;
		$loaded = Report_options::setup_options_obj('sla', array('report_id' => $opts['report_id']));
		$this->assertEquals($expected, $loaded['months']);
		$this->assertTrue($opts->delete());
	}

	function fill_sla()
	{
		$input_partial = array(8 => 8);
		$expected_partial = array(1 => 0.0, 2 => 0, 3 => 0,
			4 => 0, 5 => 0.0, 6 => 0.0, 7 => 0.0, 8 => 8, 9 => 0.0,
			10 => 0.0, 11 => 0.0, 12 => 0.0);
		$constant_times = array(
			'partially filled year' => array(
				'input' => $input_partial,
				'expected' => $expected_partial,
				'time' => strtotime('2013-05-01 08:00:00')
			),
		);
		return $constant_times;
	}
}
