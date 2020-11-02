<?php

/**
 * Provides pointers to results from the backend, base on search input.
 */
class Autocompleter {

	private $logger;
	private $tables;

	/**
	 * @param $table_information array
	 * @throws AutocompleterException
	 */
	public function __construct(array $table_information) {
		// Perform some basic validation on the table information.
		// This also acts as a guide for the variable's format.
		foreach($table_information as $table => $table_specs) {
			foreach($table_specs as $table_spec) {
				if(!isset($table_spec['display_column'])) {
					throw new AutocompleterException('Wrong format of $table_information, each $table_spec must have a display_column: '.var_export($table_spec, true));
				}
				if(!isset($table_spec['query'])) {
					throw new AutocompleterException('Wrong format of $table_information, each $table_spec must have a query: '.var_export($table_spec, true));
				}
				$unused = null;
				$matches = preg_match_all('/%s/', $table_spec['query'], $unused);
				// thank you php, for all of your excellent interfaces
				$unused = null;
				if($matches !== 1) {
					throw new AutocompleterException('Wrong format of $table_information, each $table_spec must have a query with exactly one %s in it: '.var_export($table_spec, true));
				}
			}
		}
		$this->tables = $table_information;
		$this->logger = op5log::instance('ninja');
	}

	/**
	 * Alternative constructor, based on information retrieved in manifests.
	 *
	 * This makes loading relevant data easy as pie, and still having the
	 * implementation (@see query()) independant of external parts (well,
	 * almost -- the ORM is still a hard dependency).
	 *
	 * @return Autocompleter
	 */
	static public function from_manifests() {
		$autocomplete_queries = Module_Manifest_Model::get('autocomplete');
		return new self($autocomplete_queries);
	}

	/**
	 * Since we might have another backend later on, I'm making $tables
	 * optional.
	 *
	 * @param $search_term string
	 * @param $tables array = array()
	 * @throws ORMDriverException
	 * @return array
	 */
	public function query($search_term, array $tables = array()) {
		$results = array();
		foreach($tables as $table) {
			if(!isset($this->tables[$table]) ||
				!is_array($this->tables[$table])) {
				$this->logger->log('error', "Tried to search for '$search_term' on tables ".implode(', ', $tables)." but there are no settings registered for that table");
				continue;
			}
			foreach($this->tables[$table] as $query_info) {
				$query = sprintf($query_info['query'], html::specialchars($search_term));
				$set = ObjectPool_Model::get_by_query($query);
				if(!op5mayi::instance()->run($set->mayi_resource().":read.autocomplete")) {
					continue;
				}

				// We leave out specifying the exact columns,
				// because I can't get the native driver to properly
				// resolve host names on mocked services, which causes
				// me to not being able to test things. The overhead
				// of including all columns should be negligible for
				// the small amount of items in an autocomplete list.
				foreach ($set->it(false, array(), 15, 0) as $object) {

					// we assign the data to a unique key
					// in $results to avoid multiple results
					// for the same object, should it match
					// more than one of the queries for the
					// same table.
					$results[$table.$object->get_key()] = array(
						"name" => $object->get_readable_name(),
						"table" => $table,
						"key" => $object->get_key()
					);
				}
			}
		}
		return array_values($results);
	}
}
