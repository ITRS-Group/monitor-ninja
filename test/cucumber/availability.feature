Feature: Availability reports
	@asmonitor @calendar
	Scenario: Toggle JS-calendars on custom report date
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
