<?php

class ListviewTest extends PHPUnit_Framework_TestCase {

	public function querylink_provider() {
		return array(
			array(
				'[hosts] not (in "Windows servers" or in "Linux servers")',
				"~/(ninja|monitor)/index.php/listview/?q=%5Bhosts%5D%20not%20(in%20%22Windows%20servers%22%20or%20in%20%22Linux%20servers%22)~"
			)
		);
	}

	/**
	 * @group MON-9890
	 * @dataProvider querylink_provider
	 */
	public function test_querylink($query, $expected_link) {
		$this->assertRegExp($expected_link, listview::querylink($query));
	}

}
