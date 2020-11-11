<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Input library.
 *
 * $Id: Input.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Input {

	// IP address of current user
	public $ip_address;

	// Input singleton
	protected static $instance;

	/**
	 * Retrieve a singleton instance of Input. This will always be the first
	 * created instance of this class.
	 *
	 * @return  object
	 */
	public static function instance()
	{
		if (self::$instance === NULL)
		{
			// Create a new instance
			return new Input;
		}

		return self::$instance;
	}

	/**
	 * Sanitizes global GET, POST and COOKIE data. Also takes care of
	 * magic_quotes and register_globals, if they have been enabled.
	 *
	 * @return  void
	 */
	public function __construct()
	{

		if (self::$instance === NULL)
		{
			// magic_quotes_gpc is enabled
			if (get_magic_quotes_gpc())
			{
				echo ('Disable magic_quotes_gpc! It is evil and deprecated: http://php.net/magic_quotes');
				exit(1);
			}

			// register_globals is enabled
			if (ini_get('register_globals'))
			{
				echo ('Disable register_globals! It is evil and deprecated: http://php.net/register_globals');
				exit(1);
			}

			// Create a singleton
			self::$instance = $this;
		}
	}

	/**
	 * Fetch an item from the $_GET array.
	 *
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @return  mixed
	 */
	public function get($key = array(), $default = NULL)
	{
		return $this->search_array($_GET, $key, $default);
	}

	/**
	 * Fetch an item from the $_POST array.
	 *
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @return  mixed
	 */
	public function post($key = array(), $default = NULL)
	{
		return $this->search_array($_POST, $key, $default);
	}

	/**
	 * Fetch an item from the $_COOKIE array.
	 *
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @return  mixed
	 */
	public function cookie($key = array(), $default = NULL)
	{
		return $this->search_array($_COOKIE, $key, $default);
	}

	/**
	 * Fetch an item from the $_SERVER array.
	 *
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @return  mixed
	 */
	public function server($key = array(), $default = NULL)
	{
		return $this->search_array($_SERVER, $key, $default);
	}

	/**
	 * Fetch an item from a global array.
	 *
	 * @param   array    array to search
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @return  mixed
	 */
	protected function search_array($array, $key, $default = NULL)
	{
		if ($key === array())
			return $array;

		if ( ! isset($array[$key]))
			return $default;

		// Get the value
		$value = $array[$key];

		return $value;
	}

	/**
	 * Fetch the IP Address.
	 *
	 * @return string
	 */
	public function ip_address()
	{
		if ($this->ip_address !== NULL)
			return $this->ip_address;

		if ($ip = $this->server('HTTP_CLIENT_IP'))
		{
			 $this->ip_address = $ip;
		}
		elseif ($ip = $this->server('REMOTE_ADDR'))
		{
			 $this->ip_address = $ip;
		}
		elseif ($ip = $this->server('HTTP_X_FORWARDED_FOR'))
		{
			 $this->ip_address = $ip;
		}

		if ($comma = strrpos($this->ip_address, ',') !== FALSE)
		{
			$this->ip_address = substr($this->ip_address, $comma + 1);
		}

		if ( ! valid::ip($this->ip_address))
		{
			// Use an empty IP
			$this->ip_address = '0.0.0.0';
		}

		return $this->ip_address;
	}

	/**
	 * This is a helper method. It enforces W3C specifications for allowed
	 * key name strings, to prevent malicious exploitation.
	 *
	 * @param   string  string to clean
	 * @return  string
	 */
	public function clean_input_keys($str)
	{
		$chars = PCRE_UNICODE_PROPERTIES ? '\pL' : 'a-zA-Z';

		if ( ! preg_match('#^['.$chars.'0-9:_.-]++$#uD', $str))
		{
			exit('Disallowed key characters in global data.');
		}

		return $str;
	}

} // End Input Class
