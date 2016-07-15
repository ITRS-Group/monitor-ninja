<?php
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class ExpParser_Translator_Test extends PHPUnit_Framework_TestCase {
	protected $controller = false; /* Controller to test */

	public function setUp() {
		global $_SESSION;
		$_SESSION = array();

		/* Make sure our environment is clean */
		$objstore = op5objstore::instance();
		$objstore->clear();
		$objstore->mock_clear();

		$this->parser = new ExpParser_SearchFilter(array(
			'h'  => 'hosts',
			's'  => 'services',
			'c'  => 'comments',
			'hg' => 'hostgroups',
			'sg' => 'servicegroups',
			'si' => '_si'
		));

		$this->translator = new ExpParser_Translator(array(
			'hosts' => array( 'name', 'display_name', 'address', 'alias', 'notes' ),
			'services' => array( 'description', 'display_name', 'notes' ),
			'hostgroups' => array( 'name', 'alias' ),
			'servicegroups' => array( 'name', 'alias' ),
			'comments' => array( 'author', 'comment' ),
			'_si' => array('plugin_output', 'long_plugin_output')
		));

	}

	/*
	 * Those tests should test how the search from the ExpParser filter is converted to a live status query
	 *
	 * Tests handling the syntax of the filter shoudl be in expparser_searchfilter_Test,
	 * This is about columns and generation oh the query, and wildcard
	 */

	/* *****
	 * Test simple table access
	 */
	public function test_host() {
		$result = $this->parser->parse('h:kaka');
		$filters = $this->translator->translate($result);
		$this->assertEquals(
			array(
				'hosts' => '[hosts] (name ~~ "kaka" or display_name ~~ "kaka" or address ~~ "kaka" or alias ~~ "kaka" or notes ~~ "kaka")'
			), $filters
		);
	}

	public function test_service() {
		$result = $this->parser->parse('s:kaka');
		$filters = $this->translator->translate($result);
		$this->assertEquals(
			array(
				'services' => '[services] (description ~~ "kaka" or display_name ~~ "kaka" or notes ~~ "kaka")'
			), $filters
		);
	}

	public function test_hostgroups() {
		$result = $this->parser->parse('hg:kaka');
		$filters = $this->translator->translate($result);
		$this->assertEquals(
			array(
				'hostgroups' => '[hostgroups] (name ~~ "kaka" or alias ~~ "kaka")'
			), $filters
		);
	}

	public function test_servicegroups() {
		$result = $this->parser->parse('sg:kaka');
		$filters = $this->translator->translate($result);
		$this->assertEquals(
			array(
				'servicegroups' => '[servicegroups] (name ~~ "kaka" or alias ~~ "kaka")'
			), $filters
		);
	}

	public function test_status_info() {
		$result = $this->parser->parse('si:kaka');
		$filters = $this->translator->translate($result);
		$this->assertEquals(
			array(
				'hosts' => '[hosts] (plugin_output ~~ "kaka" or long_plugin_output ~~ "kaka")',
				'services' => '[services] (plugin_output ~~ "kaka" or long_plugin_output ~~ "kaka")'
			), $filters
		);
	}

	/* ******
	 * Test wildcard search
	 */
	public function test_wildcard() {
		$result = $this->parser->parse('h:aaa%bbb');
		$filters = $this->translator->translate($result);
		$this->assertEquals(
			array(
				'hosts' => '[hosts] (name ~~ "aaa.*bbb" or display_name ~~ "aaa.*bbb" or address ~~ "aaa.*bbb" or alias ~~ "aaa.*bbb" or notes ~~ "aaa.*bbb")'
			), $filters
		);
	}


	/* ******
	 * Test combined host/service (services by hosts)
	 */
	public function test_host_service() {
		$result = $this->parser->parse('h:kaka and s:pong');
		$filters = $this->translator->translate($result);
		$this->assertEquals(
			array(
				'services' => '[services] (description ~~ "pong" or display_name ~~ "pong" or notes ~~ "pong") and (host.name ~~ "kaka" or host.display_name ~~ "kaka" or host.address ~~ "kaka" or host.alias ~~ "kaka" or host.notes ~~ "kaka")'
			), $filters
		);
	}

	/* ******
	 * Test limit
	 */
	public function test_host_limit() {
		$result = $this->parser->parse('h:kaka limit=24');
		$filters = $this->translator->translate($result);
		$this->assertEquals(
			array(
				'limit' => 24,
				'hosts' => '[hosts] (name ~~ "kaka" or display_name ~~ "kaka" or address ~~ "kaka" or alias ~~ "kaka" or notes ~~ "kaka")'
			), $filters
		);
	}

}
