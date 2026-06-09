Given /^I am real logged in as "([^"]*)" with password "([^"]*)"$/ do |username, password|
	steps %Q{
		When I am on the main page
		And I enter real "#{username}" into "username"
		And I enter real "#{password}" into "password"
		And I click "Log in"
		Then I should see "Log out"
	}
end

When /^I enter real "([^"]*)" into "([^"]*)"$/ do |val, sel|
    fill_in(sel % @params, :with => val)
  end