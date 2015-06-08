@reports
Feature: Reports
	This is intended to contain general report tests, rather than report-type
	specific report tests which can be found in their respective own features

	@asmonitor @reports @unreliable
	Scenario: All helptexts are defined
		Given I am on the Host details page
		When I hover over the "Report" menu
		When I hover over the "SLA" menu
		And I click "Setup SLA Report"
		Then all helptexts should be defined
		When I hover over the "Report" menu
		When I hover over the "Availability" menu
		And I click "Setup Availability Report"
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

	@asmonitor @reports @calendar
	Scenario: Toggle JS-calendars on custom report date
		Given I am on the Host details page
		When I hover over the "Report" menu
		And I hover over the "Availability" menu
		And I click "Setup Availability Report"
		And I select "Custom" from "Reporting period"

		And I click css "#cal_start"
		Then I should see css "#dp-popup"
		When I click css ".jq-filterable-filter"
		Then I shouldn't see css "#dp-popup"

		When I click css "#cal_end"
		Then I should see css "#dp-popup"
		When I click css ".jq-filterable-filter"
		Then I shouldn't see css "#dp-popup"
