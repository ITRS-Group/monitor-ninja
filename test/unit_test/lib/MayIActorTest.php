<?php
require_once ("op5/objstore.php");
require_once ("op5/mayi.php");

/**
 * This test suite tests the actor feature of the MayI class
 *
 * Verifies that the actors are resolved at the correct time, and its
 * output is sent to the rule sets.
 */
class MayIActorTest_SetInfoActor implements op5MayI_Actor {
	private $env = array ();
	public function setActorInfo(array $env) {
		$this->env = $env;
	}
	public function getActorInfo() {
		return $this->env;
	}
}
class MayIActorTest_EnvDumpConstraints implements op5MayI_Constraints {
	protected $env = false;
	protected $result = true;
	public function setResult($results = true) {
		$this->results = $results;
	}
	public function getEnv() {
		$env = $this->env;
		// Keep env set to false, to verify it's explicitly set
		$this->env = false;
		return $env;
	}
	public function run($action, $env, &$messages, &$perfdata) {
		$this->env = $env;
		return $this->result;
	}
}
class MayIActorTest extends PHPUnit_Framework_TestCase {
	public static $config = array ();

	/* Set up may_i configuration enviornment to runt tests within */
	public static function setUpBeforeClass() {
		/* Make sure we have control over all mockups */
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->mock_add('op5config',
			new MockConfig(array ('may_i' => self::$config)));
	}

	/* Clean up */
	public static function tearDownAfterClass() {
		/* Make sure we leave everything untouched */
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->clear();
	}
	public function setUp() {
		/* Make sure we start from scratch */
		op5objstore::instance()->clear();
	}

	/**
	 * Validate that the information is resolved at the time of the call
	 * ->run().
	 *
	 * This implicitly tests the basic functionality of adding and that values
	 * are
	 * passed through
	 */
	public function test_resolved_runtime() {
		$mayi = op5MayI::instance();
		$cs = new MayIActorTest_EnvDumpConstraints();
		$actor = new MayIActorTest_setInfoActor();

		/* Map the actor and constraints at the beginning */
		$mayi->be('subject', $actor);
		$mayi->act_upon($cs);

		/* Set environment and test */
		$actor->setActorInfo(array ('a' => 'b'));
		$this->assertTrue($mayi->run('something:stuff'));
		$this->assertEquals(array ('subject' => array ('a' => 'b')),
			$cs->getEnv());

		/* Set environment again, but don't relink, and test */
		$actor->setActorInfo(array ('a' => 'c'));
		$this->assertTrue($mayi->run('something:stuff'));
		$this->assertEquals(array ('subject' => array ('a' => 'c')),
			$cs->getEnv());

		/* Set environment again with new key, but don't relink, and test */
		$actor->setActorInfo(array ('b' => 'c'));
		$this->assertTrue($mayi->run('something:stuff'));
		$this->assertEquals(array ('subject' => array ('b' => 'c')),
			$cs->getEnv());
	}

	/**
	 * Test multiple actors in one mayi
	 */
	public function test_multiple_actors() {
		$mayi = op5MayI::instance();

		$cs = new MayIActorTest_EnvDumpConstraints();
		$mayi->act_upon($cs);

		$actora = new MayIActorTest_setInfoActor();
		$mayi->be('actA', $actora);

		$actorb = new MayIActorTest_setInfoActor();
		$mayi->be('actB', $actorb);

		/* Verity both actors values exists */
		$actora->setActorInfo(array ('a' => 1));
		$actorb->setActorInfo(array ('b' => 2));
		$this->assertTrue($mayi->run('something:stuff'));
		$this->assertEquals(
			array ('actA' => array ('a' => 1),'actB' => array ('b' => 2)),
			$cs->getEnv());

		/* Verity updating of values */
		$actora->setActorInfo(array ('a' => 3,'aa' => 4));
		$this->assertTrue($mayi->run('something:stuff'));
		$this->assertEquals(
			array ('actA' => array ('a' => 3,'aa' => 4),
				'actB' => array ('b' => 2)), $cs->getEnv());
	}

	/**
	 * Test that enviornemnt is passed through to all active constraints
	 */
	public function test_multiple_constraints() {
		$mayi = op5MayI::instance();

		$csa = new MayIActorTest_EnvDumpConstraints();
		$mayi->act_upon($csa);
		$csb = new MayIActorTest_EnvDumpConstraints();
		$mayi->act_upon($csb);

		$actor = new MayIActorTest_setInfoActor();
		$mayi->be('subject', $actor);

		$actor->setActorInfo(array ('stuff' => 'yep'));
		$this->assertTrue($mayi->run('something:stuff'));

		// The enviornemnt should exist in both constraints
		$this->assertEquals(array ('subject' => array ('stuff' => 'yep')),
			$csa->getEnv());
		$this->assertEquals(array ('subject' => array ('stuff' => 'yep')),
			$csb->getEnv());
	}

	/**
	 * Test that arguments can overwrite actor information when passed as an
	 * argument to ->run()
	 */
	public function test_argument_actor() {
		$mayi = op5MayI::instance();

		$cs = new MayIActorTest_EnvDumpConstraints();
		$mayi->act_upon($cs);

		$actora = new MayIActorTest_setInfoActor();
		$mayi->be('actA', $actora);

		$actorb = new MayIActorTest_setInfoActor();
		$mayi->be('actB', $actorb);

		$actora->setActorInfo(array ('a' => 1, 'x' => 4, 'y' => 5));
		$actorb->setActorInfo(array ('b' => 2));

		$this->assertTrue(
			$mayi->run('something:stuff', array (
				'actA' => array(
					'x' => 3,
					'k' => 7
				)
			)));

		$this->assertEquals(array (
			'actA' => array(
				'a' => 1,
				'x' => 3,
				'y' => 5,
				'k' => 7
			),
			'actB' => array(
				'b' => 2
			)
		),
			$cs->getEnv());
	}

	/**
	 * Test that the actor "args" can be registered externally, and used if not
	 * passed to the run method as an argument
	 *
	 * The behaviour of "args" actor is no longer a special case, because the
	 * args is an array to override the values of actors when calling.
	 *
	 * This is still kepts as a test to validate that args isn't removed by some
	 * strange reason. (The test is still valid, so why remove it?)
	 */
	public function test_args_registered_externally() {
		$mayi = op5MayI::instance();

		$cs = new MayIActorTest_EnvDumpConstraints();
		$mayi->act_upon($cs);

		$actor = new MayIActorTest_SetInfoActor();
		$mayi->be('args', $actor);

		$actor->setActorInfo(array ('this' => 'exist'));
		$this->assertTrue($mayi->run('something:stuff'));
		$this->assertEquals(array ('args' => array ('this' => 'exist')),
			$cs->getEnv());
	}
}