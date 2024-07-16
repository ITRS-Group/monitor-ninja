<?php
use PHPUnit\TextUI\Configuration\Group;
use PHPUnit\Framework\Attributes\DataProvider;

class Autocompleter_Test extends \PHPUnit\Framework\TestCase {
	private $mock_log;

	protected function setUp() : void {
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
	}

	protected function tearDown() : void {
		op5objstore::instance()->mock_clear();
	}

	private function mock_data($tables) {
		foreach($tables as $driver => $tables) {
			op5objstore::instance()->mock_add(
				$driver,
				new ORMDriverNative($tables, null, $driver)
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

	#[DataProvider('data_for_test_can_match_on_multiple_tables')]
	#[Group('MON-9409')]
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
		));

		$ac = new Autocompleter($autocompleter_backend);

		$calculated_result = $ac->query($search_term, $search_tables);
		$this->assertIsArray($calculated_result);
		$this->assertSame($expected_result, $calculated_result);
	}

	/**
	 * Integration test, depends on data found in Ninja (search for
	 * autocomplete.php manifest files)
	 */
	#[Group('MON-9409')]
	#[Group('integration')]
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
		));

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
	 * Integration test, depends on data found in Ninja (search for
	 * autocomplete.php manifest files)
	 */
	#[Group('MON-9409')]
	#[Group('MON-9519')]
	#[Group('integration')]
	public function test_can_use_installed_manifests_for_matching_service_by_hostname() {
		$this->mock_data(array(
			'ORMDriverLS default' => array(
				'hosts' => array(
					array(
						'name' => 'Juan Rico',
					),
				),
				'services' => array(
					array(
						'host' => array(
							'name' => 'Juan Rico',
						),
						'description' => 'Carmencita Ibanez'
					),
				),
			),
		));

		$ac = Autocompleter::from_manifests();
		$calculated_result = $ac->query('juan', array('services'));
		$expected_result = array(
			array(
				'name' => 'Juan Rico / Carmencita Ibanez',
				'table' => 'services',
				'key' => 'Juan Rico;Carmencita Ibanez'
			)
		);
		$this->assertSame($expected_result, $calculated_result);
	}

	/**
	 * Integration test, depends on data found in Ninja (search for
	 * autocomplete.php manifest files)
	 */
	#[Group('MON-9539')]
	#[Group('integration')]
	public function test_can_use_installed_manifests_for_matching_user_by_name() {
		$this->mock_data(array(
			'ORMDriverYAML default' => array(
				'users' => array(
					array(
						'username' => 'Frank Frink',
					),
				),
			),
		));

		$ac = Autocompleter::from_manifests();
		$calculated_result = $ac->query('frank', array('users'));
		$expected_result = array(
			array(
				'name' => 'Frank Frink',
				'table' => 'users',
				'key' => 'Frank Frink'
			)
		);
		$this->assertSame($expected_result, $calculated_result);
	}

	/**
	 * Integration test, depends on data found in Ninja (search for
	 * autocomplete.php manifest files)
	 */
	#[Group('MON-9539')]
	#[Group('integration')]
	public function test_can_use_installed_manifests_for_matching_usergroup_by_name() {
		$this->mock_data(array(
			'ORMDriverYAML default' => array(
				'usergroups' => array(
					array(
						'groupname' => 'Nobusuke Tagomi',
					),
				),
			),
		));

		$ac = Autocompleter::from_manifests();
		$calculated_result = $ac->query('tagomi', array('usergroups'));
		$expected_result = array(
			array(
				'name' => 'Nobusuke Tagomi',
				'table' => 'usergroups',
				'key' => 'Nobusuke Tagomi'
			)
		);
		$this->assertSame($expected_result, $calculated_result);
	}

	#[Group('MON-9409')]
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

	#[Group('MON-9539')]
	public function test_autocompleter_requires_mayi_read_autocomplete_rights() {
		$this->mock_data(array(
			"ORMDriverYAML default" => array(
				"users" => array(
					array(
						"username" => "Daisy",
					),
				),
			),
		));
		$ac = Autocompleter::from_manifests();

		// let's prove the positive case first
		$calculated_result = $ac->query("daisy", array("users"));
		$expected_result = array(
			array(
				"name" => "Daisy",
				"table" => "users",
				"key" => "Daisy"
			)
		);
		$this->assertSame($expected_result, $calculated_result,
			"We should have found Daisy as part of the fixture"
		);


		$interesting_mayi_action = "monitor.system.users:read.autocomplete";
		$mayi_denied_fixture = array(
			$interesting_mayi_action => array(
				"message" => "Fogedaboudid"
			)
		);
		$mock_mayi = new MockMayI(array(
			"denied_actions" => $mayi_denied_fixture
		));
		op5objstore::instance()->mock_add("op5MayI", $mock_mayi);
		$this->assertSame(array(), $ac->query("daisy", array("users")),
			"The denying mayi right was not enough to hide Daisy ".
			"from the autocompleter backend. It should have been."
		);
	}

	#[Group('MON-9409')]
	public function test_throws_exception_if_table_spec_is_missing_display_column() {
		$this->expectException('AutocompleterException');
		$this->expectExceptionMessage('Wrong format of $table_information, each $table_spec must have a display_column');
		$table_spec = array(
			'hosts' => array(
				array(
					'query' => '[hosts] name = "%s"'
				)
			)
		);
		new Autocompleter($table_spec);
	}

	#[Group('MON-9409')]
	public function test_throws_exception_if_table_spec_is_missing_query() {
		$this->expectException('AutocompleterException');
		$this->expectExceptionMessage('Wrong format of $table_information, each $table_spec must have a query');
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

	#[DataProvider('data_with_wrong_string_placeholders')]
	#[Group('MON-9409')]
	public function test_throws_exception_if_table_spec_query_has_wrong_amounts_of_string_placeholders($query) {
		$this->expectException('AutocompleterException');
		$this->expectExceptionMessage('Wrong format of $table_information, each $table_spec must have a query with exactly one %s in it');
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
