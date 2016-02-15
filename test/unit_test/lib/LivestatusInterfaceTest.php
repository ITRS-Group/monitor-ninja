<?php

require_once "op5/objstore.php";
require_once "op5/livestatus.php";

class mock_livestatus_connection {
	private $test;
	private $compare_buf;
	private $outbuf = '';
	private $custom_error = false;

	public function __construct($options) {
		$this->test = $options['test'];
		$this->test->lsconn = $this; /* circular referenses FTW :) */
		$this->buffer = array();
	}

	public function __destruct() {
	}

	public function connect() {
	}

	public function close() {
	}

	public function writeSocket($str) {
		/* If we don't want to test the result, don't... Used if tests tracks
		 * exceptions
		*/
		if($this->compare_buf === false)
			return;

		/* We should actually buffer here, but, what the heck, op5livestatus
		 * outputs everything in one writeSocket anyway...
		*/

		/* Convert compare_buf to a string. Strip empty lines, and trim if only space */
		$exp = implode("\n",array_filter(array_map('trim',$this->compare_buf)))."\n\n";
		$this->test->assertEquals($str, $exp, $this->custom_error?$this->custom_error:'Query doesn\'t match expected');
	}

	public function readSocket($len) {
		$ret = substr($this->outbuf, 0, $len);
		$this->outbuf = substr($this->outbuf, $len);
		return $ret;
	}

	public function mock_query($result, $lsq, $custom_error=false) {
		/* Mock result - what op5livestatus gets from livestatus */
		if(is_array($result)) {
			$result = utf8_decode(json_encode($result));
		}
		$result_code = 200;
		$this->outbuf = sprintf("%03d %11d\n%s", $result_code, strlen($result), $result);


		/* Mock query - what is expected op5livestatus to generate, false if don't care */
		if(!array($lsq) && $lsq !== false) {
			$lsq = array_map( 'trim', explode('\n', $lsq) );
		}
		$this->compare_buf = $lsq;

		$this->custom_error = $custom_error;
	}
}

class LivestatusInterfaceTest extends PHPUnit_Framework_TestCase
{
	public $ls;
	public $lsconn;

	/**
	 * Setup mock LDAP enviornment
	 */
	public function setUp() {
		$this->ls = new op5livestatus(array(
				'connection_class' => 'mock_livestatus_connection',
				'test' => $this
		));

	}

	/**
	 * Shut down mock LDAP environment
	 */
	public function tearDown() {
		unset($this->ls); /* Kill circular references */
		unset($this->lsconn); /* Kill circular references */
	}


	/**
	 * This test is primarly to test the test environemnt, just to make sure
	 * mocking the environment works
	 */
	public function test_simple_query() {
		$this->lsconn->mock_query(
				array(
						'data' => array(
						),
						'total_count' => 0
				),
				array(
						'GET hosts',
						'OutputFormat: wrapped_json',
						'ResponseHeader: fixed16',
						'AuthUser: theusername',
						'Columns: name',

				)
		);
		$this->ls->query("hosts", "", array('name'), array(
				'auth' => new User_Model(array(
						'username' => 'theusername',
						'auth_data' => array()
				))
		));
	}

	/**
	 * Test existance of the tables. If existing, don't get an exception, if not
	 * existing, an exception is expected.
	 */
	public function test_table_existance() {
		$tables = array(
				'commands'             => true,
				'comments'             => true,
				'contactgroups'        => true,
				'contacts'             => true,
				'downtimes'            => true,
				'hosts'                => true,
				'hostgroups'           => true,
				'services'             => true,
				'servicegroups'        => true,
				'status'               => true,
				'timeperiods'          => true,

				// Doesn't exist in our PHP-interface
				'log'                  => false,
				'hostsbygroup'         => false,
				'servicesbygroup'      => false,
				'servicesbyhostgroup'  => false,
				'columns'              => false,

				// Shouldn't even exist in livestatus
				'nevergonnagiveyouup'  => false,
				'nevergonnaletyoudown' => false,
				'nevergonnaturnaround' => false,
				'anddessertyou'        => false
		);
		foreach($tables as $table => $exists) {
			try {
				$this->lsconn->mock_query(
						array(
								'data' => array(),
								'total_count' => 0,
								'columns' => array(array())),
						/* We don't care about the result, because then we need to test auth here */
						false
				);
				$this->ls->query($table, "", false, array(
						'auth' => new User_Model(array(
								'username' => 'theusername',
								'auth_data' => array()
						))
				));
				$this->assertTrue( $exists, "Table '".$table."' doesn't exist, but no expection thrown");
			} catch( op5LivestatusException $e ) {
				if( $e->getMessage() == "Unknown table ".$table ) {
					$this->assertTrue( !$exists, "Table '".$table."' should exist, but got an expection");
				} else {
					throw $e; /* If not interesting exception, throw further */
				}
			}
		}
	}

	/**
	 * Test auth headers.
	 *
	 * See that the correct headers applies to tables
	 */
	public function test_table_authentication() {
		/* A list of all possible permissions to test with */
		$permissions_available = array(
				'host_view_all',
				'host_view_contact',
				'host_template_view_all',
				'service_view_all',
				'service_view_contact',
				'service_template_view_all',
				'hostgroup_view_all',
				'hostgroup_view_contact',
				'servicegroup_view_all',
				'servicegroup_view_contact',
				'hostdependency_view_all',
				'servicedependency_view_all',
				'hostescalation_view_all',
				'serviceescalation_view_all',
				'contact_view_contact',
				'contact_view_all',
				'contact_template_view_all',
				'contactgroup_view_contact',
				'contactgroup_view_all',
				'timeperiod_view_all',
				'command_view_all',
				'management_pack_view_all'
		);

		/* A list of tables to test, where value is an array of:
		 * [0] => an array of all permissions giving full access
		* [1] => the header that will restrict the permission for the user
		*/
		$tables = array(
				'commands' => array(
						array('command_view_all'),
						'Or: 0'
				),
				'comments' => array(
						array('host_view_all','service_view_all'),
						'AuthUser: theusername'
				),
				'contactgroups' => array(
						array('contactgroup_view_all'),
						'AuthUser: theusername'
				),
				'contacts' => array(
						array('contact_view_all'),
						'Filter: name = theusername'
				),
				'downtimes' => array(
						array('host_view_all','service_view_all'),
						'AuthUser: theusername'
				),
				'hosts' => array(
						array('host_view_all'),
						'AuthUser: theusername'
				),
				'hostgroups' => array(
						array('hostgroup_view_all'),
						'AuthUser: theusername'
				),
				'services' => array(
						array('service_view_all'),
						'AuthUser: theusername'
				),
				'servicegroups' => array(
						array('servicegroup_view_all'),
						'AuthUser: theusername'
				),
				'status' => array(
						array('system_information'),
						'Or: 0'
				),
				'timeperiods' => array(
						array('timeperiod_view_all'),
						'Or: 0'
				)
		);
		foreach($tables as $table => $args) {
			list($auth_perms, $header) = $args;
			foreach($permissions_available as $permission) {
				$auth_header = $header;
				if( in_array( $permission, $auth_perms) ) {
					$auth_header = false;
				}
				$this->lsconn->mock_query(
						array(
								'data' => array(),
								'total_count' => 0,
								'columns' => array(array()),
						),
						array_filter( array(
								'GET '.$table,
								'OutputFormat: wrapped_json',
								'ResponseHeader: fixed16',
								$auth_header,
						) ),
						'Invalid query for: '.$table.' permission: '.$permission
				);
				$this->ls->query($table, "", false, array(
						'auth' => new User_Model(array(
								'username' => 'theusername',
								'auth_data' => array(
										$permission => true
								)
						))
				));
			}
		}
	}


	/**
	 * This test verifies that filter lines is added to the livestatus query
	 */
	public function test_filter() {
		$this->lsconn->mock_query(
				array(
						'data' => array(
						),
						'total_count' => 0
				),
				array(
						'GET hosts',
						'OutputFormat: wrapped_json',
						'ResponseHeader: fixed16',
						'AuthUser: theusername',
						'Columns: name',
						'Line: 1',
						'Line: 2'

				)
		);
		$this->ls->query("hosts", "Line: 1\nLine: 2\n", array('name'), array(
				'auth' => new User_Model(array(
						'username' => 'theusername',
						'auth_data' => array()
				))
		));
	}


	/**
	 * This test verifies that filter lines is added to the livestatus query,
	 * even without a linebreak at the end
	 */
	public function test_filter_no_nl() {
		$this->lsconn->mock_query(
				array(
						'data' => array(
						),
						'total_count' => 0
				),
				array(
						'GET hosts',
						'OutputFormat: wrapped_json',
						'ResponseHeader: fixed16',
						'AuthUser: theusername',
						'Columns: name',
						'Line: 1',
						'Line: 2'

				)
		);
		$this->ls->query("hosts", "Line: 1\nLine: 2", array('name'), array(
				'auth' => new User_Model(array(
						'username' => 'theusername',
						'auth_data' => array()
				))
		));
	}


	/**
	 * This test verifies that filter lines is added to the livestatus query,
	 * even without a linebreak at the end
	 */
	public function test_filter_extra_nl() {
		$this->lsconn->mock_query(
				array(
						'data' => array(
						),
						'total_count' => 0
				),
				array(
						'GET hosts',
						'OutputFormat: wrapped_json',
						'ResponseHeader: fixed16',
						'AuthUser: theusername',
						'Columns: name',
						'Line: 1',
						'Line: 2'

				)
		);
		$this->ls->query("hosts", "Line: 1\nLine: 2\n\n", array('name'), array(
				'auth' => new User_Model(array(
						'username' => 'theusername',
						'auth_data' => array()
				))
		));
	}


	/**
	 * This test verifies that filter lines is added to the livestatus query,
	 * even without a linebreak at the end
	 */
	public function test_filter_only_nl() {
		$this->lsconn->mock_query(
				array(
						'data' => array(
						),
						'total_count' => 0
				),
				array(
						'GET hosts',
						'OutputFormat: wrapped_json',
						'ResponseHeader: fixed16',
						'AuthUser: theusername',
						'Columns: name'

				)
		);
		$this->ls->query("hosts", "\n", array('name'), array(
				'auth' => new User_Model(array(
						'username' => 'theusername',
						'auth_data' => array()
				))
		));
	}
}
