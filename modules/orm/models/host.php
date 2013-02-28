<?php

require_once( dirname(__FILE__).'/base/basehost.php' );

/**
 * Describes a single object from livestatus
 */
class Host_Model extends BaseHost_Model {
	/**
	 * A list of macros to expand for the current object
	 */
	static public $macros = array(
		'$HOSTNAME$' => 'name',
		'$HOSTADDRESS$' => 'address',
		'$HOSTDISPLAYNAME$' => 'display_name',
		'$HOSTALIAS$' => 'alias',
		'$HOSTSTATE$' => 'state_text_uc',
		'$HOSTSTATEID$' => 'state',
		'$HOSTSTATETYPE$' => 'state_type_text_uc',
		'$HOSTATTEMPT$' => 'current_attempt',
		'$MAXHOSTATTEMPTS$' => 'max_check_attempts',
		'$HOSTGROUPNAME$' => 'first_group',
		'$CURRENT_USER$' => 'current_user'
	);

	/**
	 * An array containing the custom column dependencies
	 */
	static public $rewrite_columns = array(
		'state_text_uc' => array('state_text'),
		'state_type_text_uc' => array('state_type'),
		'state_text' => array('state','has_been_checked'),
		'first_group' => array('groups'),
		'checks_disabled' => array('active_checks_enabled'),
		'duration' => array('last_state_change'),
		'comments_count' => array('comments'),
		'config_url'      => array('name'),
		'check_type_str'  => array('check_type')
	);

	/**
	 * Create an instance of the given type. Don't call dirctly, called from *Set_Model-objects
	 */
	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'state_text';
		$this->export[] = 'checks_disabled';
		$this->export[] = 'duration';
		$this->export[] = 'comments_count';
		$this->export[] = 'config_url';
		$this->export[] = 'check_type_str';
	}

	/**
	 * Get state, as text
	 */
	public function get_state_text() {
		if( !$this->get_has_been_checked() )
			return 'pending';
		switch( $this->get_state() ) {
			case 0: return 'up';
			case 1: return 'down';
			case 2: return 'unreachable';
		}
		return 'unknown'; // should never happen
	}

	/**
	 * Get the state, as uppercase
	 */
	public function get_state_text_uc() {
		return strtoupper($this->get_state_text());
	}

	/**
	 * get the state type, as text in uppercase (HARD/SOFT)
	 */
	public function get_state_type_text_uc() {
		return $this->get_state_type()?'HARD':'SOFT';
	}

	/**
	 * get if checks are disabled
	 */
	public function get_checks_disabled() {
		//FIXME: passive as active
		return !$this->get_active_checks_enabled();
	}

	/**
	 * Get the first host group of the host group memberships
	 */
	public function get_first_group() {
		$groups = $this->get_groups();
		if(isset($groups[0])) return $groups[0];
		return '';
	}

	/**
	 * Get duration
	 */
	public function get_duration() {
		$now = time();
		$last_state_change = $this->get_last_state_change();
		if( $last_state_change == 0 )
			return -1;
		return $now - $last_state_change;
	}

	/**
	 * Get the notes url, with macros expanded
	 */
	public function get_notes_url() {
		return $this->expand_macros_url(parent::get_notes_url());
	}

	/**
	 * Get the notes field, with macros expanded
	 */
	public function get_notes() {
		return $this->expand_macros(parent::get_notes());
	}

	/**
	 * Get the action url with macros expanded
	 */
	public function get_action_url() {
		return $this->expand_macros_url(parent::get_action_url());
	}

	/**
	 * get the number of comments associated to the host
	 */
	public function get_comments_count() {
		return count($this->get_comments());
	}
	
	/**
	 * Get check type, as a string ("active" or "passive")
	 */
	public function get_check_type_str() {
		return $this->get_check_type() ? 'passive' : 'active';
	}
	
	/**
	 * Get a list of custom commands for the host
	 */
	public function get_custom_commands() {
		return Custom_command_Model::parse_custom_variables($this->get_custom_variables());
	}
}
