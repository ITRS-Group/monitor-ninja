<?php

require_once( dirname(__FILE__).'/base/baseservice.php' );

class Service_Model extends BaseService_Model {
	static public $macros =  array(
		'$HOSTNAME$' => 'host.name',
		'$HOSTADDRESS$' => 'host.address',
		'$HOSTDISPLAYNAME$' => 'host.display_name',
		'$HOSTALIAS$' => 'host.alias',
		'$HOSTSTATE$' => 'host.state_text_uc',
		'$HOSTSTATEID$' => 'host.state',
		'$HOSTSTATETYPE$' => 'host.state_type_text_uc',
		'$HOSTATTEMPT$' => 'host.current_attempt',
		'$MAXHOSTATTEMPTS$' => 'host.max_check_attempts',
		'$HOSTGROUPNAME$' => 'host.first_group',
		'$SERVICEDESC$' => 'description',
		'$SERVICEDISPLAYNAME$' => 'display_name',
		'$SERVICEGROUPNAME$' => 'first_group',
		'$SERVICESTATE$' => 'state',
		'$CURRENT_USER$' => 'current_user'
	);
	
	static public $rewrite_columns = array(
		'state_text_uc'   => array('state_text'),
		'state_text'      => array('state','has_been_checked'),
		'first_group'     => array('groups'),
		'checks_disabled' => array('active_checks_enabled'),
		'duration'        => array('last_state_change'),
		'comments_count'  => array('comments')
		);

	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'state_text';
		$this->export[] = 'checks_disabled';
		$this->export[] = 'duration';
		$this->export[] = 'comments_count';
	}

	public function get_state_text() {
		if( !$this->get_has_been_checked() )
			return 'pending';
		switch( $this->get_state() ) {
			case 0: return 'ok';
			case 1: return 'warning';
			case 2: return 'critical';
			case 3: return 'unknown';
		}
		return 'unknown'; // should never happen
	}
	
	public function get_state_type_text_uc() {
		return $this->get_state_type()?'HARD':'SOFT';
	}
	
	public function get_first_group() {
		$groups = $this->get_groups();
		if(isset($groups[0])) return $groups[0];
		return '';
	}

	public function get_checks_disabled() {
		//FIXME: passive as active
		return !$this->get_active_checks_enabled();
	}

	public function get_duration() {
		$now = time();
		$last_state_change = $this->get_last_state_change();
		if( $last_state_change == 0 )
			return -1;
		return $now - $last_state_change;
	}
	
	public function get_notes_url() {
		return $this->expand_macros(parent::get_notes_url());
	}
	
	public function get_action_url() {
		return $this->expand_macros(parent::get_action_url());
	}
	
	public function get_comments_count() {
		return count($this->get_comments());
	}
}
