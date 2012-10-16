<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Valery Stukanov
 * Date: 9/7/11
 * Time: 1:19 PM
 */
class Nagvis_config_Model {
	static private $__instance = null;

	/**
	 * Get singleton instance
	 * @static
	 * @return Nagvis_config_Model
	 */
	static public function getInstance() {
		if(self::$__instance === NULL) {
			self::$__instance = new self();
		}
		return self::$__instance;
	}

	private $__data = null;
	protected $_path = null;

	private function __clone() {}
	private function __construct() {
		$this->_path = rtrim(Kohana::config('nagvis.nagvis_real_path'), '/') . '/etc/nagvis.ini.php';
		if(!file_exists($this->_path)) {
			throw new Kohana_Exception("Could not find nagvis conf file: {$this->_path}");
		}
		if(($this->__data = parse_ini_file($this->_path, true)) === FALSE) {
			throw new Kohana_Exception("Error in parsing nagvis configuration file!");
		}
	}

	/**
	 * Get value from nagvis config file.
	 * @param string $key string like <section>.<key>
	 * @param mixed $default
	 * @return null|mixed
	 */
	public function getValue($key, $default = NULL) {
		list($section, $key) = explode('.', $key);
		if(isset($this->__data[$section]) && isset($this->__data[$section][$key])) {
			return $this->__data[$section][$key];
		}
		return $default;
	}

}
