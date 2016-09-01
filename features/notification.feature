@notification
Feature: Notification
	@contacts-MON7591
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
		And I am logged in as monitor
		When I hover over "Manage" menu
		And I click "View active config"
		When I select "Object type" as "contacts"
		Then I should see "Contact Name" "administrator"
		And I should see "Service Notification Period" "workhours"
		And I should see "Host Notification Period" "nonworkhours"