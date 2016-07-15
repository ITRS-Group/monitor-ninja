<?php
require_once ("op5/objstore.php");
require_once ("op5/mayi.php");

/**
 * This test suite tests the actor feature of the MayI class
 *
 * Verifies that the actors are resolved at the correct time, and its
 * output is sent to the rule sets.
 */
class MayIMetricTest_MetricProviderConstraints implements op5MayI_Constraints {
	/**
	 * List of metrics to return
	 *
	 * @var op5MayIMetric[]
	 */
	protected $metrics = false;
	/**
	 * Result for the request
	 *
	 * @var bool
	 */
	protected $result = true;

	/**
	 * Initialze the metric provider constraints
	 *
	 * @param op5MayIMetric[] $metrics
	 * @param bool $result
	 */
	public function __construct($metrics, $result = true) {
		$this->metrics = $metrics;
	}

	/**
	 * Run test constraint
	 *
	 * @see op5MayI_Constraints::run()
	 */
	public function run($action, $env, &$messages, &$metrics) {
		foreach ( $this->metrics as $name => $metric ) {
			$metrics[$name] = $metric;
		}
		return $this->result;
	}
}

/**
 * Test that MayI metrics interface works as intended
 */
class MayIMetricsTest extends PHPUnit_Framework_TestCase {
	/**
	 * op5config mock environment
	 */
	public static $config = array ();

	/**
	 * The MayI instance under test.
	 * Cleared between each test case
	 *
	 * @var op5MayI
	 */
	public $mayi;

	/**
	 * Make sure we have a defined environment for testing
	 */
	public static function setUpBeforeClass() {
		/* Make sure we have control over all mockups */
		op5objstore::instance()->mock_clear();
		op5objstore::instance()->mock_add( 'op5config', new MockConfig( array (
			'may_i' => self::$config
		) ) );
	}

	/**
	 * Make sure we don't leave any traces
	 */
	public static function tearDownAfterClass() {
		/* Make sure we leave everything untouched */
		op5objstore::instance()->mock_clear();
	}

	/**
	 * Clear transient singleton instances (non-PHPdoc)
	 *
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	public function setUp() {
		/* Make sure we start from scratch */
		op5objstore::instance()->clear();
		$this->mayi = op5MayI::instance();
	}

	/**
	 * Verify that the interface for the metric object is as expected
	 *
	 * SImply verifies that we don't change names or something..
	 */
	public function test_metric_object_interface() {
		$metric = new op5MayIMetric(23,4,103);
		$this->assertEquals(23, $metric->get_value());
		$this->assertEquals(4, $metric->get_min());
		$this->assertEquals(103, $metric->get_max());
	}

	/**
	 * Test that metrics are returned from the constraint
	 *
	 * The most basic case
	 */
	public function test_metric_provider() {
		$this->mayi->act_upon( new MayIMetricTest_MetricProviderConstraints( array (
			'metric_a' => new op5MayIMetric( 4, 3, 5 ),
			'metric_b' => new op5MayIMetric( 2, 3, 4 )
		) ) );
		$messages = false;
		$metrics = false;
		$this->assertTrue( $this->mayi->run( 'some:stuff', array (), $messages, $metrics ) );
		$this->assertSame( array (), $messages );
		$this->assertEquals( array (
			'metric_a' => new op5MayIMetric( 4, 3, 5 ),
			'metric_b' => new op5MayIMetric( 2, 3, 4 )
		), $metrics );
	}

	/**
	 * Test that metrics are returned from the constraint
	 *
	 * The most basic case
	 */
	public function test_metric_multiple_provider() {
		$this->mayi->act_upon( new MayIMetricTest_MetricProviderConstraints( array (
			'metric_a' => new op5MayIMetric( 4, 3, 5 ),
			'metric_b' => new op5MayIMetric( 2, 3, 4 )
		) ) );
		$this->mayi->act_upon( new MayIMetricTest_MetricProviderConstraints( array (
			'metric_c' => new op5MayIMetric( 7, 7, 7 ),
			'metric_d' => new op5MayIMetric( 2, 1, 3 )
		) ) );
		$messages = false;
		$metrics = false;
		$this->assertTrue( $this->mayi->run( 'some:stuff', array (), $messages, $metrics ) );
		$this->assertSame( array (), $messages );
		$this->assertEquals( array (
			'metric_a' => new op5MayIMetric( 4, 3, 5 ),
			'metric_b' => new op5MayIMetric( 2, 3, 4 ),
			'metric_c' => new op5MayIMetric( 7, 7, 7 ),
			'metric_d' => new op5MayIMetric( 2, 1, 3 )
		), $metrics );
	}
}