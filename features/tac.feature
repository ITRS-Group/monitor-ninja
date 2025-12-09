@editedhappypath
Feature: Tactical Overview, TAC

	Background:
		Given I am real logged in as "monitor" with password "monitor"

	Scenario: No failed widget should be visible
		Then I shouldn't see "This widget failed to load"

	Scenario: Info about no dashboard
		Then I should see "No dashboard"

	Scenario: Info about no widgets
		When I hover over the "Dashboards" menu
		And I click "New dashboard"
		And I enter "New Dashboard with no Widgets" into "name"
		And I click "save"
		Then I should see "No widgets"
