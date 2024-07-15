<?php
use PHPUnit\Framework\Attributes\DataProvider;
require_once ("op5/objstore.php");
require_once ("op5/mayi.php");

/**
 * This test suite tests the Constraints functionality of the mayi class
 *
 * Verifies the constraint execution, which order constraints are executed, and
 * how constraints are masked. Also message and metrics handling.
 *
 * This doesn't test the Personality interface, which is another test suite.
 */

class MayIConstraintsTest_TraceConstraints implements op5MayI_Constraints {
	protected $trace = array ();
	protected $results = array ();
	protected $messages = array ();
	protected $metrics = array ();
	public function __construct($results = array()) {
		$this->results = $results;
	}
	public function setResult($results) {
		$this->results = $results;
	}
	public function addMessage($message) {
		$this->messages[] = $message;
	}
	public function setPerfdata($metrics) {
		$this->metrics = $metrics;
	}
	public function getTrace() {
		$trace = $this->trace;
		$this->trace = array ();
		return $trace;
	}
	public function run($action, $env, &$messages, &$metrics) {
		$this->trace[] = $action;
		if (count($this->messages) > 0) {
			$messages[] = array_shift($this->messages);
		}
		foreach ($this->metrics as $k => $v) {
			$metrics[$k] = $v;
		}
		if (isset($this->results[$action]))
			return $this->results[$action];
		return true;
	}
}
class MayIConstraintsTest extends \PHPUnit\Framework\TestCase {
	public static $config = array ();

	/* Set up may_i configuration enviornment to runt tests within */
	public static function setUpBeforeClass() : void {
		/* Make sure we have control over all mockups */
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->mock_add('op5config',
			new MockConfig(array ('may_i' => self::$config)));
	}

	/* Clean up */
	public static function tearDownAfterClass() : void {
		/* Make sure we leave everything untouched */
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();
	}
	public function setUp() : void {
		/* Make sure we start from scratch */
		op5objstore::instance()->clear();
	}

	/**
	 * Requesting from a simple MayI should result in allow, since constraints are
	 * implicitly and:ed
	 */
	public function test_simple_setup() {
		$mayi = op5MayI::instance();
		$this->assertTrue($mayi->run('do.something.really.fun:doit'));
	}

	/**
	 * Make sure a request ends up with an empty list of messages and metrics
	 */
	public function test_simple_return_values() {
		$mayi = op5MayI::instance();
		$messages = false;
		$metrics = false;
		$this->assertTrue(
			$mayi->run('do.something.really.fun:doit', array (), $messages,
				$metrics));
		$this->assertEquals(array (), $messages);
		$this->assertEquals(array (), $metrics);
	}

	/**
	 * Make sure that Constraints are executed when asking for an action
	 */
	public function test_simple_constraints_execution() {
		$mayi = op5MayI::instance();
		$mayi->act_upon($c = new MayIConstraintsTest_TraceConstraints());

		$this->assertEquals(array (), $c->getTrace());
		$this->assertTrue($mayi->run('do.something.really.fun:doit'));
		$this->assertEquals(array ('do.something.really.fun:doit'),
			$c->getTrace());
	}

	/**
	 * Make sure that failing constraints passes through mayi
	 */
	public function test_simple_failing_constarints() {
		$mayi = op5MayI::instance();
		$c = new MayIConstraintsTest_TraceConstraints(
			array ('something.failing:stuff' => false));
		$mayi->act_upon($c);

		$this->assertEquals(array (), $c->getTrace());
		$this->assertFalse($mayi->run('something.failing:stuff'));
		$this->assertEquals(array ('something.failing:stuff'), $c->getTrace());
	}

	/**
	 * We had the behaviour of masking execution of constraints, which is
	 * changed. Verify that everything is executed always.
	 */
	public function test_multiple_constraints() {
		$mayi = op5MayI::instance();
		$mayi->act_upon($ca = new MayIConstraintsTest_TraceConstraints());
		$mayi->act_upon($cb = new MayIConstraintsTest_TraceConstraints());

		$ca->setResult(array ('x:stuff' => true));
		$cb->setResult(array ('x:stuff' => true));
		$this->assertTrue($mayi->run('x:stuff'));
		$this->assertEquals(array ('x:stuff'), $ca->getTrace());
		$this->assertEquals(array ('x:stuff'), $cb->getTrace());

		$ca->setResult(array ('x:stuff' => true));
		$cb->setResult(array ('x:stuff' => false));
		$this->assertFalse($mayi->run('x:stuff'));
		$this->assertEquals(array ('x:stuff'), $ca->getTrace());
		$this->assertEquals(array ('x:stuff'), $cb->getTrace());

		$ca->setResult(array ('x:stuff' => false));
		$cb->setResult(array ('x:stuff' => true));
		$this->assertFalse($mayi->run('x:stuff'));
		$this->assertEquals(array ('x:stuff'), $ca->getTrace());
		$this->assertEquals(array ('x:stuff'), $cb->getTrace());

		$ca->setResult(array ('x:stuff' => false));
		$cb->setResult(array ('x:stuff' => false));
		$this->assertFalse($mayi->run('x:stuff'));
		$this->assertEquals(array ('x:stuff'), $ca->getTrace());
		$this->assertEquals(array ('x:stuff'), $cb->getTrace());
	}

	/**
	 * Verify messages are returned
	 */
	public function test_simple_messages() {
		$mayi = op5MayI::instance();
		$mayi->act_upon($c = new MayIConstraintsTest_TraceConstraints());

		// Make sure first request returns an empty array
		$messages = false;
		$metrics = false;

		$this->assertTrue(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array (), $messages);

		// Make sure messages are returned
		$messages = false;
		$metrics = false;

		$c->addMessage('This is a message');

		$this->assertTrue(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array ('This is a message'), $messages);
	}

	/**
	 * Verify messages are returned from multiple result sets
	 */
	public function test_multi_resultset_messages() {
		$mayi = op5MayI::instance();
		$ca = new MayIConstraintsTest_TraceConstraints();
		$cb = new MayIConstraintsTest_TraceConstraints();
		$mayi->act_upon($ca);
		$mayi->act_upon($cb);

		// Make sure first request returns an empty array
		$messages = false;
		$metrics = false;

		$this->assertTrue(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array (), $messages);

		// Make sure messages are returned
		$messages = false;
		$metrics = false;

		$ca->addMessage('msg a');

		$this->assertTrue(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array ('msg a'), $messages);

		// Make sure messages are returned
		$messages = false;
		$metrics = false;

		$cb->addMessage('msg b');

		$this->assertTrue(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array ('msg b'), $messages);

		// Make sure all messages are returned
		$messages = false;
		$metrics = false;

		$ca->addMessage('msg c');

		$cb->addMessage('msg d');

		$this->assertTrue(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array ('msg c','msg d'), $messages);
	}

	/**
	 * Verify messages can be masked from result sets returning false
	 */
	public function test_masked_messages_metrics() {
		$mayi = op5MayI::instance();
		$ca = new MayIConstraintsTest_TraceConstraints();
		$cb = new MayIConstraintsTest_TraceConstraints();
		$mayi->act_upon($ca);
		$mayi->act_upon($cb);

		$ca->setPerfdata(array ('a' => 1,'x' => 3));
		$cb->setPerfdata(array ('b' => 2,'x' => 4));

		// Make sure messages are returned
		$messages = false;
		$metrics = false;

		$ca->addMessage('msg a');
		$ca->setResult(array ('some:stuff' => true));

		$cb->addMessage('msg b');
		$cb->setResult(array ('some:stuff' => true));

		$this->assertTrue(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array ('msg a','msg b'), $messages);
		// Last executed constraints should overwrite metrics
		$this->assertEquals(array ('a' => 1,'x' => 4,'b' => 2), $metrics);

		// Make sure messages are returned
		$messages = false;
		$metrics = false;

		$ca->addMessage('msg a');
		$ca->setResult(array ('some:stuff' => true));

		$cb->addMessage('msg b');
		$cb->setResult(array ('some:stuff' => false));

		$this->assertFalse(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array ('msg b'), $messages);
		// Last executed constraints should overwrite metrics
		$this->assertEquals(array ('b' => 2,'x' => 4), $metrics);

		// Make sure messages are returned
		$messages = false;
		$metrics = false;

		$ca->addMessage('msg a');
		$ca->setResult(array ('some:stuff' => false));

		$cb->addMessage('msg b');
		$cb->setResult(array ('some:stuff' => true));

		$this->assertFalse(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array ('msg a'), $messages);
		$this->assertEquals(array ('a' => 1,'x' => 3), $metrics);

		// Make sure messages are returned
		$messages = false;
		$metrics = false;

		$ca->addMessage('msg a');
		$ca->setResult(array ('some:stuff' => false));

		$cb->addMessage('msg b');
		$cb->setResult(array ('some:stuff' => false));

		$this->assertFalse(
			$mayi->run('some:stuff', array (), $messages, $metrics));
		$this->assertEquals(array ('msg a', 'msg b'), $messages);
		$this->assertEquals(array ('a' => 1,'x' => 4,'b' => 2), $metrics);
	}

	/**
	 * Returns a list of test scenareos
	 *
	 * All cases contains the result and three constraint settings. Each setting
	 * should be an array of:
	 *  - Result
	 *  - Priority
	 *  - If message shold be visible
	 */
	public function ds_msg_priorities() {
		return array (
			/*
			 * Test that all messages is visible if allowed, independent of
			 * priorities
			 */
			array (
				true,
				array (true,0,true),
				array (true,0,true),
				array (true,0,true)
			),
			array (
				true,
				array (true,0,true),
				array (true,1,true),
				array (true,1,true)
			),
			array (
				true,
				array (true,1,true),
				array (true,0,true),
				array (true,1,true)
			),
			array (
				true,
				array (true,1,true),
				array (true,1,true),
				array (true,1,true)
			),
			array (
				true,
				array (true,1,true),
				array (true,3,true),
				array (true,2,true)
			),
			/*
			 * Test that deny masks allow, but with priorities between deny
			 */
			array (
				false,
				array (false,0,true),
				array (true,0,false),
				array (true,0,false)
			),
			array (
				false,
				array (false,0,true),
				array (false,0,true),
				array (true,0,false)
			),
			array (
				false,
				array (true,0,false),
				array (false,0,true),
				array (true,0,false)
			),
			array (
				false,
				array (true,0,false),
				array (false,0,true),
				array (false,0,true)
			),
			/*
			 * Test that deny masks allow, even if allow has higher priority
			 */
			array (
				false,
				array (false,0,true),
				array (true,1,false),
				array (true,2,false)
			),
			array (
				false,
				array (false,0,false),
				array (false,1,true),
				array (true,2,false)
			),
			array (
				false,
				array (true,2,false),
				array (false,1,true),
				array (true,0,false)
			),
			array (
				false,
				array (true,2,false),
				array (false,1,true),
				array (false,0,false)
			)
		);
	}

	/**
	 * Verify that messages is returned given the correct priorities and result
	 */
	#[DataProvider('ds_msg_priorities')]
	public function test_msg_priorities($result, $ca_conf, $cb_conf, $cc_conf) {
		$mayi = op5MayI::instance();

		$ca = new MayIConstraintsTest_TraceConstraints(array('some:stuff' => $ca_conf[0]));
		$cb = new MayIConstraintsTest_TraceConstraints(array('some:stuff' => $cb_conf[0]));
		$cc = new MayIConstraintsTest_TraceConstraints(array('some:stuff' => $cc_conf[0]));

		$mayi->act_upon($ca, $ca_conf[1]);
		$mayi->act_upon($cb, $cb_conf[1]);
		$mayi->act_upon($cc, $cc_conf[1]);

		$ca->addMessage('msg a');
		$cb->addMessage('msg b');
		$cc->addMessage('msg c');

		// Make sure first request returns an empty array
		$messages = false;
		$metrics = false;

		$this->assertEquals($result,
				$mayi->run('some:stuff', array (), $messages, $metrics));

		$exp_msg = array();
		if($ca_conf[2]) $exp_msg[] = 'msg a';
		if($cb_conf[2]) $exp_msg[] = 'msg b';
		if($cc_conf[2]) $exp_msg[] = 'msg c';
		$this->assertEquals($exp_msg, $messages);
	}
}
