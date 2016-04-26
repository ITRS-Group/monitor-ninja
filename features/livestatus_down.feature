Feature: Livestatus error handling

	Background:
		Given I am logged in as administrator

	Scenario: See that "Livestatus down" is handled correctly on a page
		When I am on address "/index.php/failing/orm_exception"
		Then I should see "Service unavailable"
		And I should see "We were unable to satisfy your request at this time, you may attempt to refresh this page in your browser"
		And I should see "Please contact your administrator"

	Scenario: See that "Livestatus down" is handled correct in pre controller
		# This hook throws an exception in system.pre_controller
		When I am on address "/index.php/failing/hook"
		Then I should see "Service unavailable"
		And I should see "This page should display a Service Unavailable message"
		And I should see "Please contact your administrator"
