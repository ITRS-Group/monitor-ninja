<?php

require_once("op5/objstore.php");
require_once("op5/log.php");

class LogTest extends PHPUnit_Framework_TestCase
{
	static public $config = array(
			'lvl_debug' => array(
					'level' => 'debug',
					'file' => 'a'
			),
			'lvl_notice' => array(
					'level' => 'notice',
					'file' => 'b'
			),
			'lvl_warning' => array(
					'level' => 'warning',
					'file' => 'c'
			),
			'lvl_error' => array(
					'level' => 'error',
					'file' => 'd'
			),
			'same_file_a' => array(
					'level' => 'debug',
					'file' => 'e'
			),
			'same_file_b' => array(
					'level' => 'debug',
					'file' => 'e'
			)
	);



	/* Set up logging enviornment to runt tests within */
	public static function setUpBeforeClass() {
		op5objstore::instance()->mock_clear();
		$filenames = array();
		foreach( self::$config as $ns => &$cfg ) {
			$filetag = $cfg['file'];
			if( !isset($filenames[$filetag]) ) {
				$filenames[$filetag] = tempnam('/tmp', 'op5lib_log_test_');
			}
			$cfg['file'] = $filenames[$filetag];
		}
		unset($cfg); /* Drop the reference $cfg */

		op5objstore::instance()->mock_add( 'op5config',
		new MockConfig(array('log' => self::$config)) );
	}

	public static function tearDownAfterClass() {
		// Make sure everything is written, so we don't need to create files once they are removed
		op5log::writeback();

		$files = array();

		foreach( self::$config as $ns => $cfg ) {
			/* Save as keys, makes it implicitly unique */
			$files[$cfg['file']] = true;
		}

		foreach( $files as $file => $v ) {
			unlink($file);
		}
	}

	static public function getOutputRawNS($namespace) {
		$file = self::$config[$namespace]['file'];
		$size = filesize($file);

		$fp = fopen($file, 'r+');
		$content = $size == 0 ? '' : fread($fp, filesize($file));
		ftruncate($fp,0);
		fclose($fp);

		return $content;
	}

	static public function getOutputNS($namespace) {
		$content_str = self::getOutputRawNS($namespace);
		$content = array_filter(
				array_map(
						function($line) {
							if( 0==strlen(trim($line)) )
								return false;
							$time  = trim( substr( $line,  0, 20 ) );
							$level = trim( substr( $line, 20,  7 ) );
							$text  = trim( substr( $line, 27     ) );

							list( $prefix, $content ) = explode( ': ', $text, 2 );
							return array($level, $prefix, $content);
						},
						explode("\n", $content_str)
		)
		);

		return $content;
	}


	function setUp() {
		/* Make sure all log files are empty to ensure isolated tests */
		op5log::writeback();
		foreach( self::$config as $ns => $cfg ) {
			$fp = fopen($cfg['file'], 'r+');
			ftruncate($fp,0);
			fclose($fp);
		}
	}

	function test_simple_logging()
	{
		op5log::instance('lvl_debug')->log('debug','message');
		op5log::writeback();

		$content = self::getOutputNS('lvl_debug');
		$this->assertEquals( $content, array(
				array('debug', 'lvl_debug', 'message')
		), 'Output doesn\'t match' );
	}

	function test_levels()
	{
		$levels = array('error','warning','notice','debug');

		foreach( $levels as $loglvl ) {
			foreach( $levels as $msglvl ) {
				op5log::instance('lvl_'.$loglvl)->log($msglvl,'Log: '.$loglvl.' x '.$msglvl);
			}
		}

		op5log::writeback();
		$logs = array();
		foreach($levels as $loglvl) {
			$logs[$loglvl] = self::getOutputNS('lvl_'.$loglvl);
		}
		$this->assertEquals( $logs, array(
				'error' => array(
						array('error',   'lvl_error',   'Log: error x error')
				),
				'warning' => array(
						array('error',   'lvl_warning', 'Log: warning x error'),
						array('warning', 'lvl_warning', 'Log: warning x warning')
				),
				'notice' => array(
						array('error',   'lvl_notice',  'Log: notice x error'),
						array('warning', 'lvl_notice',  'Log: notice x warning'),
						array('notice',  'lvl_notice',  'Log: notice x notice')
				),
				'debug' => array(
						array('error',   'lvl_debug',   'Log: debug x error'),
						array('warning', 'lvl_debug',   'Log: debug x warning'),
						array('notice',  'lvl_debug',   'Log: debug x notice'),
						array('debug',   'lvl_debug',   'Log: debug x debug')
				),
		), 'Output doesn\'t match' );
	}

	function test_undefined_log_output()
	{
		/* Shouldn't return any errors... no way to test it doesn't store
		 * anything though.
		*/
		op5log::instance('doesntexist')->log('debug','message');
		$this->assertTrue(true, 'Doesn\'t crash, which is correct');
	}

	function test_interleaved_references()
	{
		/* If by some reason instance($ns) set's a state somewhere, interleave
		 * two calls, and make sure those doesn't interfere.
		*/

		$log_w = op5log::instance('lvl_warning');
		$log_d = op5log::instance('lvl_debug');

		$log_w->log('error', 'a');
		$log_w->log('error', 'b');
		$log_d->log('error', 'c');
		$log_w->log('error', 'd');
		$log_d->log('error', 'e');

		op5log::writeback();

		$content = self::getOutputNS('lvl_warning');
		$this->assertEquals( $content, array(
				array('error', 'lvl_warning', 'a'),
				array('error', 'lvl_warning', 'b'),
				array('error', 'lvl_warning', 'd')
		), 'Output doesn\'t match for lvl_warning' );

		$content = self::getOutputNS('lvl_debug');
		$this->assertEquals( $content, array(
				array('error', 'lvl_debug', 'c'),
				array('error', 'lvl_debug', 'e')
		), 'Output doesn\'t match for lvl_debug' );
	}

	function test_interleaved_same_file()
	{
		$log_a = op5log::instance('same_file_a');
		$log_b = op5log::instance('same_file_b');

		$log_a->log('error', 'a');
		$log_a->log('error', 'b');
		$log_b->log('error', 'c');
		$log_a->log('error', 'd');
		$log_b->log('error', 'e');

		op5log::writeback();

		/* Read one of those, should include all */
		$content = self::getOutputNS('same_file_a');
		$this->assertEquals( $content, array(
				array('error', 'same_file_a', 'a'),
				array('error', 'same_file_a', 'b'),
				array('error', 'same_file_b', 'c'),
				array('error', 'same_file_a', 'd'),
				array('error', 'same_file_b', 'e')
		), 'Output doesn\'t match for interleaved, same file' );
	}

	function test_log_exception() {
		op5log::instance('lvl_error')->log('error',
			new Exception('dummy exception')
		);
		op5log::writeback();
		$content = self::getOutputNS('lvl_error');
		$this->assertEquals( $content[0], array('error', 'lvl_error', 'exception: dummy exception'), 'Invalid exception header logged');
		$this->assertEquals( $content[1][0], 'error', 'Stack trace has incorrect error level');
		$this->assertEquals( $content[1][1], 'lvl_error', 'Stack trace has incorrect log prefix');
		// TODO: Fix Me!
		/**$this->assertTrue( 0<preg_match(
				'%^\#0 .*test/tools/php/tapunit\.php\(.*\): Log_Test->test_log_exception\(\)$%',
				$content[1][2]
				), 'Stack trace has incorrect stack trace (first line), got: '.$content[1][2]); */

	}

	/* Test invoking debug object as a function to log as debug message */
	function test_debug_invoke() {
		$log = op5log::instance('lvl_debug');
		$log('message');
		op5log::writeback();

		$content = self::getOutputNS('lvl_debug');
		$this->assertEquals( $content, array(
				array('debug', 'lvl_debug', 'message')
		), 'Output doesn\'t match' );
	}
}
