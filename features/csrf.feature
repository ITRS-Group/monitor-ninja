@csrf
Feature: CSRF Token
	Background:
		Given I have these mocked hosts
			| name          |
			| linux-server1 |
		# We need to be properly logged in as to run the CSRF hook
		And I am logged in as administrator

	Scenario: Token validation should succeed with valid token
		Given I visit the object details page for host "linux-server1"
		And I toggle operating status "Active checks"
		Then the operating status toggle "Active checks" should be active

	Scenario: CSRF Should fail submission of POST form with invalid token
		Given I visit the object details page for host "linux-server1"
		And I have the csrf token "invalidtokenfails"
		And I toggle operating status "Active checks"
		Then I should see an error notification
		And the notification should contain "Failed to toggle setting"

	Scenario: CSRF Should fail submission of POST form with empty token
		Given I visit the object details page for host "linux-server1"
		And I have the csrf token ""
		And I toggle operating status "Active checks"
		Then I should see an error notification
		And the notification should contain "Failed to toggle setting"

	Scenario: CSRF Should fail submission of POST form with no token
		Given I visit the object details page for host "linux-server1"
		And I have no csrf token
		And I toggle operating status "Active checks"
		Then I should see an error notification
		And the notification should contain "Failed to toggle setting"
