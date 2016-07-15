<?php
define('SKIP_KOHANA', true);

$ninja_base = realpath(__DIR__.'/..');
set_include_path($ninja_base . '/src' . PATH_SEPARATOR . get_include_path());

require_once ('op5/objstore.php');

/**
 * Dummy Session module for tests
 *
 * Since Session is autoloaded by Kohana, and always generates a cookie, which
 * works quite well in normal Kohana usage, we need to mock this up in unit
 * tests, since we get the problem otherwise of "Headers already sent". And
 * since we never use a session in the unit tests, it shouldn't bother us
 * either.
 */
class Session {
	public static function instance() { return new self(); }
	public function id() { return 'sessionid'; }
	public function create($vars = NULL) {}
	public function destroy() {}
	public function write_close() {}
	public function set($keys, $val = FALSE) {}
	public function get($key = FALSE, $default = FALSE) { return $default; }
	public function delete($keys) {}
}

// Make sure deprecated features are treated as such, see MON-9199
assert(putenv('OP5_NINJA_DEPRECATION_SHOULD_EXIT=1'));

/* Hardcode the path to the MockConfig, so we can boostrap isolated */
require_once ($ninja_base . '/modules/test/libraries/MockConfig.php');
op5objstore::instance()->mock_add('op5config', new MockConfig(array(
		'auth' => array (
			'common' => array (
				'default_auth' => 'mydefault',
				'session_key' => 'testkey'
			),
			'mydefault' => array (
				'driver' => 'Default'
			)
		)
	)));
require_once ($ninja_base . '/index.php');
op5objstore::instance()->mock_clear();

/*
 * If using this bootstrap from a module repository, uncomment those lines
 * below, and update $ninja_base above to the install path of ninja.
 */
// Kohana::remove_include_paths ( ':.*modules/my_module.*:' );
// Kohana::add_include_path ( realpath ( __DIR__ . '/../../my_module' ) . '/' );
