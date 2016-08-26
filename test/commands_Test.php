<?php
require_once('op5/objstore.php');

/** Record incoming commands instead of executing them */
class mock_queryhandler_response extends op5queryhandler {
    /** Reset history */
    function __construct() {
        parent::__construct();
		$this->history = array();
		$this->outputs = array();
    }

    /**
     * Trap each command in public history, heavily reliant on
     * op5queryhandler's interface (call() and raw_call()) not
     * changing
     */
    function raw_call($command, $node = false) {
        // the php queryhandler caller adds an ending space.. we
        // don't want to add that to all our comparisons
        $command = trim($command);

        // also: compare naemon commands, not queryhandler input, since
        // it makes the test more verbose and less focused on the real
		// meaning of the test
		$command = preg_replace("/^@command run /", null, $command);
		$command_name = array_shift(explode(';', $command));
		$command_name = array_pop(explode(' ', $command_name));

		$this->history[] = $command;
		if (isset($this->outputs[$command_name]))
			return $this->outputs[$command_name];
        return "Bogus, fixed output";
	}

	function set_output_for_command ($command, $output) {
		 $this->outputs[$command] = $output;
	}

    /** @return string|null */
    function last_cmd() {
        return array_pop($this->history);
    }
}

/**
 * Command Test.
 *
 * @package    Unit_Test
 * @author     op5
 */
class Command_Test extends PHPUnit_Framework_TestCase {

	protected function setUp () {
		$this->query_handler = new mock_queryhandler_response();
		op5objstore::instance()->mock_add('op5queryhandler', $this->query_handler);
    }

    private function mock_data($tables) {
        foreach($tables as $driver => $tables) {
            op5objstore::instance()->mock_add(
                $driver,
                new ORMDriverNative($tables, null, $driver)
            );
        }
    }

	public function test_hostgroup_to_host_command_output_propagation () {

		$this->mock_data(array(
            'ORMDriverLS default' => array(
				'hostgroups' => array(
					array(
						'name' => 'Intini',
						'members' => 'Jabbaraj'
					)
				),
				'hosts' => array(
					array(
						'name' => 'Jabbaraj',
						'groups' => 'Intini'
					)
				)
            )
        ));

		$this->query_handler->set_output_for_command('SCHEDULE_HOST_DOWNTIME', 'Response from schedule host downtime');
		$group = HostGroupPool_Model::all()->reduce_by('name', 'Intini', '=')->one();

		$result = $group->schedule_host_downtime(time() + 1000, time() + 3600, false, null, null, 0, "");
		$this->assertEquals("Response from schedule host downtime", $result['output']);

	}

	public function test_hostgroup_to_service_command_output_propagation () {

		$this->mock_data(array(
            'ORMDriverLS default' => array(
				'hostgroups' => array(
					array(
						'name' => 'Intini',
						'members' => 'Jabbaraj'
					)
				),
				'hosts' => array(
					array(
						'name' => 'Jabbaraj',
						'groups' => 'Intini'
					)
				),
				'services' => array(
					array(
						'description' => 'Merry',
						'host' => 'Jabbaraj'
					)
				)
            )
        ));

		$this->query_handler->set_output_for_command('SCHEDULE_SVC_DOWNTIME', 'Response from schedule service downtime');
		$group = HostGroupPool_Model::all()->reduce_by('name', 'Intini', '=')->one();

		$result = $group->schedule_service_downtime(time() + 1000, time() + 3600, false, null, null, 0, "");
		$this->assertEquals("Response from schedule service downtime", $result['output']);

	}

	public function test_servicegroup_to_service_command_output_propagation () {

		$this->mock_data(array(
            'ORMDriverLS default' => array(
				'servicegroups' => array(
					array(
						'name' => 'Intini',
						'members' => 'Jabbaraj'
					)
				),
				'services' => array(
					array(
						'description' => 'Jabbaraj',
						'host' => 'Pippin',
						'groups' => 'Intini'
					)
				),
				'hosts' => array(
					array(
						'name' => 'Pippin'
					)
				)
            )
        ));

		$this->query_handler->set_output_for_command('SCHEDULE_SVC_DOWNTIME', 'Response from schedule service downtime');
		$group = ServiceGroupPool_Model::all()->reduce_by('name', 'Intini', '=')->one();

		$result = $group->schedule_service_downtime(time() + 1000, time() + 3600, false, null, null, 0, "");
		$this->assertEquals("Response from schedule service downtime", $result['output']);

	}

	public function test_servicegroup_to_host_command_output_propagation () {

		$this->mock_data(array(
            'ORMDriverLS default' => array(
				'servicegroups' => array(
					array(
						'name' => 'Intini',
						'members' => 'Merry'
					)
				),
				'hosts' => array(
					array(
						'name' => 'Jabbaraj'
					)
				),
				'services' => array(
					array(
						'description' => 'Merry',
						'host' => 'Jabbaraj',
						'groups' => 'Intini'
					)
				)
            )
        ));

		$this->query_handler->set_output_for_command('SCHEDULE_HOST_DOWNTIME', 'Response from schedule host downtime');
		$group = ServiceGroupPool_Model::all()->reduce_by('name', 'Intini', '=')->one();

		$result = $group->schedule_host_downtime(time() + 1000, time() + 3600, false, null, null, 0, "");
		$this->assertEquals("Response from schedule host downtime", $result['output']);

	}

}
