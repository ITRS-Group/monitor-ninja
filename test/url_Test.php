<?php

class Url_Test extends PHPUnit_Framework_TestCase {

	public function url_provider() {
		return array(
			array("/dokuwiki/hello", "/dokuwiki/hello"),
			// this is the cheap version of checking whether or not we
			// are running the test against an installed version of
			// Monitor, or from the checked out code (meaning Ninja)
			array("dokuwiki/hello", "/(ninja|monitor)/index.php/dokuwiki/hello"),

			//To make sure that the cmd.php class returns the proper url, related to: MON-9817
			array("configuration/configure/hostgroup/test:1", "/monitor/index.php/configuration/configure/hostgroup/test:1")
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

	public function site_url_provider() {
		return array(
			array("configuration/configure/hostgroup/test:1", "/monitor/index.php/configuration/configure/hostgroup/test:1")
		);
	}

	/**
	 * @dataProvider site_url_provider
	 * @group MON-9817
	 */
	public function test_site_url($uri, $expected) {
		$url = url::site($uri);
		$this->assertRegExp('~^'.$expected.'$~', $url,
			"The url helper treated the input incorrectly. When ".
			"debugging, look for how it handles leading '/'."
		);
	}

}
