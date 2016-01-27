<?php
/**
 * Example Test.
 *
 * $Id$
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class LinkProviderTestClass_Controller {
	public function home () {}
	public function _hidden () {}
	private function priv () {}
}

class LinkProvider_Test extends PHPUnit_Framework_TestCase {

	public function setup () {
		$this->lp = new LinkProvider('https', '192.168.0.1', 'ninja/index.php');
	}

	public function test_instanced_class () {
		$lptc = new LinkProviderTestClass_Controller();
		$url = $this->lp->get_url($lptc, "home");
		$this->assertEquals("https://192.168.0.1/ninja/index.php/linkprovidertestclass/home", $url);
	}

	public function test_existing_class () {
		$url = $this->lp->get_url("LinkProviderTestClass_Controller", "home");
		$this->assertEquals("https://192.168.0.1/ninja/index.php/linkprovidertestclass/home", $url);
	}

	public function test_no_method_provided_default () {
		$url = $this->lp->get_url("linkprovidertestclass");
		$this->assertEquals("https://192.168.0.1/ninja/index.php/linkprovidertestclass", $url);
	}

	public function test_existing_class_by_slug () {
		$url = $this->lp->get_url("linkprovidertestclass", "home");
		$this->assertEquals("https://192.168.0.1/ninja/index.php/linkprovidertestclass/home", $url);
	}

	public function test_existing_class_with_parameters () {
		$url = $this->lp->get_url("linkprovidertestclass", "home", array('foo' => 'bar'));
		$this->assertEquals("https://192.168.0.1/ninja/index.php/linkprovidertestclass/home?foo=bar", $url);
	}

	public function test_existing_class_with_array_parameters () {
		$url = $this->lp->get_url("linkprovidertestclass", "home", array('foo' => array('a', 'b', 7 => 'c')));
		$this->assertEquals("https://192.168.0.1/ninja/index.php/linkprovidertestclass/home?foo%5B0%5D=a&foo%5B1%5D=b&foo%5B7%5D=c", $url);
	}

	/**
	 * @expectedException LinkProviderException
	 * @expectedExceptionMessage Cannot create URL to unknown controller 'flurpbar'
	 */
	public function test_nonexisting_class () {
		$url = $this->lp->get_url("flurpbar");
	}

	/**
	 * @expectedException LinkProviderException
	 * @expectedExceptionMessage Cannot create URL to unknown controller 'LinkProvider'
	 */
	public function test_non_controller_class () {
		$url = $this->lp->get_url("LinkProvider", "get_url");
	}

	/**
	 * @expectedException LinkProviderException
	 * @expectedExceptionMessage Cannot create URL to restricted method 'priv' on class 'linkprovidertestclass_Controller'
	 */
	public function test_private_method () {
		$url = $this->lp->get_url("linkprovidertestclass", "priv");
	}

	/**
	 * @expectedException LinkProviderException
	 * @expectedExceptionMessage Cannot create URL to restricted method '_hidden' on class 'linkprovidertestclass_Controller'
	 */
	public function test_kohana_convention_inaccessable_method () {
		$url = $this->lp->get_url("linkprovidertestclass", "_hidden");
	}

	/**
	 * @expectedException LinkProviderException
	 * @expectedExceptionMessage Cannot create URL to unknown method 'flurp' on class 'linkprovidertestclass_Controller'
	 */
	public function test_no_such_method () {
		$url = $this->lp->get_url("linkprovidertestclass", "flurp");
	}

}
