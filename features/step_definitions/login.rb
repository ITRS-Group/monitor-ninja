Given /^I am logged in as "([^"]*)" with password "([^"]*)"$/ do |username, password|
	steps %Q{
		When I am on the main page
		And I enter "#{username}" into "username"
		And I enter "#{password}" into "password"
		And I click "Log in"
		Then I should see "Log out"
	}
end

When /^I enter "([^"]*)" into "([^"]*)"$/ do |val, sel|
    fill_in(sel % @params, :with => val)
  end