@widgets
Feature: Widget Menu

	Background:
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		Given I have these mocked dashboard_widgets
			|id|dashboard_id | name         | position      | setting |
			|1 |1            | listview     | {"c":0,"p":0} | {"title":"My widget name"}|
		And I am logged in
		When I am on the main page
		Then I should see "My widget name"

	Scenario: Clicking Cancel hides widget edit form

		When I hover css ".widget-header"
		And I click link "Edit this widget"
		Then I should see "Custom title"
		And I should see button "Save"
		And I should see button "Cancel"

		When I click "Cancel"
		Then I shouldn't see "Custom title"
		And I shouldn't see button "Save"
		And I shouldn't see button "Cancel"

	Scenario: Clicking Save hides widget edit form

		When I hover css ".widget-header"
		And I click link "Edit this widget"
		Then I should see "Custom title"
		And I should see button "Save"
		And I should see button "Cancel"

		When I click "Save"
		Then I shouldn't see "Custom title"
		And I shouldn't see button "Save"
		And I shouldn't see button "Cancel"

