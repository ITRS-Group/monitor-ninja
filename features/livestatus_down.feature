Feature: Livestatus error handling

	Background:
		Given I am logged in as administrator

	Scenario: See that we get the expected error when quering backend
		When I am on address "/index.php/listview/fetch_ajax?query=[failing] all&limit=100"
		Then I should see "This is a failing test driver."

	Scenario: See that "Livestatus down" is handled correct on a page
		When I am on address "/index.php/failing/orm_exception"
		Then I should see "Service unavailable"
		And I should see "We were unable to satisfy your request at this time, you may attempt to refresh this page in your browser"
		And I should see "Please contact your administrator"
