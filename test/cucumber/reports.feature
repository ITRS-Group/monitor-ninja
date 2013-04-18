@reports
Feature: Reports
	This is intended to contain general report tests, rather than report-type
	specific report tests which can be found in their respective own features

	@asmonitor
	Scenario: All helptexts are defined
		Given I am on the Host details page
		When I hover over the "Reporting" button
		And I click "SLA"
		Then all helptexts should be defined
		When I hover over the "Reporting" button
		And I click "Availability"
		Then all helptexts should be defined
		When I hover over the "Reporting" button
		And I click "Alert Summary"
		Then all helptexts should be defined
		When I choose "Custom"
		Then all helptexts should be defined
		When I hover over the "Reporting" button
		And I click "Schedule Reports"
		Then all helptexts should be defined

	@asmonitor @calendar
	Scenario: Toggle JS-calendars on custom report date
		Given I am on the Host details page
		When I hover over the "Reporting" button
		And I click "Availability"
		And I select "Custom" from "Reporting period"

		And I click css "#cal_start"
		Then I should see css "#dp-popup"
		When I click css "#filter_field"
		Then I shouldn't see css "#dp-popup"

		When I click css "#cal_end"
		Then I should see css "#dp-popup"
		When I click css "#filter_field"
		Then I shouldn't see css "#dp-popup"
