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

# path should be "/monitor/index.php/..."
Given /^I am on address "(.*)"$/ do |path|
  visit Op5Cucumber::NavigationHelpers::url_for(path)
end

#use to include querystrings in the match
Then /^I should be on url "([^"]*)"$/ do |url|
	#prepend right op with https://localhost for matching
	current_url.should ==  Op5Cucumber::NavigationHelpers::url_for(url)
end

Then /^I should be on the (.*)$/ do |page_name|
  Op5Cucumber::NavigationHelpers::url_for(current_path).should == Op5Cucumber::NavigationHelpers::path_to(page_name)
end

Then /^I should be on address "([^"]*)"$/ do |page_name|
  Op5Cucumber::NavigationHelpers::url_for(current_path).should == Op5Cucumber::NavigationHelpers::url_for(page_name)
end
