<?php

defined('SYSPATH') or die('No direct access allowed.');
/**
 * Hosts widget for tactical overview
 *
 * We use the gridstat_Widget from the module lsfilter as template, with just a
 * simple configuraiton. That widget just needs a configuration to work.
 *
 * @author op5 AB
 */
class Tac_scheduled_Widget extends gridstat_Widget {
	public function __construct($widget_model) {
		parent::__construct($widget_model);
		$this->settings = array (
			array (
				'title' => _('Down'),
				'icon' => 'scheduled-downtime',
				'fields' => array(
					array(
						'filter' => '[hosts] (has_been_checked = 1 and state = 1) and scheduled_downtime_depth > 0',
						'text' => '%d '._('Scheduled hosts')
					)
				)
			),
			array (
				'title' => _('Unreachable'),
				'icon' => 'scheduled-downtime',
				'fields' => array(
					array(
						'filter' => '[hosts] (has_been_checked = 1 and state = 2) and scheduled_downtime_depth > 0',
						'text' => '%d '._('Scheduled hosts')
					)
				)
			),
			array (
				'title' => _('Up'),
				'icon' => 'scheduled-downtime',
				'fields' => array(
					array(
						'filter' => '[hosts] (has_been_checked = 1 and state = 0) and scheduled_downtime_depth > 0',
						'text' => '%d '._('Scheduled hosts')
					)
				)
			),
			array (
				'title' => _('Pending'),
				'icon' => 'scheduled-downtime',
				'fields' => array(
					array(
						'filter' => '[hosts] has_been_checked = 0 and scheduled_downtime_depth > 0',
						'text' => '%d '._('Scheduled hosts')
					)
				)
			),
			array (
				'title' => _('Critical'),
				'icon' => 'scheduled-downtime',
				'fields' => array(
					array(
						'filter' => '[services] (has_been_checked = 1 and state = 2) and scheduled_downtime_depth > 0',
						'text' => '%d '._('Scheduled services')
					)
				)
			),
			array (
				'title' => _('Warning'),
				'icon' => 'scheduled-downtime',
				'fields' => array(
					array(
						'filter' => '[services] (has_been_checked = 1 and state = 1) and scheduled_downtime_depth > 0',
						'text' => '%d '._('Scheduled services')
					)
				)
			),
			array (
				'title' => _('Unknown'),
				'icon' => 'scheduled-downtime',
				'fields' => array(
					array(
						'filter' => '[services] (has_been_checked = 1 and state = 3) and scheduled_downtime_depth > 0',
						'text' => '%d '._('Scheduled services')
					)
				)
			),
			array (
				'title' => _('Ok'),
				'icon' => 'scheduled-downtime',
				'fields' => array(
					array(
						'filter' => '[services] (has_been_checked = 1 and state = 0) and scheduled_downtime_depth > 0',
						'text' => '%d '._('Scheduled services')
					)
				)
			),
			array (
				'title' => _('Pending'),
				'icon' => 'scheduled-downtime',
				'fields' => array(
					array(
						'filter' => '[services] has_been_checked = 0 and scheduled_downtime_depth > 0',
						'text' => '%d '._('Scheduled services')
					)
				)
			)
		);
	}
}