Given /^I login to op5config with password "([^"]*)"$/ do |password|
	steps %Q{
		When I am on address "/op5config/"
		And I enter "#{password}" into "password"
		And I click "Login"
	}
end

Given /^I am logged in as "([^"]*)" with password "([^"]*)"$/ do |username, password|
	steps %Q{
		When I am on the main page
		And I enter "#{username}" into "username"
		And I enter "#{password}" into "password"
		And I click "Login"
		Then I should see "Log out"
	}
end

Given /^I have a license with the following contents:$/ do |table|
	values = table.hashes.map { |val| val[:type] + ' ' + val[:value] }
	command = '/opt/op5sys/lib/mock_license.php ' + values.join(' ')
	`#{command} 2>&1`
end

Given(/^I am logged in with the "(.*?)" group$/) do |group|
	name = (0...6).map { ('a'..'z').to_a[rand(26)] }.join
	`/usr/bin/op5-manage-users --update --username=#{name} --password=#{name} --module=Default --group=#{group} 2>&1`
	step "I am logged in as \"#{name}\" with password \"#{name}\""
end
