#
# URL stuff
#

# url should be "https://...."
Given /^I am on "(.*)"$/ do |url|
	visit url
end

Given /^I'm on the list view for query "(.*)"$/ do |query|
	visit NavigationHelpers::path_to("list view") + '?q=' + query
end

# duplicate from op5license, alias of "I'm on the list view for query"
Given /^I go to the listview for (.*)$/ do |query|
	visit NavigationHelpers::path_to("list view") + '?q=' + query
end

# page_name could be "login page" etc
Given /^I am on the ([^"]*)$/ do |page_name|
	visit NavigationHelpers.path_to(page_name)
end

# path should be "/index.php/..."
Given /^I am on address "(.*)"$/ do |path|
	visit NavigationHelpers::url_for(path)
end

Given /^I visit the process information page$/ do
	visit NavigationHelpers::url_for("/index.php/extinfo/show_process_info")
end

Given /^I visit the object details page for (host|hostgroup|servicegroup) "(.*)"$/ do |type, object|
	object = URI.escape(object, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
	visit NavigationHelpers::url_for("/index.php/extinfo/details?#{type}=#{object}")
end

Given /^I visit the object details page for service "(.*)" on host "(.*)"$/ do |object, parent|
	object = URI.escape(object, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
	parent = URI.escape(parent, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
	visit NavigationHelpers::url_for("/index.php/extinfo/details?host=#{parent}&service=#{object}")
end

Given /^I visit the alert history page for (host|service|hostgroup|servicegroup) "(.*)"$/ do |type, host|
	host = URI.escape(host, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
	visit NavigationHelpers::url_for("/index.php/alert_history/generate?report_type=#{type}s&objects[0]=#{host}")
end

#use to include querystrings in the match
Then /^I should be on url "([^"]*)"$/ do |url|
	#prepend right op with https://localhost for matching
	current_url.should ==  NavigationHelpers::url_for(url)
end

#use to include querystrings in the match
Then /^I should be on list view with filter '([^']*)'$/ do |filter|
	query = URI.escape(filter, Regexp.new("[^#{URI::PATTERN::UNRESERVED}]"))
	current_url.should ==  NavigationHelpers::path_to("list view") + '?q=' + query
end

Then /^I should be on the (.*)$/ do |page_name|
	NavigationHelpers::url_for(current_path).should == NavigationHelpers::path_to(page_name)
end

Then /^I should be on address "([^"]*)"$/ do |page_name|
	NavigationHelpers::url_for(current_path).should == NavigationHelpers::url_for(page_name)
end

Then /^I view a "([^\"]+)" report with these settings:$/ do |report, table|
	data = table.hashes
	params = ''
	data.each do |row|
		row.each do |key, value|
			if key.eql? "objects"
				value_objects = value.split(",")
					if value_objects.length > 1
						value_objects.each {|v|
							params << "#{key}[]=#{v}&"
						}
					else
						params << "#{key}[]=#{value}&"
					end
			elsif key.eql? "months"
				value_objects = value.split(",")
					if value_objects.length > 1
						value_objects.each_with_index {|v,i|
							params << "#{key}[#{i+1}]=#{v}&"
						}
					else
						params << "#{key}[1]=#{value}&"
					end
			else
				params << "#{key}=#{value}&"
			end
		end
	end
	params << "with_chrome=1"
	params = params.chomp('&')
	visit NavigationHelpers::url_for("/index.php/#{report}/edit_settings?#{params}")
end
