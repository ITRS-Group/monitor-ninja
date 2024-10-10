Feature: Change password

	Scenario: Change password
		Given I am logged in as administrator
		And I am on the main page
		And I hover the profile
		And I click "My Account"
		And I click "Change Password"
		And I enter "123123" into "current_password"
		And I enter "billabong" into "new_password"
		And I enter "billabong" into "confirm_password"
		And I click "Change password"
		Then I should see "Password changed successfully"
		And I hover the profile
		When I click "Log out"
		Then I should see "Username"
		When I enter "administrator" into "username"
		And I enter "billabong" into "password"
		And I click "Log in"
		And I hover the profile
		And I click "My Account"
		And I click "Change Password"
		And I enter "billabong" into "current_password"
		And I enter "123123" into "new_password"
		And I enter "123123" into "confirm_password"
		And I click "Change password"
		Then I should see "Password changed successfully"

	@addedhappypath
	Scenario: Password not equal
		Given I am logged in as administrator
		And I am on the main page
		And I hover the profile
		And I click "My Account"
		And I click "Change Password"
		And I enter "" into "current_password"
		And I enter "billabong" into "new_password"
		And I enter "billabongg" into "confirm_password"
		And I click "Change password"
		Then I should see "You entered incorrect current password."

	@addedhappypath
	Scenario: Password is blank
		Given I am logged in as administrator
		And I am on the main page
		And I hover the profile
		And I click "My Account"
		And I click "Change Password"
		And I enter "123123" into "current_password"
		And I click "Change password"
		Then I should see "Password changed successfully"
		And I hover the profile
		When I click "Log out"
		Then I should see "Username"
		When I enter "administrator" into "username"
		And I enter "123123" into "password"
		And I click "Log in"
		Then I shouldn't see "Login failed - please try again" 
