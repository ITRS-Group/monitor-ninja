<?php

class Url_Test extends PHPUnit_Framework_TestCase {

	public function url_provider() {
		return array(
			array("/dokuwiki/hello", "/dokuwiki/hello"),
			// this is the cheap version of checking whether or not we
			// are running the test against an installed version of
			// Monitor, or from the checked out code (meaning Ninja)
			array("dokuwiki/hello", "/(ninja|monitor)/index.php/dokuwiki/hello")
		);
	}

	/**
	 * @dataProvider url_provider
	 * @group MON-9648
	 */
	public function test_redirect($input, $expected) {
		$redirect = url::_redirect($input);
		$this->assertRegExp('~^'.$expected.'$~', $redirect["url"],
			"The url helper treated the input incorrectly. When ".
			"debugging, look for how it handles leading '/'."
		);
	}
}
