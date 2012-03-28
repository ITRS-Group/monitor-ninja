Feature: Availability reports

	Scenario: Toggle JS-calendars on custom report date
		Given I am logged in as "monitor" with password "monitor"
		And I click "Availability"
		And I select "* CUSTOM REPORT PERIOD *" from "report_period"

		And I click css "#cal_start"
		Then I should see css "#dp-popup"
		When I click css "#filter_field"
		Then I shouldn't see css "#dp-popup"

		When I click css "#cal_end"
		Then I should see css "#dp-popup"
		When I click css "#filter_field"
		Then I shouldn't see css "#dp-popup"
