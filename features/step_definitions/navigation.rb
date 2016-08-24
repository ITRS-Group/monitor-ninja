#
# URL stuff
#

# url should be "https://...."
Given /^I am on "(.*)"$/ do |url|
  visit url
end

Given /^I'm on the list view for query "(.*)"$/ do |query|
  visit Op5Cucumber::NavigationHelpers::path_to("list view") + '?q=' + query
end

# page_name could be "login page" etc
Given /^I am on the ([^"]*)$/ do |page_name|
  visit Op5Cucumber::NavigationHelpers.path_to(page_name)
end

# path should be "/index.php/..."
Given /^I am on address "(.*)"$/ do |path|
  visit Op5Cucumber::NavigationHelpers::url_for(path)
end

Given /^I visit the object details page for (host|hostgroup|servicegroup) "(.*)"$/ do |type, object|
  object = URI.escape(object, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
  visit Op5Cucumber::NavigationHelpers::url_for("/index.php/extinfo/details?#{type}=#{object}")
end

Given /^I visit the object details page for service "(.*)" on host "(.*)"$/ do |object, parent|
  object = URI.escape(object, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
  parent = URI.escape(parent, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
  visit Op5Cucumber::NavigationHelpers::url_for("/index.php/extinfo/details?host=#{parent}&service=#{object}")
end

Given /^I visit the alert history page for (host|service|hostgroup|servicegroup) "(.*)"$/ do |type, host|
  host = URI.escape(host, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
  visit Op5Cucumber::NavigationHelpers::url_for("/index.php/alert_history/generate?report_type=#{type}s&objects[0]=#{host}")
end

#use to include querystrings in the match
Then /^I should be on url "([^"]*)"$/ do |url|
	#prepend right op with https://localhost for matching
	current_url.should ==  Op5Cucumber::NavigationHelpers::url_for(url)
end

#use to include querystrings in the match
Then /^I should be on list view with filter '([^']*)'$/ do |filter|
  query = URI.escape(filter, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
	current_url.should ==  Op5Cucumber::NavigationHelpers::path_to("list view") + '?q=' + query
end

Then /^I should be on the (.*)$/ do |page_name|
  Op5Cucumber::NavigationHelpers::url_for(current_path).should == Op5Cucumber::NavigationHelpers::path_to(page_name)
end

Then /^I should be on address "([^"]*)"$/ do |page_name|
  Op5Cucumber::NavigationHelpers::url_for(current_path).should == Op5Cucumber::NavigationHelpers::url_for(page_name)
end
