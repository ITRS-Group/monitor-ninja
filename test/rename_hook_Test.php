<?php

/**
 * Ensure state before and after objects' name changes.
 */
class Rename_hook_Test extends PHPUnit_Framework_TestCase {

	function test_rename_host_in_host_report() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'hosts',
			'objects' => array(
				'killa',
				'bee'
			)
		));
		$this->assertContains('killa', $report['objects']);
		$this->assertCount(2, $report['objects']);

		$something_changed = $report->rename_object('host', 'killa', 'vanilla');
		$this->assertTrue($something_changed);

		$this->assertContains('vanilla', $report['objects']);
		$this->assertNotContains('killa', $report['objects']);
		$this->assertCount(2, $report['objects']);
	}

	function test_rename_host_in_service_report() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'services',
			'objects' => array(
				'killa;bee',
				'mister;clean'
			)
		));
		$this->assertContains('killa;bee', $report['objects']);
		$this->assertCount(2, $report['objects']);

		$something_changed = $report->rename_object('host', 'killa', 'vanilla');
		$this->assertTrue($something_changed);

		$this->assertContains('vanilla;bee', $report['objects']);
		$this->assertNotContains('killa;bee', $report['objects']);
		$this->assertCount(2, $report['objects']);
	}

	function test_remove_servicegroup() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'servicegroups',
			'objects' => array(
				'killa',
				'bee'
			)
		));
		$this->assertContains('killa', $report['objects']);
		$this->assertCount(2, $report['objects']);

		$something_changed = $report->remove_object('servicegroup', 'killa');
		$this->assertTrue($something_changed);

		$this->assertNotContains('killa', $report['objects']);
		$this->assertCount(1, $report['objects']);
	}

	function test_remove_host_in_service_report() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'services',
			'objects' => array(
				'lille;skutt',
				'skal;man'
			)
		));
		$this->assertContains('lille;skutt', $report['objects']);
		$this->assertCount(2, $report['objects']);

		$something_changed = $report->remove_object('host', 'lille');
		$this->assertTrue($something_changed);

		$this->assertNotContains('lille;skutt', $report['objects']);
		$this->assertCount(1, $report['objects']);
	}

	function test_no_remove_all_objects() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'services',
			'objects' => Report_options::ALL_AUTHORIZED
		));

		$something_changed = $report->remove_object('host', 'lille');
		$this->assertFalse($something_changed);
	}

	function test_no_remove_for_irrelevant_object() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'services',
			'objects' => array(
				'judge;dredd'
			)
		));
		$this->assertContains('judge;dredd', $report['objects']);
		$this->assertCount(1, $report['objects']);

		$something_changed = $report->remove_object('host', 'megaman');
		$this->assertFalse($something_changed);

		$this->assertCount(1, $report['objects']);
	}

	function test_no_remove_when_changed_object_differs_from_reports_object_type() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'hosts',
			'objects' => array(
				'powerpuffgirls'
			)
		));
		$this->assertContains('powerpuffgirls', $report['objects']);
		$this->assertCount(1, $report['objects']);

		$something_changed = $report->remove_object('servicegroup', 'powerpuffgirls');
		$this->assertFalse($something_changed);

		$this->assertContains('powerpuffgirls', $report['objects']);
		$this->assertCount(1, $report['objects']);
	}

	function test_no_rename_all_objects() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'services',
			'objects' => Report_options::ALL_AUTHORIZED
		));

		$something_changed = $report->rename_object('host', 'jamson', 'tarzan');
		$this->assertFalse($something_changed);
	}

	function test_no_rename_for_irrelevant_object() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'services',
			'objects' => array(
				'judge;dredd'
			)
		));
		$this->assertContains('judge;dredd', $report['objects']);
		$this->assertCount(1, $report['objects']);

		$something_changed = $report->rename_object('host', 'pelle', 'palle');
		$this->assertFalse($something_changed);

		$this->assertCount(1, $report['objects']);
	}

	function test_no_rename_when_changed_object_differs_from_reports_object_type() {
		$report = Report_options::setup_options_obj('avail', array(
			'report_type' => 'hosts',
			'objects' => array(
				'blumchen'
			)
		));
		$this->assertContains('blumchen', $report['objects']);
		$this->assertCount(1, $report['objects']);

		$something_changed = $report->rename_object('servicegroup', 'blumchen', 'e-type');
		$this->assertFalse($something_changed);

		$this->assertContains('blumchen', $report['objects']);
		$this->assertCount(1, $report['objects']);
	}
}
