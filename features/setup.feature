Feature: Initial administrator setup

	Background:
		Given I have no users configured
		And I have the default authentication module configured
		And I have an admins user group with all rights

	Scenario: I default to the administrator setup page
		Given I am on the login page
		Then I should see "Create your administrator account"

	Scenario: I create my administrator account
		Given I am on the login page
		Then I should see "Create your administrator account"
		When I enter "my grandma rocks" into "password"
		And I enter "my grandmas socks" into "password-repeat"
		And I click "Create account"
		Then I should see "Passwords do not match"
		When I enter "my grandma rocks" into "password"
		And I enter "my grandma rocks" into "password-repeat"
		And I click "Create account"
		Then I should be logged in as "administrator"
		When I go to the listview for [contacts] all
		Then I should see "administrator"

	Scenario: I create my administrator account and choose a username
		Given I am on the login page
		Then I should see "Create your administrator account"
		When I enter "a super secret" into "password"
		And I enter "a super secret" into "password-repeat"
		And I enter "bikermise_from_mars" into "username"
		And I click "Create account"
		Then I should be logged in as "bikermise_from_mars"
		When I go to the listview for [contacts] all
		Then I should see "bikermise_from_mars"
