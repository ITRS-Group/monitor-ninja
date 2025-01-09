@reports
Feature: Reports
	This is intended to contain general report tests, rather than report-type
	specific report tests which can be found in their respective own features

	@unreliable @unreliable_el7
	Scenario: All helptexts are defined
		Given I am logged in
		And I am on the Host details page
		When I hover over the "Report" menu
		When I hover over the "SLA" menu
		And I click "Create SLA Report"
		Then all helptexts should be defined
		When I hover over the "Report" menu
		When I hover over the "Availability" menu
		And I click "Create Availability Report"
		Then all helptexts should be defined
		When I hover over the "Report" menu
		When I hover over the "Alert Summary" menu
		And I click "Setup Alert Summary"
		Then all helptexts should be defined
		When I choose "Custom"
		Then all helptexts should be defined
		When I hover over the "Report" menu
		And I click "Schedule Reports"
		Then all helptexts should be defined
		When I hover over the "Report" menu
		When I hover over the "Histogram" menu
		And I click "Setup Histogram"
		Then all helptexts should be defined

	@editedhappypath
	Scenario: Toggle JS-calendars on custom report date for Availability Report
		Given I am logged in
		And I am on the main page
		Then I am on the Host details page
		When I hover over the "Report" menu
		And I hover over the "Availability" menu
		And I click "Create Availability Report"
		And I select "Custom" from "Reporting period"
		And I click css "#cal_start"
		Then I should see css "#dp-popup"
		When I click css ".jq-filterable-filter"
		Then I shouldn't see css "#dp-popup"
		When I click css "#cal_end"
		Then I should see css "#dp-popup"
		When I click css ".jq-filterable-filter"
		Then I shouldn't see css "#dp-popup"

	@editedhappypath
	Scenario: Toggle JS-calendars on custom report date for Histogram Report
		Given I am logged in
		And I am on the main page
		Then I am on the Host details page
		When I hover over the "Report" menu
		And I hover over the "Histogram" menu
		And I click "Create Histogram Report"
		And I select "Custom" from "Reporting period"
		And I click css "#cal_start"
		Then I should see css "#dp-popup"
		When I click css ".jq-filterable-filter"
		Then I shouldn't see css "#dp-popup"
		When I click css "#cal_end"
		Then I should see css "#dp-popup"
		When I click css ".jq-filterable-filter"
		Then I shouldn't see css "#dp-popup"

	@editedhappypath
	Scenario: Toggle JS-calendars on custom report date for Summary Report
		Given I am logged in
		And I am on the main page
		Then I am on the Host details page
		When I hover over the "Report" menu
		And I hover over the "Summary" menu
		And I click "Create Summary Report"
		And I click on radio button without id "input[name='report_mode'][value='custom']"
		And I select "Custom" from "Reporting period"
		And I click css "#cal_start"
		Then I should see css "#dp-popup"
		When I click css ".jq-filterable-filter"
		Then I shouldn't see css "#dp-popup"
		When I click css "#cal_end"
		Then I should see css "#dp-popup"
		When I click css ".jq-filterable-filter"
		Then I shouldn't see css "#dp-popup"

	@gian
	Scenario: Check helptexts in Availability Report
		Given I am logged in
		And I am on the Host details page
		When I hover over the "Report" menu
		And I hover over the "Availability" menu
		And I click "Create Availability Report"
		Then the helptext "help:avail.include_alerts" should exist
		And the helptext "help:avail.include_trends" should exist
		And the helptext "help:avail.piechart" should exist
		And the helptext "help:avail.use_alias" should exist
		And the helptext "help:avail.description" should exist
		
	@gian
	Scenario: Check helptexts in SLA Report
		Given I am logged in
		And I am on the Host details page
		When I hover over the "Report" menu
		And I hover over the "SLA" menu
		And I click "Create SLA Report"
		Then the helptext "help:sla.report-type" should exist
		And the helptext "help:sla.reporting_period" should exist
		And the helptext "help:sla.report_time_period" should exist
		And the helptext "help:sla.stated_during_downtime" should exist
		And the helptext "help:sla.include_alerts" should exist
		And the helptext "help:sla.use_alias" should exist
		And the helptext "help:sla.description" should exist
		And the helptext "help:sla.enter-sla" should exist

	@gian
	Scenario: Check helptexts in Histogram Report
		Given I am logged in
		And I am on the Host details page
		When I hover over the "Report" menu
		And I hover over the "Histogram" menu
		And I click "Create Histogram Report"
		Then the helptext "help:histogram.report-type" should exist
	
	@gian
	Scenario: Check helptexts in Scheduled Report
		Given I am logged in
		And I am on the Host details page
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see all elements in the UI
	#	Then the helptext "help:reports.report-type-save" should exist
	#	And the helptext "help:reports.select-report" should exist
	#	And the helptext "help:reports.recipents" should exist
	#	And the helptext "help:reports.filename" should exist

	@gian
	Scenario: Check helptexts in Alert Summary Report
		Given I am logged in
		And I am on the Host details page
		When I hover over the "Report" menu
		And I hover over the "Summary" menu
		And I click "Create Summary Report"
		Then I should see all elements in the UI
	#	Then the helptext "help:summary.standardreport" should exist
	#	And the helptext "help:summary.summary_items" should exist