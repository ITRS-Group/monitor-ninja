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
		And I click "Login"
		And I hover the profile
		And I click "My Account"
		And I click "Change Password"
		And I enter "billabong" into "current_password"
		And I enter "123123" into "new_password"
		And I enter "123123" into "confirm_password"
		And I click "Change password"
		Then I should see "Password changed successfully"
