<?php
require_once('op5/objstore.php');

/** Record incoming commands instead of executing them */
class mock_queryhandler extends op5queryhandler {
	/** Reset history */
	function __construct() {
		parent::__construct();
		$this->history = array();
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
		$command = preg_replace("/^@command run /", '', $command);

		$this->history[] = $command;
		return "Bogus, fixed output";
	}

	/** @return string|null */
	function last_cmd() {
		return array_pop($this->history);
	}
}

class Orm_Command_Test extends \PHPUnit\Framework\TestCase {

	protected $objects = array (
		"hosts" => array(
			array(
				'name' => 'myhst'
			),
			array(
				'name' => 'a_hst'
			)
		),
		"services" => array (
			array (
				'host_name' => 'myhst',
				'description' => 's_a',
				'groups' => array('lightweight'),
			),
			array (
				'host_name' => 'myhst',
				'description' => 's_b',
				'groups' => array(),
			),
			array (
				'host_name' => 'a_hst',
				'description' => 's_c',
				'groups' => array('lightweight'),
			),
		),
		"servicegroups" => array (
			array(
				'name' => 'lightweight'
			)
		)
	);

	function setUp() : void {
		// capture all external commands
		$this->m = new mock_queryhandler();
		op5objstore::instance()->mock_add('op5queryhandler', $this->m);

		op5objstore::instance()->mock_add( 'op5Livestatus', new MockLivestatus( $this->objects, array (
			'allow_undefined_columns' => true
		) ) );

		// login as a common user to avoid it in every step,
		// since many of the commands want an "author"
		// parameter, which is implicitly the logged in one
		$this->author = 'ronnie';
		Op5Auth::factory(array('session_key' => false))
			->force_user(new User_Model(array('username' => $this->author)), true);
	}

	function tearDown() : void {
		op5objstore::instance()->mock_clear();
		op5auth::instance()->logout();
	}

	function test_host_disable_check() {
		$host_name = 'my cat had a hat';
		$host = Host_Model::factory_from_setiterator(array('name' => $host_name), '', array('name'));
		$host->disable_check();
		$this->assertMatchesRegularExpression(
			'/\[\d+\] DISABLE_HOST_CHECK;'.$host_name.'/',
			$this->m->last_cmd()
		);
	}

	/**
	 * Example of non 1:1-to-Naemon-API. Everytime there's a common use case
	 * that requires N parameters instead of N+15, we could/should make a
	 * shortcut for it to help the client of the ORM.
	 */
	function test_host_check_now_helper_method() {
		$now = time();
		$name = 'bosse bildoktor';
		$host = Host_Model::factory_from_setiterator(array('name' => $name), '', array('name'));
		$host->check_now();
		$this->assertMatchesRegularExpression(
			"/\[\d+\] SCHEDULE_HOST_CHECK;$name;$now/",
			$this->m->last_cmd()
		);
	}

	function test_servicegroup_schedule_service_downtime() {
		$now = time();
		$name = 'lightweight';
		$trigger_id = 0;
		$start_time = $now + 60;
		$end_time = $now + 60 + 3600;
		// TODO so, yeah.. should we stray apart from the Ninja GUI?
		// if so: replace calls to nagioscmd::build_command() with
		// something that doesn't call nagioscmd::massage_param()
		$duration_in = 1;
		$duration_out = 3600;
		$comment = 'baby';
		$fixed = 1;

		$sg = Servicegroup_Model::factory_from_setiterator(array('name' => $name), '', array('name'));

		$sg->schedule_service_downtime($start_time, $end_time, !$fixed, $duration_in, $trigger_id, $comment);
		$this->assertMatchesRegularExpression("/\[\d+\] SCHEDULE_SVC_DOWNTIME;a_hst;s_c;$start_time;$end_time;$fixed;$trigger_id;$duration_out;$this->author;$comment/", $this->m->last_cmd());
		$this->assertMatchesRegularExpression("/\[\d+\] SCHEDULE_SVC_DOWNTIME;myhst;s_a;$start_time;$end_time;$fixed;$trigger_id;$duration_out;$this->author;$comment/", $this->m->last_cmd());
		$this->assertNull($this->m->last_cmd());
	}

	function test_host_acknowledge_problem() {
		$host_name = 'ben';
		$comment = 'Aliens in the vents, nothing to worry about';
		$persistent = true;
		$notify = true;
		$sticky = true;

		$host = Host_Model::factory_from_setiterator(array('name' => $host_name), '', array('name'));
		$host->acknowledge_problem($comment, $persistent, $notify, $sticky);
		$this->assertMatchesRegularExpression(
			sprintf(
				'/\[\d+\] ACKNOWLEDGE_HOST_PROBLEM;%s;%d;%d;%d;%s;%s/',
				$host_name,
				$sticky ? 2 : 0, // If the "sticky" option is set to two (2), the acknowledgement will remain until the host returns to an UP state.
				$notify ? 1 : 0,
				$persistent ? 1 : 0,
				$this->author,
				$comment
			),
			$this->m->last_cmd()
		);
	}
}
