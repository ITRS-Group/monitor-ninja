<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle contactgroup data
 */
class Contactgroup_Model extends Model
{
	/**
	*	Fetch contactgroup(s) for host or service
	*/
	public static function get_contactgroup($host=false, $service=false)
	{
		if (empty($host)) {
			return false;
		}
		$host = trim($host);
		$service = trim($service);
		$db = new Database();
		$user = Auth::instance()->get_user()->username;
		$access = System_Model::nagios_access(Auth::instance()->get_user()->username);
		$view_hosts_root = false;
		$sql_auth_str = false;

		if (is_array($access) && in_array('authorized_for_all_hosts', $access)) {
			$view_hosts_root = true;
		} else {
			$sql_auth_str = " c.contact_name = ".$db->escape($user)." AND ";
		}

		if (empty($service)) {
			$sql = "SELECT DISTINCT ".
				"cg.contactgroup_name, ".
				"h.host_name ".
			"FROM ".
				"host as h, ".
				"contact AS c, ".
				"contactgroup AS cg, ".
				"contact_contactgroup AS ccg,".
				"host_contactgroup as hcg ".
			"WHERE ".
				"h.id = hcg.host AND ".
				"hcg.contactgroup = cg.id AND ".
				"ccg.contactgroup = cg.id AND ".
				"ccg.contact = c.id AND ". $sql_auth_str .
				"hcg.contactgroup = cg.id AND ".
				"h.host_name = ".$db->escape($host);
		} else {
			if ($view_hosts_root === false) {
				if (is_array($access) && !in_array('authorized_for_all_services', $access)) {
					$sql_auth_str = " c.contact_name = ".$db->escape($user)." AND ";
				} else {
					$sql_auth_str = false;
				}
			}
			$sql = "SELECT DISTINCT ".
				"cg.contactgroup_name, ".
				"s.service_description ".
			"FROM ".
				"service AS s, ".
				"contact AS c, ".
				"contactgroup AS cg, ".
				"contact_contactgroup AS ccg, ".
				"service_contactgroup AS scg ".
			"WHERE ".
				"s.id = scg.service AND ".
				"scg.contactgroup = cg.id AND ".
				"ccg.contactgroup = cg.id AND ".
				"ccg.contact = c.id AND ". $sql_auth_str .
				"s.service_description = ".$db->escape($service)." AND ".
				"s.host_name = ".$db->escape($host);
		}
		$result = $db->query($sql);
		return count($result) ? $result : false;
	}

	/**
	*	Static method to fetch all info on all members of a contactgroup
	*/
	public static function get_members($group=false)
	{
		if (empty($group)) {
			return false;
		}
		$db = new Database();
		$group = trim($group);
		$sql = "SELECT ".
				"c.* ".
			"FROM ".
				"contact c, ".
				"contactgroup cg, ".
				"contact_contactgroup ccg ".
			"WHERE ".
				"cg.contactgroup_name= ".$db->escape($group)." AND ".
				"ccg.contactgroup=cg.id AND ".
				"c.id=ccg.contact";
		$result = $db->query($sql);
		return count($result) ? $result : false;
	}
}
