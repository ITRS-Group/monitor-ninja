<?php
/**
 * An ORM driver which is backed by YAML
 */
class ORMDriverYAML extends ORMDriverNative {

	/**
	 * The YAML files have different formats and as such we need to
	 * standardize the format when loading the data.
	 *
	 * @param array $data
	 * @param string $table
	 * @param array $structure
	 * @return array
	 */
	private function unmarshall_yaml_data ($data, $table, $structure) {
		$storage = array($table => array());
		if ($table === 'usergroups') {
			/* Usergroups are stored as "name": [rights],
			   which isn't a good format to put straight into the ORM,
			   unpack the rights into an array under a "rights" key. */
			foreach ($data as $name => $rights) {
				$storage[$table][$name] = array(
					"groupname" => $name,
					"rights" => $rights
				);
			}
		} elseif ($table === 'authmodules') {
			/* "common" auth settings are saved in the same
			   file as auth modules, for some reason... Keep it separate when loading
			   authmodules. I addition not all modules have the same
			   properties so set a 'properties' key to dict for storage. */
			$storage['common'] = $data['common'];
			unset($data['common']);
			foreach ($data as $name => $props) {
				$storage[$table][$name] = array(
					"modulename" => $name,
					"properties" => $props
				);
			}
		} else {
			$storage[$table] = $data;
		}
		return $storage;
	}

	/**
	 * The YAML files have different formats and as such we need to
	 * standardize the format when saving the data.
	 *
	 * @param string $table
	 * @param array $structure
	 * @return array
	 */
	private function marshall_yaml_data ($table, $structure) {
		$data = array();
		if ($table === 'usergroups') {
			foreach ($this->storage[$table] as $group) {
				$data[$group['groupname']] = $group['rights'];
			}
		} elseif ($table === 'authmodules') {
			foreach ($this->storage[$table] as $module) {
				$data[$module['modulename']] = $module['properties'];
			}
			/* Return common to authmodule YAML */
			$data['common'] = $this->storage['common'];
		} else {
			$data = $this->storage;
		}
		return $data;
	}

	public function count($table, $structure, $filter) {
		$data = Op5Config::instance()->getConfig($structure['table']);
		$this->storage = $this->unmarshall_yaml_data($data, $table, $structure);
		return parent::count($table, $structure, $filter);
	}

	public function it($table, $structure, $filter, $columns, $order=array(), $limit=false, $offset=false) {
		$data = Op5Config::instance()->getConfig($structure['table']);
		$this->storage = $this->unmarshall_yaml_data($data, $table, $structure);
		return parent::it($table, $structure, $filter, $columns, $order, $limit, $offset);
	}

	public function delete($table, $structure, $filter) {

		$data = Op5Config::instance()->getConfig($structure['table']);
		$this->storage = $this->unmarshall_yaml_data($data, $table, $structure);
		$visitor = new NativeFilterBuilderVisitor();

		if (isset($this->storage[$table]) && count($this->storage[$table]) > 0) {
			foreach ($this->storage[$table] as $ix => $row) {
				if ($filter->visit($visitor, $row)) {
					unset($this->storage[$table][$ix]);
				}
			}
		}

		$data = $this->marshall_yaml_data($table, $structure);
		Op5Config::instance()->setConfig($structure['table'], $data[$table]);

	}

	public function update($table, $structure, $filter, $values) {

		$data = Op5Config::instance()->getConfig($structure['table']);
		$this->storage = $this->unmarshall_yaml_data($data, $table, $structure);
		$visitor = new NativeFilterBuilderVisitor();

		if (isset($this->storage[$table]) && count($this->storage[$table]) > 0) {
			foreach ($this->storage[$table] as $ix => $row) {
				if ($filter->visit($visitor, $row)) {
					$this->storage[$table][$ix] = array_merge($this->storage[$table][$ix], $values);
				}
			}
		}

		$data = $this->marshall_yaml_data($table, $structure);
		Op5Config::instance()->setConfig($structure['table'], $data[$table]);

	}

	public function insert_single($table, $structure, $values) {

                $data = Op5Config::instance()->getConfig($structure['table']);
                $this->storage = $this->unmarshall_yaml_data($data, $table, $structure);

		$key = $structure['key'][0];
		if (isset($values[$key])) {
			if (isset($this->storage[$table][$values[$key]])) {
				throw new ORMDriverException('An object with this identifier already exists');
			}
			$this->storage[$table][$values[$key]] = $values;
		} else {
			throw new ORMDriverException('Cannot create object without an identifier');
		}

                $data = $this->marshall_yaml_data($table, $structure);
                Op5Config::instance()->setConfig($structure['table'], $data[$table]);

	}

}
