<?php

require_once('op5/mayi.php');

/**
 * Class containing hooks to interface with the monitoring subsystem.
 */
class monitoring_hooks implements op5MayI_Actor {
	public function __construct() {
		Event::add('system.post_controller_constructor',
			array ($this,'load_notifications'));

		$mayi = op5MayI::instance();
		$mayi->be('monitoring', $this);
	}

	/**
	 * Return information about the system usage.
	 *
	 * @return array environemnt
	 */
	public function getActorInfo() {
		$sysinfo = op5sysinfo::instance()->get_usage();
		return array(
			'monitor' => array(
				'hosts' => isset($sysinfo['monitor']) ? $sysinfo['monitor'] : 0,
				'services' => isset($sysinfo['monitor.services']) ? $sysinfo['monitor.services'] : 0,
				'pollers' => isset($sysinfo['pollers']) ? $sysinfo['pollers'] : 0,
				'peers' => isset($sysinfo['peers']) ? $sysinfo['peers'] : 0
			)
		);
	}

	/**
	 * Hook executed in system.post_controller_constructor to load notifications
	 * for system status and configuration.
	 */
	public function load_notifications() {
		$controller = Event::$data;
		/*
		 * We can only add notifications to the ninja controller, so don't
		 * bother otherwise
		 */
		if ($controller instanceof Ninja_Controller) {

			try {
				$status = StatusPool_Model::status();
				if ($status) {
					// we've got access
					if (!$status->get_enable_notifications()) {
						$controller->add_global_notification(
							html::anchor('extinfo/show_process_info',
								_('Notifications are disabled')));
					}
					if (!$status->get_execute_service_checks()) {
						$controller->add_global_notification(
							html::anchor('extinfo/show_process_info',
								_('Service checks are disabled')));
					}
					if (!$status->get_execute_host_checks()) {
						$controller->add_global_notification(
							html::anchor('extinfo/show_process_info',
								_('Host checks are disabled')));
					}
					if (!$status->get_process_performance_data()) {
						$controller->add_global_notification(
							html::anchor('extinfo/show_process_info',
								_('Performance data processing are disabled')));
					}
					if (!$status->get_accept_passive_service_checks()) {
						$controller->add_global_notification(
							html::anchor('extinfo/show_process_info',
								_('Passive service checks are disabled')));
					}
					if (!$status->get_accept_passive_host_checks()) {
						$controller->add_global_notification(
							html::anchor('extinfo/show_process_info',
								_('Passive host checks are disabled')));
					}
					if (!$status->get_enable_event_handlers()) {
						$controller->add_global_notification(
							html::anchor('extinfo/show_process_info',
								_('Event handlers disabled')));
					}
					if (!$status->get_enable_flap_detection()) {
						$controller->add_global_notification(
							html::anchor('extinfo/show_process_info',
								_('Flap detection disabled')));
					}

					unset($status);
				}
			} catch (LivestatusException $e) {
				$controller->add_global_notification(
					_('Livestatus is not accessable'));
			} catch (ORMException $e) {
				$controller->add_global_notification(
					_('Livestatus is not accessable'));
			}
		}
	}
}

new monitoring_hooks();