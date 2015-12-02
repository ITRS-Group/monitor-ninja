@widgets
Feature: Widgets

	@external_widget
	Scenario: External widget Unhandled problems
		# could be replaced by complete mockup if we want to decouple livestatus
		When I expose the widget "tac_problems"
		And I am on address "/monitor/index.php/external_widget/tac_problems"
		Then I should see "HOSTS DOWN"
		When I am on the main page
		Then I should see "Password"

	@external_widget
	Scenario: Requesting non-existing widget
		When I expose the widget "tac_problems"
		And I am on address "/monitor/index.php/external_widget/pippilottarullgardina"
		Then I should see "Widget not found"
		When I am on the main page
		Then I should see "Password"

	@external_widget
	Scenario: External widget nagvis
		Given I expose the widget "nagvis" with settings
			| height | 600     |
			| map    | automap |
		When I am on address "/monitor/index.php/external_widget/nagvis"
		# Default root of automap is monitor
		Then I should see "monitor" within frame "nagvis"
		When I am on the main page
		Then I should see "Password"
