<?php defined('SYSPATH') OR die('No direct access allowed.');

class Current_status_Model extends Model {
	const HOST_UP =  0;
	const SERVICE_OK = 0;
	const SERVICE_WARNING = 1;
	const SERVICE_CRITICAL = 2;
	const SERVICE_UNKNOWN =  3;
	const SERVICE_PENDING = -1;

	public $base_path = '';
	public function __construct()
	{
		parent::__construct();
		$this->base_path = Kohana::config('config.nagios_base_path');
	}

	/**
	 * Parse Nagios logfile to get current status
	 * Normally this funtion is used to parse status.log
	 * @@@FIXME: rewrite to use database once available
	 *
	 * @param 	str $file path to file to parse
	 * @return 	array
	 */
	public function get_nagios_status($file=false)
	{
		$status = array();
		$file = trim($file);
		$file = empty($file) ? $this->base_path.'/var/status.log' : $file;
		if ( file_exists($file) ) {
			$data = file($file);

			foreach ( $data as $line ){
				$line = rtrim($line);

			if ( preg_match('/^[A-Za-z]* \{/', $line) ){
				// section header
				$key = split(" ", $line);
				$index = $key[0];
			}
			if ( preg_match('/[a-z_A-Z]=/', $line) ) {
				$list = explode("=", trim($line));
				$key = $list[0];
				$val = implode("=", array_slice($list,1));
				switch($index) {
					case "info":
					case "program":
						$status["$index"]["$key"] = $val;
						break;
					case "hoststatus":
						if ( $key == "host_name" ){
							$status["hosts"][] = $val;
							$status["$val"] = array();
							$current_host = $val;
						} else
							$status["$current_host"]["$key"] = $val;
						break;

					case "servicestatus":
						switch($key) {
							case "host_name":
							$current_host = $val;
							break;

							case "service_description":
								$current_svc = $val;
								$status["$current_host"]["services"][] = $val;
								$status["$current_host"]["$val"] = array();
								break;

							default:
								$status["$current_host"]["$current_svc"]["$key"] = $val;
						}
						break;
					}
				} else
					if ( preg_match("/\}$/", $line) || preg_match("/^$/", $line) )
						$current_host = $current_svc = NULL;
			} // Foreach
			return($status);

		} // if (Status Log Exists)
		else {
			return(NULL);
		}
	}

	/**
	*	@name	get_network_health
	*	@desc	Calculate current network healt for use in TAC
	*	@return hash array ('host_status' => <value in percentage>, 'service_status' => <value in percentage>)
	*/
	public function get_network_health()
	{
		$config = $this->get_nagios_status();
		if (empty($config) || !array_key_exists('hosts', $config)) {
			return false;
		}
		$up = 0;
		$total = 0;
		foreach ( $config['hosts'] as $host ){
			$total++;
			if ($config[$host]['current_state'] == self::HOST_UP )
				$up++;
			$services[$host] = $config[$host]['services'];
		}
		$host_status = number_format(($up/$total)*100, 1);
		#printf("Host Health: %13s", $host_status);

		$service_total = 0;
		$service_ok = 0;
		foreach ($services as $host => $data) {
			foreach ($data as $service_desc) {
				if ($config[$host][$service_desc]['current_state'] == self::SERVICE_OK ) {
					$service_ok+=2;
				}
		        if ($config[$host][$service_desc]['current_state'] == self::SERVICE_WARNING || $config[$host][$service_desc]['current_state'] == self::SERVICE_UNKNOWN ) {
					$service_ok++;
				}
				if ($config[$host][$service_desc]['current_state'] != self::SERVICE_PENDING ) {
					$service_total+=2;
				}
			}
		}

		#print("Services OK - $service_ok / Total Services - $service_total\n");
		$service_status = (floor(($service_ok/$service_total)*1000)/10);
		return array('host_status' => $host_status, 'service_status' => $service_status);
	}
}