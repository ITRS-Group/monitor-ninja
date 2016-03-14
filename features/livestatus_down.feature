Feature: Livestatus error handling

	Background:
		Given I am logged in as administrator

	Scenario: See that "Livestatus down" error message looks correct in listviews
		When I am on address listview/fetch_ajax?query=[failing] all&limit=100
		Then I should see "Error: Services not available. Is Livestatus down? If the problem persists, please contact your administrator"

	Scenario: See that "Livestatus down" correct on page created by a controller
		When I am on address failing/orm_exception
		Then I should see "Fantastic error message"
