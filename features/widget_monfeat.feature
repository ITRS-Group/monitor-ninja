Feature: Monitoring features widget

	@editedhappypath
	Scenario: Monitoring features should display command links
		Given I am real logged in as "monitor" with password "monitor"
		And I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "monitoring_features"
		Then I should see "Monitoring features"
		And I should see link "Flap detection enabled"
		And I should see link "Notifications enabled"
		And I should see link "Event handlers enabled"
		And I should see link "Active Host checks enabled"
		And I should see link "Active Service checks enabled"
		And I should see link "Passive Host checks enabled"
		And I should see link "Passive Service checks enabled"
