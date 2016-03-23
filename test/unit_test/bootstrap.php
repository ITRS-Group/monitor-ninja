<?php
define('SKIP_KOHANA', true);

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

set_include_path(realpath(__DIR__.'/../../src/') . PATH_SEPARATOR . get_include_path());

require_once(__DIR__.'/../../index.php');

