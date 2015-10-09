@widgets
Feature: Widgets

	# assumption: being authed requires extra tags here, which we leave out
	@external_widget
	Scenario: External widget Unhandled problems
		# could be replaced by complete mockup if we want to decouple livestatus
		When I expose the widget "tac_problems"
		And I am on address "/monitor/index.php/external_widget/tac_problems"
		Then I should see "HOSTS DOWN"

	@external_widget
	Scenario: Requesting non-existing widget
		When I expose the widget "tac_problems"
		And I am on address "/monitor/index.php/external_widget/pippilottarullgardina"
		Then I should see "Widget not found"
