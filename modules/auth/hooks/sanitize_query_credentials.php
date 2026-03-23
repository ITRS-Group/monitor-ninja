<?php
/**
 * Prevent authentication credentials in query strings from
 * being propagated into responses (HTML form actions, pagination, url::merge),
 * redirects (e.g. auth/login?uri=...), or follow-up requests.
 */
class sanitize_query_credentials_hooks {

	/** Parameter names to treat as secrets (case-insensitive match on key). */
	private static $sensitive_keys = array(
		'password',
		'passwd',
		'pwd',
		'passphrase',
		'user_password',
	);

	public function __construct() {
		Event::add('system.post_routing', array(__CLASS__, 'sanitize'));
	}

	/**
	 * Remove sensitive query parameters from a path or URL that may include ?query=...
	 *
	 * @param string $path_or_url Relative path like "search/lookup?query=x&password=y"
	 * @return string
	 */
	public static function strip_from_uri_string($path_or_url) {
		if ($path_or_url === null || $path_or_url === false || $path_or_url === '') {
			return $path_or_url;
		}
		$qpos = strpos($path_or_url, '?');
		if ($qpos === false) {
			return $path_or_url;
		}
		$path = substr($path_or_url, 0, $qpos);
		$query = substr($path_or_url, $qpos + 1);
		parse_str($query, $params);
		if (!is_array($params)) {
			return $path_or_url;
		}
		foreach (array_keys($params) as $k) {
			if (in_array(strtolower($k), self::$sensitive_keys, true)) {
				unset($params[$k]);
			}
		}
		$newq = http_build_query($params);
		return ($newq === '') ? $path : $path . '?' . $newq;
	}

	/**
	 * Runs at end of Router::setup; updates superglobals and Router statics.
	 */
	public static function sanitize() {
		if (PHP_SAPI === 'cli') {
			return;
		}

		$login_route = Kohana::config('routes.log_in_form');
		$login_parts = $login_route ? explode('/', $login_route, 2) : array('auth', 'login');
		$allow_get_credentials = (Kohana::config('auth.use_get_auth') === true
			&& strtolower(Router::$controller) === strtolower($login_parts[0])
			&& Router::$method === (isset($login_parts[1]) ? $login_parts[1] : 'index'));

		$removed = false;
		$clean = $_GET;

		/* Never allow credentials inside ?uri= (e.g. return URL from Base_Controller redirect). */
		if (isset($clean['uri'])) {
			$stripped_uri = self::strip_from_uri_string($clean['uri']);
			if ($stripped_uri !== $clean['uri']) {
				$clean['uri'] = $stripped_uri;
				$removed = true;
			}
		}

		if (!$allow_get_credentials) {
			foreach (array_keys($clean) as $key) {
				if (in_array(strtolower($key), self::$sensitive_keys, true)) {
					unset($clean[$key]);
					$removed = true;
				}
			}
		}

		if (!$removed) {
			return;
		}

		$_GET = $clean;
		foreach (array_keys($_REQUEST) as $rk) {
			if (in_array(strtolower($rk), self::$sensitive_keys, true)) {
				unset($_REQUEST[$rk]);
			}
		}

		$qs = http_build_query($_GET);
		$_SERVER['QUERY_STRING'] = $qs;
		Router::$query_string = ($qs === '') ? '' : '?' . $qs;
		Router::$complete_uri = Router::$current_uri . Router::$query_string;

		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && !headers_sent()) {
			url::redirect(url::site(Router::$current_uri . Router::$query_string), '302');
		}
	}
}

new sanitize_query_credentials_hooks();
