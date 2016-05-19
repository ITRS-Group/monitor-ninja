<?php

class LinkProviderException extends Exception {}

class LinkProvider {

	private $protocol;
	private $domain;
	private $indexpath;

	public function __construct ($protocol, $domain, $indexpath) {
		$this->protocol = $protocol;
		$this->domain = $domain;
		$this->indexpath = $indexpath;
	}

	/**
	 * Create a Koahana/ninja default LinkProvider
	 */
	public static function factory () {

		$protocol = Kohana::config('core.site_protocol');
		$domain = (string) Kohana::config('core.site_domain', TRUE);

		if ($domain[0] === '/') {
			$domain = rtrim($domain, "/");
		}

		if (PHP_SAPI !== 'cli') {
			$domain = $_SERVER['HTTP_HOST'] . $domain;
			if ($protocol == false) {
				$protocol = (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')
					? 'http' : 'https';
			}
		}

		$indexpath = Kohana::config('core.index_page');
		return (new LinkProvider($protocol, $domain, $indexpath));
	}

	/**
	 * Validate and normalize the controllers accessable name then returns both
	 * actual classname and controller slug.
	 *
	 * @throws LinkProviderException   When the validation fails
	 * @param mixed $controller        Controller name string or Controller instance
	 * @return array  		   Classname/Controller slug pair
	 */
	private function validate_controller ($controller) {

		$classname = $controller;
		if (is_object($controller)) {
			$classname = get_class($controller);
			$controller = $classname;
		} elseif (!preg_match("/\_[cC]ontroller$/", $controller))
			$classname = $controller . '_Controller';

		if (!class_exists($classname, true))
			throw new LinkProviderException("Cannot create URL to unknown controller '$controller'");

		$controller = strtolower(preg_replace("/\_[cC]ontroller$/", "", $controller));
		return array($classname, $controller);

	}

	/**
	 * Validate that the method exists and is accessable on the class $classname
	 *
	 * @throws LinkProviderException   When the validation fails
	 * @param string $classname        The classname to validate method on
	 * @param string $method           The method to validate
	 * @return string $method	   The method name
	 */
	private function validate_method ($classname, $method) {

		if (!$method) return '';
		if (!method_exists($classname, $method))
			throw new LinkProviderException("Cannot create URL to unknown method '$method' on class '$classname'");
		/* The only way besides actually instantiating
		 * the controller in order to find out if the
		 * method is publicly available */
		$reflection = new ReflectionMethod($classname, $method);

		/* Check both Kohana convention of underscored but public methods that are not
		 * accessable via requests, and that the method is public */
		if ($method[0] === '_' || !$reflection->isPublic())
			throw new LinkProviderException("Cannot create URL to restricted method '$method' on class '$classname'");

		return $method;

	}

	/**
	 * Returns a valid url to a controller invocation, valid in the sense
	 * that the controller exists, the method exists and are accessable.
	 *
	 * Note: Parameters are not validated, only appended
	 *
	 * @throws LinkProviderException   When validation fails
	 * @param mixed $controller	   Controller name or Controller instance
	 * @param string $method           A method name
	 * @param array $parameters        Parameters to add as query string
	 * @return string                  A valid URL
	 */
	public function get_url ($controller, $method = null, array $parameters = array()) {

		$query_string = (count($parameters) > 0)
			? "?" . http_build_query($parameters)
			: "";

		list($classname, $controller) = $this->validate_controller($controller);
		$url = "";
		$method = $this->validate_method($classname, $method);

		if ($method) {
			$url = sprintf("%s://%s/%s/%s/%s",
				$this->protocol,
				$this->domain,
				$this->indexpath,
				$controller,
				$method
			);
		} else {
			$url = sprintf("%s://%s/%s/%s",
				$this->protocol,
				$this->domain,
				$this->indexpath,
				$controller
			);
		}

		return $url . $query_string;

	}

}
