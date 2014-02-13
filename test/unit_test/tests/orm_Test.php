<?php
require_once ('op5/objstore.php');
require_once ('op5/livestatus.php');
class ORM_Test extends PHPUnit_Framework_TestCase {

	/**
	 * The virtual environment this test is using
	 *
	 * @var array
	 */
	protected $objects = array (
		"commands" => array (
			array ('id' => 1,'line' => "this is a command",'name' => 'cmda'),
			array ('id' => 2,'line' => "this is a another command",
				'name' => 'cmdb'),
			array ('id' => 3,'line' => "this is a command again",
				'name' => 'cmdc')),
		"hosts" => array (
			array ('accept_passive_checks' => 1,'acknowledged' => 0,
				'acknowledgement_type' => 0,
				'action_url' => '/monitor/index.php/configuration/configure',
				'action_url_expanded' => '/monitor/index.php/configuration/configure',
				'active_checks_enabled' => 0,'address' => 'localhost',
				'alias' => 'OP5 Monitor Server',
				'check_command' => 'check-host-alive',
				'check_flapping_recovery_notification' => 0,
				'check_freshness' => 0,'check_interval' => 5,
				'check_options' => 0,'check_period' => '24x7',
				'check_source' => '','check_type' => 1,'checks_enabled' => 0,
				'childs' => array (),'comments' => array (5),
				'comments_with_info' => array (
					array (5,'a_user','A little comment')),
				'contact_groups' => array ('support-group'),
				'contacts' => array ('monitor'),'current_attempt' => 3,
				'current_notification_number' => 0,
				'custom_variable_names' => array ('TYPE'),
				'custom_variable_values' => array ('core'),
				'custom_variables' => array ('TYPE' => 'core'),
				'display_name' => 'monitor','downtimes' => array (),
				'downtimes_with_info' => array (),'event_handler' => '',
				'event_handler_enabled' => 1,'execution_time' => 0,
				'filename' => '','first_notification_delay' => 0,
				'flap_detection_enabled' => 1,
				'groups' => array ('unix-servers','op5_monitor_servers',
					'network'),'hard_state' => 2,'has_been_checked' => 1,
				'high_flap_threshold' => 0,'hourly_value' => 36,
				'icon_image' => 'op5eye.png',
				'icon_image_alt' => 'OP5 Monitor Server',
				'icon_image_expanded' => 'op5eye.png','id' => 3,
				'in_check_period' => 1,'in_notification_period' => 1,
				'initial_state' => 0,'is_executing' => 0,'is_flapping' => 0,
				'last_check' => 1391082608,'last_hard_state' => 0,
				'last_hard_state_change' => 1391082608,'last_notification' => 0,
				'last_state' => 0,'last_state_change' => 1391082608,
				'last_time_down' => 1381753813,'last_time_unreachable' => 0,
				'last_time_up' => 1390393394,'latency' => 0.939,
				'long_plugin_output' => 'Line one\\nLine two',
				'low_flap_threshold' => 0,'max_check_attempts' => 3,
				'modified_attributes' => 2,
				'modified_attributes_list' => array ('active_checks_enabled'),
				'name' => 'monitor','next_check' => 0,'next_notification' => 0,
				'no_more_notifications' => 0,'notes' => '',
				'notes_expanded' => '','notes_url' => '/',
				'notes_url_expanded' => '/','notification_interval' => 0,
				'notification_period' => '24x7','notifications_enabled' => 1,
				'num_services' => 35,'num_services_crit' => 5,
				'num_services_hard_crit' => 5,'num_services_hard_ok' => 26,
				'num_services_hard_unknown' => 4,'num_services_hard_warn' => 0,
				'num_services_ok' => 26,'num_services_pending' => 0,
				'num_services_unknown' => 4,'num_services_warn' => 0,
				'obsess' => 1,'obsess_over_host' => 1,'parents' => array (),
				'pending_flex_downtime' => 0,'percent_state_change' => 0,
				'perf_data' => '','plugin_output' => 'Never gonna make you cry,',
				'pnpgraph_present' => 1,'process_performance_data' => 1,
				'retry_interval' => 1,'scheduled_downtime_depth' => 0,
				'services' => array ('op5backup state','Zombie processes',
					'Users'),
				'services_with_info' => array (
					array ('op5backup state',2,1,'check_op5backup2 CRITICAL'),
					array ('Zombie processes',0,1,'PROCS OK: 0 processes'),
					array ('Users',0,1,'USERS OK - 1 users currently logged in')),
				'services_with_state' => array (array ('op5backup state',2,1),
					array ('Zombie processes',0,1),array ('Users',0,1)),
				'should_be_scheduled' => 0,'state' => 2,'state_type' => 1,
				'statusmap_image' => 'op5eye.png','total_services' => 35,
				'worst_service_hard_state' => 2,'worst_service_state' => 2,
				'x_3d' => 0,'y_3d' => 0,'z_3d' => 0)));
	/**
	 * Contains a reference to the mocked livestatus, so we can see some debug
	 * variables
	 *
	 * @var MockLivestatus
	 */
	protected $ls = false;
	/**
	 * Make sure the enviornment is clean, and livestatus is mocked
	 */
	public function setUp() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();
		$this->ls = new MockLivestatus($this->objects);
		op5objstore::instance()->mock_add('op5Livestatus', $this->ls);
	}
	/**
	 * Remove mock environment
	 */
	public function tearDown() {
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();
	}
	/**
	 * Test that when you fetch all columns, you get all columns exported,
	 * including virtual columns
	 */
	public function test_fetch_all() {
		$orm_structure = Module_Manifest_Model::get('orm_structure');

		$columns = array_merge(array_keys(Host_Model::$rewrite_columns),
			array_keys($orm_structure['hosts']));

		$this->fetch_and_test_single_host(false, $columns);
	}
	/**
	 * Test that when requesting a couple of columns, that is the columns you
	 * will get
	 */
	public function test_some_columns() {
		$req_cols = array ('name','comments');
		$exp_cols = array ('name','comments');
		$this->fetch_and_test_single_host($req_cols, $exp_cols);
		$this->assertContains('name', $this->ls->last_columns);
		$this->assertContains('comments', $this->ls->last_columns);
	}
	/**
	 * Test that nonexisting columns doesn't get fetched, and isn't exported
	 */
	public function test_nonexisting_columns() {
		$req_cols = array ('name','comments','boll');
		$exp_cols = array ('name','comments');
		$this->fetch_and_test_single_host($req_cols, $exp_cols);
		$this->assertContains('name', $this->ls->last_columns);
		$this->assertContains('comments', $this->ls->last_columns);
		$this->assertNotContains('boll', $this->ls->last_columns);
	}

	/**
	 * Test that when requesting a virtual column, that column is retreieved and
	 * exported, but no columns extra.
	 * Also verify that the dependent column actually is fetched
	 */
	public function test_virtual_columns() {
		$req_cols = array ('name','source_node');
		$exp_cols = array ('name','source_node');
		$this->fetch_and_test_single_host($req_cols, $exp_cols);
		/* source_node is dependent on check_source, but isn't used in macros */
		$this->assertContains('check_source', $this->ls->last_columns);
	}

	/**
	 * Test performance data processing.
	 *
	 * This doesn't actually test the ORM, but a helper entirely used by ORM
	 */
	public function test_performance_data_conversion() {
		$perf_data_str = "datasource=31 'Data Saucer'=32c;;;32;34 dattenSaucen=93%;~32:2;~3: invalid 'dd\'escaped'=13b:32";
		$expect = array ('datasource' => array ('value' => 31.0),
			'Data Saucer' => array ('value' => 32.0,'unit' => 'c','min' => 32.0,
				'max' => 34.0),
			'dattenSaucen' => array ('value' => 93.0,'unit' => '%',
				'warn' => '~32:2','crit' => '~3:','min' => 0.0,'max' => 100.0),
			'dd\'escaped' => array ('value' => 13.0,'unit' => 'b'));

		$perf_data = performance_data_Core::process_performance_data(
			$perf_data_str);
		$this->assertSame($perf_data, $expect);
	}

	/**
	 * Fetch a host object, and test that when requesting a couple of columns,
	 * only the columns in an expect list is exported
	 *
	 * @param $req_cols array,
	 *        	or false for all
	 * @param $exp_cols array
	 */
	private function fetch_and_test_single_host($req_cols, $exp_cols) {
		$obj = HostPool_Model::all()->it($req_cols)->current()->export();

		foreach ($exp_cols as $col) {
			/* Also assert that rewrite columns exists */
			$this->assertArrayHasKey($col, $obj);
			unset($obj[$col]);
		}
		/* And when everything is removed, the array should be empty */
		$this->assertEmpty($obj);
	}
}
