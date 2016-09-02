@notification
Feature: Notification
	@contacts @MON-7591
	Scenario: Check Contacts notification periods
		Given I have these mocked contactgroups
			| name            | members       |
			| mycontactgroup  | administrator |
		Given I have these mocked contacts
			| name           | host_notification_period | service_notification_period |
			| administrator  | workhours | nonworkhours                               |
		Given I have these mocked hosts
			| name |
			| monitor |
		And I am logged in as administrator
		When I hover over the "Manage" menu
		And I click "View active config"
		When I select "contacts" from "type"
		Then I should see "administrator"
		And I should see "Service Notification Period"
		And I should see "nonworkhours"
		And I should see "Host Notification Period"
		And I should see "workhours"