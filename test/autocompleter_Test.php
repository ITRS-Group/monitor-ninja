<?php

class Autocompleter_Test extends PHPUnit_Framework_TestCase {
	private $mock_data_path;
	private $mock_log;

	protected function setUp() {
		op5objstore::instance()->mock_add('op5config', new MockConfig(array(
			'auth' => array(
				'common' => array(
					'default_auth' => 'mydefault',
					'session_key'  => false
				),
				'mydefault'  => array(
					'driver' => 'Default'
				)
			)
		)));
		$this->mock_log = new MockLog($print_to_stdout = false);
		op5objstore::instance()->mock_add('op5Log', $this->mock_log);

		Auth::instance(array('session_key' => false))->force_user(
			new User_AlwaysAuth_Model()
		);
		$this->mock_data_path = false;
	}

	protected function tearDown() {
		op5objstore::instance()->mock_clear();
		if($this->mock_data_path !== false) {
			unlink($this->mock_data_path);
		}
	}

	private function mock_data($tables, $file) {
		$this->mock_data_path = __DIR__ . '/' . $file . '.json';
		file_put_contents($this->mock_data_path, json_encode($tables));
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add(
				$driver,
				new ORMDriverNative($tables, $this->mock_data_path, $driver)
			);
		}
	}

	public function data_for_test_can_match_on_multiple_tables() {
		$autocompleter_backend = array(
			'hosts' => array(
				array(
					'display_column' => 'name',
					'query' => '[hosts] name~~"%s"'
				),
				array(
					'display_column' => 'name',
					'query' => '[hosts] alias~~"%s"'
				)
			),
			'services' => array(
				array(
					'display_column' => 'description',
					'query' => '[services] description~~"%s"'
				)
			)
		);

		return array(
			'match a single host by alias' => array(
				$autocompleter_backend,
				'Fred',
				array('hosts'),
				array(
					array(
						'name' => 'Bob Arctor',
						'table' => 'hosts',
						'key' => 'Bob Arctor',
					)
				)
			),
			'match a single host by host name' => array(
				$autocompleter_backend,
				'Bob',
				array('hosts'),
				array(
					array(
						'name' => 'Bob Arctor',
						'table' => 'hosts',
						'key' => 'Bob Arctor',
					)
				)
			),
			'match a single service' => array(
				$autocompleter_backend,
				'Donna',
				array('services'),
				array(
					array(
						'name' => 'Bob Arctor / Donna Hawthorne',
						'table' => 'services',
						'key' => 'Bob Arctor;Donna Hawthorne',
					)
				)
			),
			'match both a single host and a single service' => array(
				$autocompleter_backend,
				'Fred',
				array('hosts', 'services'),
				array(
					array(
						'name' => 'Bob Arctor',
						'table' => 'hosts',
						'key' => 'Bob Arctor'
					),
					array(
						'name' => 'Flintstone / Fred',
						'table' => 'services',
						'key' => 'Flintstone;Fred'
					)
				)
			),
			'match multiple hosts' => array(
				$autocompleter_backend,
				'Murphy',
				array('hosts'),
				array(
					array(
						'name' => 'Alex Murphy',
						'table' => 'hosts',
						'key' => 'Alex Murphy',
					),
					array(
						'name' => 'Eddie Murphy',
						'table' => 'hosts',
						'key' => 'Eddie Murphy',
					),
				)
			),
			'matches can be case insensitive' => array(
				$autocompleter_backend,
				'bOb aRCtoR',
				array('hosts'),
				array(
					array(
						'name' => 'Bob Arctor',
						'table' => 'hosts',
						'key' => 'Bob Arctor',
					),
				)
			)
		);
	}

	/**
	 * @dataProvider data_for_test_can_match_on_multiple_tables
	 * @group MON-9409
	 */
	public function test_can_autocomplete($autocompleter_backend,
		$search_term, $search_tables, $expected_result) {

		// need to mock_data() in this function because the
		// @dataProvider is only run once, while this function
		// is run multiple times (and teardown is run after
		// each time this function is run)
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Bob Arctor',
						'alias' => 'Fred'
					),
					array(
						'name' => 'Flintstone',
						'alias' => 'Flintstone'
					),
					array(
						'name' => 'Alex Murphy',
						'alias' => 'Alex Murphy'
					),
					array(
						'name' => 'Eddie Murphy',
						'alias' => 'Eddie Murphy'
					),
				),
				'services' => array(
					array(
						'host' => array(
							'name' => 'Bob Arctor',
						),
						'description' => 'Donna Hawthorne'
					),
					array(
						'host' => array(
							'name' => 'Flintstone',
						),
						'description' => 'Fred'
					),
				),
			),
			'ORMDriverMySQL default' => array(
				'saved_filters' => array(
					array(
						'id' => 1,
						'filter_table' => 'hosts',
						'filter' => '[hosts] name = "Bob" or name = "Alex"',
						'filter_description' => 'Bob and Alex'
					)
				),
			)
		), __FUNCTION__);

		$ac = new Autocompleter($autocompleter_backend);

		$calculated_result = $ac->query($search_term, $search_tables);
		$this->assertInternalType('array', $calculated_result);
		$this->assertSame($expected_result, $calculated_result);
	}

	/**
	 * Integration test, depends on data found in Ninja (search for
	 * autocomplete.php manifest files)
	 *
	 * @group MON-9409
	 * @group integration
	 */
	public function test_can_use_installed_manifests_for_autocompletion_information() {
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Tom Hansen',
					),
					array(
						'name' => 'Summer Finn',
					),
				)
			),
		), __FUNCTION__);

		$ac = Autocompleter::from_manifests();
		$calculated_result = $ac->query('tom', array('hosts'));
		$expected_result = array(
			array(
				'name' => 'Tom Hansen',
				'table' => 'hosts',
				'key' => 'Tom Hansen'
			)
		);
		$this->assertSame($expected_result, $calculated_result);
	}

	/**
	 * @group MON-9409
	 */
	public function test_logs_when_searching_for_unspecified_table() {
		$ac = new Autocompleter(array(
			'an_unrelated_table' => array(
				array(
					'display_column' => 'height',
					'query' => '[ufo] height = "%s"'
				)

			)
		));

		$calculated_result = $ac->query('something else', array('table_that_is_not_specified'));

		$this->assertSame(array(), $calculated_result);
		$this->assertSame(array(
			'namespace' => 'ninja',
			'level' => 'error',
			'message' => "Tried to search for 'something else' on tables table_that_is_not_specified but there are no settings registered for that table"
		), $this->mock_log->dequeue_message());
		// just make sure that there was nothing else logged
		$this->assertSame(null, $this->mock_log->dequeue_message());
	}

	/**
	 * @expectedException AutocompleterException
	 * @expectedExceptionMessage Wrong format of $table_information, each $table_spec must have a display_column
	 * @group MON-9409
	 */
	public function test_throws_exception_if_table_spec_is_missing_display_column() {
		$table_spec = array(
			'hosts' => array(
				array(
					'query' => '[hosts] name = "%s"'
				)
			)
		);
		new Autocompleter($table_spec);
	}

	/**
	 * @expectedException AutocompleterException
	 * @expectedExceptionMessage Wrong format of $table_information, each $table_spec must have a query
	 * @group MON-9409
	 */
	public function test_throws_exception_if_table_spec_is_missing_query() {
		$table_spec = array(
			'hosts' => array(
				array(
					'display_column' => 'name'
				)
			)
		);
		new Autocompleter($table_spec);
	}

	public function data_with_wrong_string_placeholders() {
		return array(
			'no placeholder' => array('lala'),
			'two placeholders' => array('%s %s'),
			'placeholder with space' => array('% s')
		);
	}

	/**
	 * @dataProvider data_with_wrong_string_placeholders
	 * @expectedException AutocompleterException
	 * @expectedExceptionMessage Wrong format of $table_information, each $table_spec must have a query with exactly one %s in it
	 * @group MON-9409
	 */
	public function test_throws_exception_if_table_spec_query_has_wrong_amounts_of_string_placeholders($query) {
		$table_spec = array(
			'hosts' => array(
				array(
					'display_column' => 'name',
					'query' => $query
				)
			)
		);
		new Autocompleter($table_spec);
	}
}
