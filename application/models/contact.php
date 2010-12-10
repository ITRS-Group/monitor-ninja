<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle contact data
 */
class Contact_Model extends Model
{
	/**
	*	Fetch contact information
	*/
	public function get_contact($id = false, $username=false)
	{
		$sql = false;
		$db = new Database();
		if (empty($id) && empty($username)) {
			$sql = "SELECT * FROM contact WHERE contact_name = " .
				$db->escape(Auth::instance()->get_user()->username);
		} else {
			if (!empty($id)) {
				$sql = "SELECT * FROM contact WHERE id = " . (int)($id);
			} elseif (!empty($username)) {
				$sql = "SELECT * FROM contact WHERE contact_name = " .
					$db->escape($username);
			}
		}
		if (empty($sql)) {
			return false;
		}

		$result = $db->query($sql);
		return $result->count() ? $result: false;
	}

	public function get_contacts_from_escalation($type = 'host', $id = false) {
		$sql = false;
		$db = new Database();
		if (empty($id)){
			return false;
		} else {
			$sql1 = "SELECT c.contact_name
					 FROM ".$type."escalation_contact as hc, ".$type."escalation as he, contact as c
					 WHERE he.id = '".$id."' AND he.id = hc.".$type."escalation AND hc.contact = c.id";

			$sql2 = "SELECT cg.contactgroup_name
					 FROM ".$type."escalation_contactgroup as hcg, ".$type."escalation as he, contactgroup as cg
					 WHERE he.id = '".$id."' AND he.id = hcg.".$type."escalation AND hcg.contactgroup = cg.id";

			$sql = $sql1." UNION ".$sql2;

		}
		if (empty($sql)) {
			return false;
		}

		$result = $db->query($sql);
		return $result->count() ? $result: false;
	}

}
