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

	Scenario: List view notifications page utf-8 check
		Given I have these mocked notifications
			| host_name | contact_name | command_name | output                          |
			| google    | test user    | host-notify  | OK - www.google.com Ã           |
			| google    | TEST USER    | host-notify  | OK - www.google.com             |
			| google    | new user1    | host-notify  | OK - www.google.com             |
			| google    | TeSt UsEr    | host-notify  | OK - www.google.com ö           |
			| google    | NEW USER2    | host-notify  | OK - www.google.com             |
		And I am logged in
		When I am on address "/index.php/listview/?q=%5Bnotifications%5D%20all"
		Then I should see "test user"
		And I should see "TEST USER"
		And I should see "OK - www.google.com ö"
		And I should see "OK - www.google.com Ã"
		And I shouldn't see "json_encode(): Invalid UTF-8 sequence"