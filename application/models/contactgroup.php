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
		$db = Database::instance();
		$user = Auth::instance()->get_user()->username;
		$view_hosts_root = false;
		$sql_auth_str = false;

		$auth = new Nagios_auth_Model();
		if ($auth->view_hosts_root) {
			$view_hosts_root = true;
		} else {
			$sql_auth_str = " c.contact_name = ".$db->escape($user)." AND ";
		}

		if (empty($service)) {
			$sql = "SELECT DISTINCT ".
				"cg.contactgroup_name, ".
				"h.host_name ".
			"FROM ".
				"host h, ".
				"contact c, ".
				"contactgroup cg, ".
				"contact_contactgroup ccg,".
				"host_contactgroup hcg ".
			"WHERE ".
				"h.id = hcg.host AND ".
				"hcg.contactgroup = cg.id AND ".
				"ccg.contactgroup = cg.id AND ".
				"ccg.contact = c.id AND ". $sql_auth_str .
				"hcg.contactgroup = cg.id AND ".
				"h.host_name = ".$db->escape($host);
		} else {
			if ($view_hosts_root === false) {
				if (!$auth->view_services_root) {
					$sql_auth_str = " c.contact_name = ".$db->escape($user)." AND ";
				} else {
					$sql_auth_str = false;
				}
			}
			$sql = "SELECT DISTINCT ".
				"cg.contactgroup_name, ".
				"s.service_description ".
			"FROM ".
				"service s, ".
				"contact c, ".
				"contactgroup cg, ".
				"contact_contactgroup ccg, ".
				"service_contactgroup scg ".
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
		$db = Database::instance();
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

	/**
	 * Given an escalation, return all contactgroups, or false on error or empty
	 * @param $type 'host' or 'service'
	 * @param $id The escalation id
	 */
	public function get_contactgroups_from_escalation($type = 'host', $id = false) {
		$sql = false;
		$db = Database::instance();
		if (empty($id)){
			return false;
		} else {
			$sql = "SELECT cg.contactgroup_name ".
					 "FROM ".$type."escalation_contactgroup hcg, ".$type."escalation he, contactgroup cg ".
					 "WHERE he.id = '".$id."' AND he.id = hcg.".$type."escalation AND hcg.contactgroup = cg.id";
		}
		if (empty($sql)) {
			return false;
		}

		$result = $db->query($sql);
		return $result->count() ? $result: false;
	}
}
