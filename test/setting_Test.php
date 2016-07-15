<?php

class Setting_Test extends PHPUnit_Framework_TestCase {

	/* Typename of the setting used in tests */
	private static $type = 'dora.the.explorer';

	/* Typename of the default setting used in tests */
	private static $default_type = 'this.is.ma.default';

	public static function tearDownAfterClass () {

		$set = SettingPool_Model::all()
			->reduce_by('type', self::$type, '=');

		foreach ($set as $setting) {
			$setting->delete();
		}

		$set = SettingPool_Model::all()
			->reduce_by('type', self::$default_type, '=');

		foreach ($set as $setting) {
			$setting->delete();
		}

	}

	public function test_create_setting () {

		$page = '*';
		$username = 'guggenheim';
		$value = '143';

		$this->assertTrue(Ninja_Setting_Model::save_page_setting(self::$type, $page, $value, $username));

	}

	public function test_read_setting () {

		$page = '*';
		$username = 'guggenheim';
		$value = '143';

		$result = Ninja_Setting_Model::fetch_user_page_setting(self::$type, $page, $username);
		$this->assertEquals($result->setting, $value);

	}

	public function test_create_default_setting () {

		$page = '*';
		$value = 'my default setting';

		/* Don't go through Ninja_Setting_Model to create default
		 * setting as this will prompt Auth calls to get which user it
		 * will apply the setting for */
		$setting = new Setting_Model();
		$setting->set_setting($value);
		$setting->set_page($page);
		$setting->set_type(self::$default_type);
		$setting->save();

	}

	public function test_read_default_setting () {

		$page = '*';
		$value = 'my default setting';

		// true to fetch default setting
		$result = Ninja_Setting_Model::fetch_page_setting(self::$default_type, $page, true);
		$this->assertEquals($result->setting, $value);

	}

}
