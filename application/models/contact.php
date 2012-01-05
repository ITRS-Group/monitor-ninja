<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Handle contact data
 */
class Contact_Model extends Model
{
	/**
	*	Fetch contact information
	*/
	public static function get_contact($id = false, $username=false)
	{
		$sql = false;
		$db = Database::instance();
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

	/**
	 * Return a database object of escalation contacts
	 * @param $type What type of escalation (host or service)
	 * @param $id The escalation ID
	 * @return Database object, or false on error or empty
	 */
	public function get_contacts_from_escalation($type = 'host', $id = false) {
		$sql = false;
		$db = Database::instance();
		if (empty($id)){
			return false;
		} else {
			$sql = "SELECT c.contact_name ".
				 "FROM ".$type."escalation_contact hc, ".$type."escalation he, contact c ".
				 "WHERE he.id = '".$id."' AND he.id = hc.".$type."escalation AND hc.contact = c.id";

		}
		if (empty($sql)) {
			return false;
		}

		$result = $db->query($sql);
		return $result->count() ? $result: false;
	}

}
