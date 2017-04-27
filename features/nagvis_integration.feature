Feature: Nagvis Integration
	Background:
		Given I am logged in as administrator

	Scenario: I get 404 when not supplying arguments to nagvis view URL
		When I am on address "/index.php/nagvis/view"
		Then I should see "404 Not Found"

	Scenario: I get 404 when not supplying arguments to nagvis rotate URL
		When I am on address "/index.php/nagvis/rotate"
		Then I should see "404 Not Found"
