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
		if( $id !== false )
			throw new Exception( 'Contact id isn\'t supported, use name instead' );
		if( $username === false )
			$username = Auth::instance()->get_user()->username;
		
		$ls = Livestatus::instance();
		$results = $ls->getContacts( array( 'filter' => array( 'name' => $username ) ) );
		
		if( count( $results ) != 1 )
			return false;
		
		return (object)$results[0];
	}

	/**
	 * Fetch list of contact names
	 */
	public static function get_contact_names() {
		return Livestatus::instance()->getContacts(array('columns' => 'name'));
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
