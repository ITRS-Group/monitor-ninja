Given /^I am logged in as real admin$/ do
	steps %Q{
		When I am at the login screen
		And I enter "admin1" into "username"
		And I enter "123123" into "password"
		And I click "Log in"
		Then I hover the profile
		And I should see "Log out"
	}
	step %Q|I enter nacoma|
end