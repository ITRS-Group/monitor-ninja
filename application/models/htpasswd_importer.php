<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Model for importing htpasswd files to ninja
 */
class Htpasswd_importer_Model extends Ninja_Model
{
	private $htpasswd_file = "/opt/monitor/etc/htpasswd.users";
	public $overwrite = false; /**< Overwrite user's passwords? */
	public $passwd_ary = array(); /**< Map between usernames and passwords */
	public $existing_ary = array(); /**< The list of users that already exists */
	private $db_table = "users";
	private $DEBUG = false;

	/**
	 * Constructor. Parses the provided htpasswd file
	 */
	public function __construct($htpasswd_file = false)
	{
		parent::__construct();
		$this->htpasswd_file = $htpasswd_file;
		$this->parse_file($this->htpasswd_file);
	}

	/**
	 * Set option in model
	 */
	public function set_option($k, $v)
	{
		if (!isset($this->$k))
			return false;

		$this->$k = $v;
		return true;
	}

	/**
	 * Read all existing users from db
	 *
	 * FIXME: I don't belong here, as I perform generic user functionality -
	 * will someone help me move?
	 */
	public function get_existing_users()
	{
		$query = 'SELECT username, password_algo, password ' .
			'FROM ' . $this->db_table;
		$result = $this->db->query($query);

		# use result as array
		$result = $result->result(false);

		foreach ($result as $row) {
			$ary = array();
			foreach ($row as $k => $v) {
				$ary[strtolower($k)] = $v;
			}
			$this->existing_ary[$ary['username']] = array
				('hash' => $ary['password'], 'algo' => $ary['password_algo']);
		}
	}

	/**
	 * Write all password hashes to db
	 */
	public function write_hashes_to_db()
	{
		$this->get_existing_users();

		foreach ($this->passwd_ary as $user => $ary) {
			$hash = $ary['hash'];
			$algo = $ary['algo'];
			$is_new = false; 	# keep track if user is new and should be assigned
								# the login role

			# if we're not supposed to overwrite user's passwords
			# and this user already exist, just move along
			if (isset($this->existing_ary[$user])) {
				if (!$this->overwrite)
					continue;
				if ($hash == $this->existing_ary[$user]['hash'] &&
					$algo == $this->existing_ary[$user]['algo'])
				{
					continue;
				}

				$query = "UPDATE $this->db_table SET " .
					"password_algo = " . $this->db->escape($algo) . ", " .
					"password = " . $this->db->escape($hash) . " " .
					"WHERE username = " . $this->db->escape($user);
			} else {
				$query = 'INSERT INTO ' . $this->db_table .
					'(username, password_algo, password) VALUES(' .
					$this->db->escape($user) . ", " .
					$this->db->escape($algo) . ", " .
					$this->db->escape($hash) . ")";
					$is_new = true; # mark this as new user
			}

			$result = $this->db->query($query);
			if ($result !== false) {
				$user_res = $this->db->query('SELECT id FROM '.$this->db_table.' WHERE username = ' . $this->db->escape($user));
				if ($user_res != false) {
					$ary = $user_res->current();
					unset ($user_res);
					$this->add_user_role($ary->id);
				}
				unset($result);
				unset($user_res);
			}
		}
	}

	/**
	 * Read hashes from file, and save to database
	 */
	public function import_hashes($htpasswd_file = false)
	{
		$ary = $this->parse_file($htpasswd_file);
		if ($ary === false)
			return false;

		return $this->write_hashes_to_db($ary);
	}

	/**
	 * Given a hash, return the algorithm name
	 */
	public function get_algo(&$hash)
	{
		if (!strncmp($hash, "{SHA}", 5)) {
			$hash = substr($hash, 5);
			return "b64_sha1";
		}
		if (!strncmp($hash, '$apr1$', 6))
			return "apr_md5";
		if (strlen($hash) === 13)
			return "crypt";

		$hash = sha1($hash);
		return "sha1";
	}

	/**
	 * Parse a htpasswd file
	 */
	public function parse_file($htpasswd_file = false)
	{
		if (!$htpasswd_file)
			$htpasswd_file = $this->htpasswd_file;

		if (!$htpasswd_file || !file_exists($htpasswd_file))
			return false;

		$buf = explode("\n", file_get_contents($htpasswd_file));

		foreach ($buf as $line) {
			$line = trim($line);
			if (empty($line))
				continue;

			$ary = explode(':', $line, 2);
			$hash = $ary[1];
			$algo = $this->get_algo($hash);
			$ent = array('hash' => $hash, 'algo' => $algo);
			$this->passwd_ary[$ary[0]] = $ent;
		}

		return $this->passwd_ary;
	}

	/**
	*	Add role for last inserted user
	*/
	public function add_user_role($user_id=false)
	{
		$user_id = (int)$user_id;
		if (!$user_id)
			return false;
		$login_role = 1;

		# make sure that the user hasn't been assigned the login role
		# already as this will result in duplicate ID error.
		$sql = "SELECT * FROM roles_users WHERE user_id=".$user_id." AND role_id=".$login_role;
		$res = $this->db->query($sql);
		if (count($res) == 0) {
			unset($res);
			$sql = "INSERT INTO roles_users (user_id, role_id) ";
			$sql .= "VALUES(".$user_id.", ".$login_role.")";
			$this->db->query($sql);
		}
	}

}
