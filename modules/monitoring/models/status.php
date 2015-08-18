<?php

require_once( dirname(__FILE__).'/base/basestatus.php' );

/**
 * Describes a single object from livestatus
 */
class Status_Model extends BaseStatus_Model {
	/**
	 * @ninja orm_command name Restart the Naemon process
	 * @ninja orm_command category Process
	 * @ninja orm_command icon command
	 *
	 * @ninja orm_command mayi_method update.command.restart_process
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function restart_process() {
		return $this->submit_naemon_command("RESTART_PROCESS");
	}

	/**
	 * @ninja orm_command name Shut down the Naemon process
	 * @ninja orm_command category Process
	 * @ninja orm_command icon command
	 *
	 * @ninja orm_command mayi_method update.command.shutdown_process
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function shutdown_process() {
		return $this->submit_naemon_command("SHUTDOWN_PROCESS");
	}

	/**
	 * @ninja orm_command name Disable Notifications
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.notification
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if enable_notifications
	 */
	public function disable_notifications() {
		return $this->submit_naemon_command("DISABLE_NOTIFICATIONS");
	}

	/**
	 * @ninja orm_command name Enable Notifications
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.notification
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !enable_notifications
	 */
	public function enable_notifications() {
		return $this->submit_naemon_command("ENABLE_NOTIFICATIONS");
	}

	/**
	 * @ninja orm_command name Stop executing serivce checks
	 * @ninja orm_command category Service operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if execute_service_checks
	 */
	public function disable_service_checks() {
		return $this->submit_naemon_command("STOP_EXECUTING_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Start executing serivce checks
	 * @ninja orm_command category Service operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !execute_service_checks
	 */
	public function enable_service_checks() {
		return $this->submit_naemon_command("START_EXECUTING_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Stop accepting passive serivce checks
	 * @ninja orm_command category Service operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if accept_passive_service_checks
	 */
	public function disable_service_passive_checks() {
		return $this->submit_naemon_command("STOP_ACCEPTING_PASSIVE_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Start accepting passive serivce checks
	 * @ninja orm_command category Service operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !accept_passive_service_checks
	 */
	public function enable_service_passive_checks() {
		return $this->submit_naemon_command("START_ACCEPTING_PASSIVE_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Stop executing host checks
	 * @ninja orm_command category Host operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if execute_host_checks
	 */
	public function disable_host_checks() {
		return $this->submit_naemon_command("STOP_EXECUTING_HOST_CHECKS");
	}

	/**
	 * @ninja orm_command name Start executing host checks
	 * @ninja orm_command category Host operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !execute_host_checks
	 */
	public function enable_host_checks() {
		return $this->submit_naemon_command("START_EXECUTING_HOST_CHECKS");
	}

	/**
	 * @ninja orm_command name Stop accepting passive host checks
	 * @ninja orm_command category Host operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if accept_passive_service_checks
	 */
	public function disable_host_passive_checks() {
		return $this->submit_naemon_command("STOP_ACCEPTING_PASSIVE_HOST_CHECKS");
	}

	/**
	 * @ninja orm_command name Start accepting passive host checks
	 * @ninja orm_command category Host operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.enabled
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !accept_passive_service_checks
	 */
	public function enable_host_passive_checks() {
		return $this->submit_naemon_command("START_ACCEPTING_PASSIVE_HOST_CHECKS");
	}

	/**
	 * @ninja orm_command name Disable event handlers
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.event_handler
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if enable_event_handlers
	 */
	public function disable_event_handlers() {
		return $this->submit_naemon_command("DISABLE_EVENT_HANDLERS");
	}

	/**
	 * @ninja orm_command name Enable event handlers
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.event_handler
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !enable_event_handlers
	 */
	public function enable_event_handlers() {
		return $this->submit_naemon_command("ENABLE_EVENT_HANDLERS");
	}

	/**
	 * @ninja orm_command name Stop obsessing over services
	 * @ninja orm_command category Service operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.obsess
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if obsess_over_services
	 */
	public function stop_obsessing_over_services() {
		return $this->submit_naemon_command("STOP_OBSESSING_OVER_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Start obsessing over services
	 * @ninja orm_command category Service operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.obsess
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !obsess_over_services
	 */
	public function start_obsessing_over_services() {
		return $this->submit_naemon_command("START_OBSESSING_OVER_SVC_CHECKS");
	}

	/**
	 * @ninja orm_command name Stop obsessing over hosts
	 * @ninja orm_command category Host operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.obsess
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if obsess_over_hosts
	 */
	public function stop_obsessing_over_hosts() {
		return $this->submit_naemon_command("STOP_OBSESSING_OVER_HOST_CHECKS");
	}

	/**
	 * @ninja orm_command name Start obsessing over hosts
	 * @ninja orm_command category Host operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.obsess
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !obsess_over_hosts
	 */
	public function start_obsessing_over_hosts() {
		return $this->submit_naemon_command("START_OBSESSING_OVER_HOST_CHECKS");
	}

	/**
	 * @ninja orm_command name Disable flap detection
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.flapping
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if enable_flap_detection
	 */
	public function stop_flap_detection() {
		return $this->submit_naemon_command("DISABLE_FLAP_DETECTION");
	}

	/**
	 * @ninja orm_command name Enable flap detection
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.flapping
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !enable_flap_detection
	 */
	public function start_flap_detection() {
		return $this->submit_naemon_command("ENABLE_FLAP_DETECTION");
	}

	/**
	 * @ninja orm_command name Disable performance data processing
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon disabled
	 *
	 * @ninja orm_command mayi_method update.command.flapping
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if process_performance_data
	 */
	public function stop_perfdata_processing() {
		return $this->submit_naemon_command("DISABLE_PERFORMANCE_DATA");
	}

	/**
	 * @ninja orm_command name Enable performance data processing
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon enabled
	 *
	 * @ninja orm_command mayi_method update.command.flapping
	 * @ninja orm_command view monitoring/naemon_command
	 * 
	 * @ninja orm_command enabled_if !process_performance_data
	 */
	public function start_perfdata_processing() {
		return $this->submit_naemon_command("ENABLE_PERFORMANCE_DATA");
	}
}
