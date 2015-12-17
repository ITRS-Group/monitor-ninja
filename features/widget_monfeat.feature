Feature: Monitoring features widget

	Scenario: Monitoring features should display command links
		When I am logged in as "monitor" with password "monitor"
		And I am on the main page
		Then I should see "Monitoring features"
		And I should see link "Flap detection enabled"
		And I should see link "Notifications enabled"
		And I should see link "Event handlers enabled"
		And I should see link "Active Host checks enabled"
		And I should see link "Active Service checks enabled"
		And I should see link "Passive Host checks enabled"
		And I should see link "Passive Service checks enabled"

