Feature: Initial administrator setup

	Background:
		Given I have no users configured
		And I have the default authentication module configured
		And I have an admin user group with all rights

	Scenario: I default to the administrator setup page
		Given I am on the login page
		Then I should see "Create your op5 Monitor administrator account"

	Scenario: I create my administrator account
		Given I am on the login page
		Then I should see "Create your op5 Monitor administrator account"
		When I enter "my grandma rocks" into "password"
		And I enter "my grandmas socks" into "password-repeat"
		And I click "Create account"
		Then I should see "Passwords do not match"
		When I enter "my grandma rocks" into "password"
		And I enter "my grandma rocks" into "password-repeat"
		And I click "Create account"
		Then I should be logged in as "administrator"
