<?php
class performance_data_Test extends PHPUnit_Framework_TestCase {
	/**
	 * Test performance data processing.
	 *
	 * This doesn't actually test the ORM, but a helper entirely used by ORM
	 */
	public function test_performance_data_conversion() {
		$perf_data_str = "datasource=31 'Data Saucer'=32c;;;32;34 dattenSaucen=93%;~32:2;~3: invalid 'dd\'invalid contains singelquote'=13Gb;32 'dd\backslash'=13Gb;32";
		$expect = array (
			'datasource' => array (
				'value' => 31.0
			),
			'Data Saucer' => array (
				'value' => 32.0,
				'unit' => 'c',
				'min' => 32.0,
				'max' => 34.0
			),
			'dattenSaucen' => array (
				'value' => 93.0,
				'unit' => '%',
				'warn' => '~32:2',
				'crit' => '~3:',
				'min' => 0.0,
				'max' => 100.0
			),
			'dd\\backslash' => array (
				'value' => 13.0,
				'unit' => 'Gb',
				'warn' => '32'
			)
		);

		$perf_data = performance_data::process_performance_data($perf_data_str);
		$this->assertSame($perf_data, $expect);
	}

	/**
	 * Test some strange nonescaped behaviour, according to bug 8781
	 *
	 * According to monitoring plugins, data source names isn't allowed to
	 * contain
	 * single quotes, equal-signs. To allow for spaces, the string needs to be
	 * single quoted, but shouldn't be escaped.
	 */
	public function test_no_unescape() {
		$str = "'C:\ Used Space'=15.71Gb;21.17;23.66;0.00;24.90";
		$pd = performance_data::process_performance_data($str);
		$this->assertSame(
			array (
				'C:\\ Used Space' => Array (
					'value' => 15.71,
					'unit' => 'Gb',
					'warn' => '21.17',
					'crit' => '23.66',
					'min' => 0.0,
					'max' => 24.9
				)
			), $pd);

		$str = "'C:\'=15.71Gb;21.17;23.66;0.00;24.90";
		$pd = performance_data::process_performance_data($str);
		$this->assertSame(
			array (
				'C:\\' => Array (
					'value' => 15.71,
					'unit' => 'Gb',
					'warn' => '21.17',
					'crit' => '23.66',
					'min' => 0.0,
					'max' => 24.9
				)
			), $pd);

		$str = "'C:\ test\lol\'=15.71Gb;21.17;23.66;0.00;24.90";
		$pd = performance_data::process_performance_data($str);
		$this->assertSame(
			array (
				'C:\\ test\\lol\\' => Array (
					'value' => 15.71,
					'unit' => 'Gb',
					'warn' => '21.17',
					'crit' => '23.66',
					'min' => 0.0,
					'max' => 24.9
				)
			), $pd);

		$str = "'C:\ test\lol'=15.71Gb;21.17;23.66;0.00;24.90";
		$pd = performance_data::process_performance_data($str);
		$this->assertSame(
			array (
				'C:\\ test\\lol' => Array (
					'value' => 15.71,
					'unit' => 'Gb',
					'warn' => '21.17',
					'crit' => '23.66',
					'min' => 0.0,
					'max' => 24.9
				)
			), $pd);
	}
}