Feature: Tactical Overview, TAC
	Widgets

	@widget
	Scenario: All widgets should be reachable
		Given I am logged in as "monitor" with password "monitor"
		Then I shouldn't see "Couldn't load widget"
