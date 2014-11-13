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
	public function set_flash($keys, $val = FALSE) {}
	public function keep_flash($keys = NULL) {}
	public function expire_flash() {}
	public function get($key = FALSE, $default = FALSE) { return $default; }
	public function get_once($key, $default = FALSE) { return $default; }
	public function delete($keys) {}
}

require_once(__DIR__.'/../../index.php');

set_include_path(DOCROOT.'src/op5/' . PATH_SEPARATOR . get_include_path());