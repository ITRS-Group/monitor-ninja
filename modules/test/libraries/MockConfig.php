<?php

/**
 * Mock replacement for op5Config class
 *
 * Takes a configuraiton as argument to the constructor. Useful to have inline
 * configurations in unit tests.
 *
 * Use it adding it to the mock interface of op5objstore:
 *
 * op5objstore::instance()->mock('op5config', new MockConfig($config));
 *
 */
class MockConfig {
	/**
	 * Active configuration, can be used to directly verify and modify config
	 * from the test interface
	 */
	public $config;

	/**
	 * Load the mock config
	 *
	 * @param $config Configuration array
	 */
	public function __construct($config) {
		$this->config = $config;
	}

	/**
	 * Get configuration for a given namespace
	 *
	 * @param $namespace Name of namespace
	 * @return array Configuration for the namespace
	 */
	public function getConfig($namespace) {
		if(!isset($this->config[$namespace])) {
			return array();
		}
		return $this->config[$namespace];
	}

	/**
	 * Set configuration for a given namespace
	 *
	 * @param $namespace Name of namespace
	 * @param $array the new configuration of the given namespace
	 */
	public function setConfig($namespace, $array) {
		$this->config[$namespace] = $array;
	}
}
