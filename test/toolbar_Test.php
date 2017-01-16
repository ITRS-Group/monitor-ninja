<?php
/**
 * Toolbar tests
 *
 * @package NINJA
 * @author  op5
 * @license GPL
 */
class Toolbar_Test extends PHPUnit_Framework_TestCase {

	public function test_toolbar_title_escaping () {
		$toolbar = new Toolbar_Controller("<script>alert(1)</script>");
		$this->assertEquals("<div class=\"main-toolbar-title\">&lt;script&gt;alert(1)&lt;/script&gt;</div>", $toolbar->get_title_html());
	}

	public function test_toolbar_subtitle_escaping () {
		$toolbar = new Toolbar_Controller("Title", "<script>alert(1)</script>");
		$this->assertEquals("<div class=\"main-toolbar-subtitle\">&lt;script&gt;alert(1)&lt;/script&gt;</div>", $toolbar->get_subtitle_html());
	}

}
