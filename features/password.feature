Feature: Change password

	@asmonitor
	Scenario: Change password
		When I click "Monitor Admin"
		And I click "Change Password"
		And I enter "monitor" into "current_password"
		And I enter "billabong" into "new_password"
		And I enter "billabong" into "confirm_password"
		And I click "Change password"
		Then I should see "Password changed successfully"
		When I click "Log out"
		Given I am logged in as "monitor" with password "billabong"
		And I click "Monitor Admin"
		And I click "Change Password"
		And I enter "billabong" into "current_password"
		And I enter "monitor" into "new_password"
		And I enter "monitor" into "confirm_password"
		And I click "Change password"
		Then I should see "Password changed successfully"
		# ok we're done, let's just make sure the teardown worked
		When I click "Log out"
		Given I am logged in as "monitor" with password "monitor"
		Then I should see "Monitor Admin"
