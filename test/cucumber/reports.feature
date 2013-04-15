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
