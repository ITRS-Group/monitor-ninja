<?php

/**
 * Livestatus communication handler
 */
class Livestatus
{
	static $auth = false;
	private $sock = false;
	private static $instance = false;
	/**
	 * @throws Api_Error_Response
	 * @return filehandle, Livestatus socket
	 */
	private function open_livestatus_socket()
	{
		$sock = @fsockopen('unix:///opt/monitor/var/rw/live', null, $errno, $errstr);
		if ($errno) {
			$i = 0;
			while ($i < 5) {
				sleep(1);
				$sock = @fsockopen('unix:///opt/monitor/var/rw/live', null, $errno, $errstr);
				if (!$errno) {
					break;
				}
			}
		}
		if ($errno)
			throw new Exception("Couldn't open livestatus socket: " . $errstr);
		return $sock;
	}

	/**
	 * @param $socket filehandle Livestatus socket
	 * @param $len int
	 * @return string
	 */
	private function read_socket($socket, $len)
	{
		$offset = 0;
		$res = '';

		while ($offset < $len) {
			$data = fread($socket, $len - $offset);
			if (empty($data))
				break;
			$res .= $data;
			$offset += strlen($data);
		}
		return $res;
	}

	public function instance() {
		if (self::$instance === false)
			return new Livestatus();
		else
			return $ls;
	}

	private function __construct() {
		$this->auth = new Nagios_auth_Model();
		$this->sock = $this->open_livestatus_socket();
	}

	public function __destruct() {
		fclose($this->sock);
	}

	public function query($query) {
		$query = trim($query); // keep track of them newlines
		if (!((strpos($query, 'GET host') === 0 && $this->auth->view_hosts_root ) ||
			(strpos($query, 'GET service') === 0 && ($this->auth->view_hosts_root || $this->auth->view_services_root))))
			$query .= "\nAuthUser: $username";
		$query .= "\nOutputFormat: json\nKeepAlive: on\nResponseHeader: fixed16\n\n";
		@fwrite($this->sock, $query);
		$head = $this->read_socket($this->sock, 16);
		if (empty($head)) {
			throw new Exception("Couldn't read livestatus header");
		}
		$out = $this->read_socket($this->sock, substr($head, 4, 15));
		if (substr($head, 0, 3) !== '200')
			throw new Exception("Invalid request $head: $out");
		else if (empty($out))
			throw new Exception("No output");

		return json_decode($out);
	}
}
