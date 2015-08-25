<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * @author     op5 AB
 */
class Tac_problems_Widget extends gridstat_Widget {
	public function __construct($widget_model) {
		widget_Base::__construct($widget_model);


		$this->settings = array(
				array(
						'title' => _('Network outages'),
						'icon' => 'shield-outages',
						'fields' => array(
								array(
										'text' => _('%d network outages'),
										'filter' => '[hosts] state != 0 and has_been_checked = 1 and childs != ""'
								)
						)
				),
				array(
						'title' => _('Hosts down'),
						'icon' => 'shield-down',
						'fields' => array(
								array(
										// TODO: Passive as active
										'text' => _('%d unhandled problems'),
										'filter' => '[hosts] state = 1 and has_been_checked = 1 and scheduled_downtime_depth = 0 and acknowledged = 0 and checks_enabled = 1'
								)
						)
				),
				array(
						'title' => _('Services Critical'),
						'icon' => 'shield-critical',
						'fields' => array(
								array(
										// TODO: Passive as active
										'text' => _('%d unhandled problems'),
										'filter' => '[services] state = 2 and has_been_checked = 1 and scheduled_downtime_depth = 0 and acknowledged = 0 and checks_enabled = 1 and host.state = 0 and host.has_been_checked = 1'
								),
								array(
										// TODO: Passive as active
										'text' => _('%d on problem hosts'),
										'filter' => '[services] state = 2 and has_been_checked = 1 and scheduled_downtime_depth = 0 and acknowledged = 0 and checks_enabled = 1 and (host.state != 0 or host.has_been_checked = 0)'
								),
						)
				),
				array(
						'title' => _('Services Warning'),
						'icon' => 'shield-warning',
						'fields' => array(
								array(
										// TODO: Passive as active
										'text' => _('%d unhandled problems'),
										'filter' => '[services] state = 1 and has_been_checked = 1 and scheduled_downtime_depth = 0 and acknowledged = 0 and checks_enabled = 1 and host.state = 0 and host.has_been_checked = 1'
								),
								array(
										// TODO: Passive as active
										'text' => _('%d on problem hosts'),
										'filter' => '[services] state = 1 and has_been_checked = 1 and scheduled_downtime_depth = 0 and acknowledged = 0 and checks_enabled = 1 and (host.state != 0 or host.has_been_checked = 0)'
								),
						)
				),
				array(
						'title' => _('Services Unknown'),
						'icon' => 'shield-unknown',
						'fields' => array(
								array(
										// TODO: Passive as active
										'text' => _('%d unhandled problems'),
										'filter' => '[services] state = 3 and has_been_checked = 1 and scheduled_downtime_depth = 0 and acknowledged = 0 and checks_enabled = 1 and host.state = 0 and host.has_been_checked = 1'
								),
								array(
										// TODO: Passive as active
										'text' => _('%d on problem hosts'),
										'filter' => '[services] state = 3 and has_been_checked = 1 and scheduled_downtime_depth = 0 and acknowledged = 0 and checks_enabled = 1 and (host.state != 0 or host.has_been_checked = 0)'
								),
						)
				)
		);
	}
}
