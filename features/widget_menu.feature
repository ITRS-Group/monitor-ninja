Feature: Widget Menu

	@editedhappypath
	Scenario: Clicking Cancel hides widget edit form
		Given I am real logged in as "monitor" with password "monitor"
		When I hover css ".widget-header"
		And I click link "Edit this widget"
		Then I should see "Custom title"
		And I should see button "Save"
		And I should see button "Cancel"

		When I click "Cancel"
		Then I shouldn't see "Custom title"
		And I shouldn't see button "Save"
		And I shouldn't see button "Cancel"

	@editedhappypath
	Scenario: Clicking Save hides widget edit form
		Given I am real logged in as "monitor" with password "monitor"
		When I hover css ".widget-header"
		And I click link "Edit this widget"
		Then I should see "Custom title"
		And I should see button "Save"
		And I should see button "Cancel"

		When I click "Save"
		Then I shouldn't see "Custom title"
		And I shouldn't see button "Save"
		And I shouldn't see button "Cancel"

