<?php
require_once ('op5/objstore.php');

class ORM_Test extends PHPUnit_Framework_TestCase {

	/**
	 * The virtual environment this test is using
	 *
	 * @var array
	 */
	protected $objects = array (
		"contacts" => array (
			array (
				'name' => 'superuser'
			)
		),
		"contactgroups" => array (
			array(
				'name' => 'admins',
				'members' => array('superuser')
			)
		),
		"commands" => array (
			array ('id' => 1,'line' => "this is a command",'name' => 'cmda'),
			array ('id' => 2,'line' => "this is a another command",
				'name' => 'cmdb'),
			array ('id' => 3,'line' => "this is a command again",
				'name' => 'cmdc')),"services" => array (),"comments" => array (),
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
				'x_3d' => 0,'y_3d' => 0,'z_3d' => 0)
			),
		"services" => array (
			array (
				'accept_passive_checks' => 1,
				'acknowledged' => 0,
				'acknowledgement_type' => 0,
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
				'description' => 'test-service',
				'host_name' => 'monitor',
				'host_state' => 0,
				'host_has_been_checked' => 1,
				'host_action_url' => '/monitor/index.php/configuration/configure',
				'host_action_url_expanded' => '/monitor/index.php/configuration/configure',
				'next_check' => 0,
				'next_notification' => 0,
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
				'x_3d' => 0,'y_3d' => 0,'z_3d' => 0)
			)
			);
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

		$columns = array_merge(array_keys(Host_Model::rewrite_columns()),
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
	 * Test whether sub object names is resolved, with correct virtual column rewriting
	 *
	 * We don't have any services in the test, but test that the correct columns is requested at least.
	 */
	public function test_sub_object_columns() {
		$req_cols = array ('description', 'host.name','state_text','host.state_text');
		$exp_cols = array ('description', 'host_name','state','has_been_checked','host_state','host_has_been_checked');
		ServicePool_Model::all()->it($req_cols);
		/* source_node is dependent on check_source, but isn't used in macros */
		sort($exp_cols);
		$this->assertEquals($exp_cols, $this->ls->last_columns);
	}

	/**
	 * Tests that implicitly generated LS association functions support flat relations
	 */
	public function test_ls_legacy_associations_with_flat_relations () {
		$host = HostPool_Model::all()->reduce_by('name', 'monitor', '=')->one();
		$this->assertInstanceOf('ServiceSet_Model', $host->get_services_set());
	}

	/**
	 * Tests that implicitly generated LS associations supports nested relations
	 */
	public function test_ls_legacy_associations_with_nested_relations () {
		$service = ServicePool_Model::all()->reduce_by('description', 'test-service', '=')->one();
		$this->assertInstanceOf('DowntimeSet_Model', $service->get_downtimes_set());
	}

	/**
	 * Test column renaming, which is having different name in backend and frontend
	 */
	public function test_renaming() {
		$req_cols = array ('description', 'host.name','notes_url','host.action_url');
		$exp_cols = array ('description', 'host_name','notes_url_expanded','host_action_url_expanded');
		ServicePool_Model::all()->it($req_cols);
		/* source_node is dependent on check_source, but isn't used in macros */
		sort($exp_cols);
		$this->assertEquals($exp_cols, $this->ls->last_columns);
	}

	/**
	 * Test sub sub objects should only resolve one level of object abstraction
	 *
	 * For example comment -> service -> host should just resolve as comment -> host.
	 */
	public function test_sub_sub_object_columns() {
		$req_cols = array ('id','is_service','host.name','service.description','service.host.state');
		$exp_cols = array ('id','is_service','host_name','service_description','host_state');
		CommentPool_Model::all()->it($req_cols);
		/* source_node is dependent on check_source, but isn't used in macros */
		sort($exp_cols);
		$this->assertEquals($exp_cols, $this->ls->last_columns);
	}

	/**
	 * Test if key columns is fetched automatically for base object (not for related)
	 */
	public function test_key_columns() {
		$req_cols = array ('comment','host.state');
		$exp_cols = array ('comment','host_state','id','is_service');
		CommentPool_Model::all()->it($req_cols);
		/* source_node is dependent on check_source, but isn't used in macros */
		sort($exp_cols);
		$this->assertEquals($exp_cols, $this->ls->last_columns);
	}

	/**
	 * @expectedException ORMException
	 * @expectedExceptionMessage Table 'hosts' has no column 'kaka'
	 */
	public function test_sort_on_missing_column() {
		HostPool_Model::all()->it(false, array('kaka'));
	}

	/**
	 * Test that config url contains the host name
	 *
	 * This test depends on the configuration containing the macro $HOSTNAME$,
	 * and that config_url is the same with or without name field
	 */
	public function test_host_config_url() {
		$obj = HostPool_Model::all()->it(array ('config_url'))->current();
		$config_url_single = $obj->get_config_url();

		$obj = HostPool_Model::all()->it(array ('config_url','name'))->current();
		$config_url_name = $obj->get_config_url();

		$this->assertSame($config_url_single, $config_url_name);

		$obj = HostPool_Model::all()->it(array ('name'))->current();
		$host_name = $obj->get_name();
		$this->assertTrue(
			false !== strpos($config_url_single, urlencode($host_name)));

		$this->assertNotEmpty($host_name);
	}

	public function test_saved_filter_setting_filter_sets_table () {
		$obj = new SavedFilter_Model();
		$obj->set_filter('[saved_filters] all');
		$this->assertEquals('saved_filters', $obj->get_filter_table());
	}

	/**
	 * Test create/update/delete
	 *
	 * This test uses an SQL table, which should be available always, and is
	 * writeable. It creates an object with a unique and random name, and should
	 * remove it afterwards. Thus, should be isolated, but depend on database.
	 */
	public function test_create_update_fetch_delete() {
		// A unique name, so we don't conflict with earlier failing tests
		$current_name = md5(uniqid());

		// Create object

		$obj = new SavedFilter_Model();
		$obj->set_filter('[saved_filters] all');
		$obj->set_filter_description('This is a filter used in tests');
		$obj->set_filter_name($current_name);

		$obj->save();
		unset($obj); // We wan't a clean environment to next step

		// Fetch object

		$set = SavedFilterPool_Model::all()->reduce_by('filter_name', $current_name, '=');
		$this->assertCount(1, $set);
		$obj = $set->one();

		$this->assertInstanceOf('SavedFilter_Model', $obj);
		$this->assertEquals('[saved_filters] all', $obj->get_filter());
		$this->assertEquals('This is a filter used in tests', $obj->get_filter_description());
		$this->assertEquals($current_name, $obj->get_filter_name());
		$this->assertEquals('saved_filters', $obj->get_filter_table());

		// Update object

		$obj->set_filter('[somerandomtable] all');
		// ->set_filter() should set filter table, verify that...
		$this->assertEquals('somerandomtable', $obj->get_filter_table());
		$obj->save();
		unset($obj); // We wan't a clean environment to next step

		// Fetch object again

		$set = SavedFilterPool_Model::all()->reduce_by('filter_name', $current_name, '=');
		$this->assertCount(1, $set);
		$obj = $set->one();

		$this->assertInstanceOf('SavedFilter_Model', $obj);
		$this->assertEquals('[somerandomtable] all', $obj->get_filter());
		$this->assertEquals('This is a filter used in tests', $obj->get_filter_description());
		$this->assertEquals($current_name, $obj->get_filter_name());
		$this->assertEquals('somerandomtable', $obj->get_filter_table());

		// Delete object

		$obj->delete();
		unset($obj); // We wan't a clean environment to next step

		// Fetch object, it should be gone

		$set = SavedFilterPool_Model::all()->reduce_by('filter_name', $current_name, '=');
		$this->assertCount(0, $set);
		$obj = $set->one();

		$this->assertSame(false, $obj);

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
