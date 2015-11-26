require 'uri'

Then /^I should see this status:$/ do |table|
  wait_until do
    page.evaluate_script('$.active') == 0
  end
  cols = table.transpose.raw
  cols.each do |row|
    title = row.shift
    all(:xpath, "//div[@id='filter_result']/table/tbody/tr/td[count(preceding-sibling::td) = count(../../../thead[position()=last()]/tr/th[contains(.,'" + title + "')]/preceding-sibling::th)]").each do |col|
      expected = row.shift
      col.should have_content expected
    end
    row.length.should be == 0
  end
end

Then /^the filter result table should have (\d+) rows$/ do |numrows|
  wait_until do
    page.evaluate_script('$.active') == 0
  end
  page.all('div#filter_result table tbody tr').count == numrows.to_i
end

When /^I am on the "(.*?)" listview$/ do |arg1|
  visit(path_to("list view") + "?q=" + URI::escape(arg1))
end

When /^I sort the filter result table by "(.*?)"$/ do |arg1|
  wait_until do
    page.evaluate_script('$.active') == 0
  end
  page.find("div#filter_result table thead:first-child th[data-column=#{arg1}]").trigger(:click)
end

Then /^The (.*?) row of the filter result table should contain "(.*?)"$/ do |pos, str|
  wait_until do
    page.evaluate_script('$.active') == 0
  end
  within(:css, "div#filter_result table tbody tr:#{pos}-child") do
    page.should have_content(str)
  end
end

Then /^the listview should be empty$/ do
  wait_until do
    page.evaluate_script('$.active') == 0
  end
  page.should have_content("No entries found using filter")
end

When /I select "(.*)" from the multiselect "(.*)"$/ do |option, selector|
  tmp_sel = find_field(find_field(selector)[:id].sub('[', '_tmp['))
  tmp_sel.select(option)
  page.execute_script("$('##{tmp_sel[:id].gsub('[', '\\\\\[').gsub(']', '\\\\\]')}').trigger('change');")
end

When /I deselect "(.*)" from the multiselect "(.*)"$/ do |option, selector|
  tmp_sel = find_field(selector)
  tmp_sel.select(option)
  page.execute_script("$('##{tmp_sel[:id].gsub('[', '\\\\\[').gsub(']', '\\\\\]')}').trigger('change');")
end

When /^I filter "(.*)" on "(.*)"$/ do |selector, regex|
  find(:xpath, ".//input[following-sibling::*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')][(((./@id = '#{selector}' or ./@name = '#{selector}') or ./@placeholder = '#{selector}') or ./@id = //label[normalize-space(string(.)) = '#{selector}']/@for)]]").set(regex)
end

Then /^I should see a notification$/ do
  wait_until do
    page.all('div.notify-notification').count > 0
  end
end

Then /^waiting until I see (?:([\d]+) )?"([^"]*)"$/ do |n, string|
  wait_until do
    page.should have_content(string, :count => n)
  end
end


Then /^I should see (a|an) (error|info|success|warning) notification$/ do |ignore, type|
  wait_until do
    page.all("div.notify-notification.notify-notification-#{type}").count > 0
  end
end

And /^the notification should contain "([^\"]*)"$/ do |notification_text|
  page.find('div.notify-notification').should have_content(notification_text)
end

Then /^I should see a notification containing the text "([^\"]*)"$/ do |notification_text|
  steps %Q{
    Then I should see a notification
    And the notification should contain "#{notification_text}"
  }
end

Then /^I should see (a|an) (error|info|success|warning) notification containing the text "([^\"]*)"$/ do |ignore, type, notification_text|
  steps %Q{
    Then I should see an #{type} notification
    And the notification should contain "#{notification_text}"
  }
end

When /^I reload the page$/ do
  visit current_url
end

Given /^I have PNP data for "(.+)"/ do |object|
	if object =~ /;/ then
		objs = object.split(";")
		host = objs[0]
		service = objs[1]
	else
		service = "_HOST_"
		host = object
	end
	host.gsub!(/[ :\/\\]/, '_')
	service.gsub!(/[ :\/\\]/, '_')

	FileUtils.mkdir_p("/opt/monitor/op5/pnp/perfdata/" + host)
	FileUtils.touch("/opt/monitor/op5/pnp/perfdata/" + host + "/" + service + ".xml")
end

When /^I enter the time in (\d) minutes into "(.+)"$/ do |minutes, selector|
	require('date')
	fill_in(selector, :with => (Time.now + minutes.to_i * 60).strftime('%F %T'))
end

# Because all our projects have their own helptext implementation...
Then /^all helptexts should be defined$/ do
  all(:css, '*[data-popover]', :visible => true).each { | elem |
    elem.trigger(:mouseover)
    sleep(1)
    page.should have_css(".lib-popover-tip", :visible => true)
    # "This helptext (%s) is not translated yet" is only printed by convention, but it appears we follow it
    page.should have_no_content "This helptext"
    find(".lib-popover-tip", :visible => true).text.length.should_not be 0
    elem.trigger(:mouseleave)
    page.should have_selector('.lib-popover-tip', visible: false)
  }
end

When /^I hover the branding$/ do
  page.find('a[data-menu-id="branding"]').hover
end

Then /^I should see menu items:$/ do |table|
  rows = table.raw
  rows.each do |row|
    page.find('a span', :text => row[0]).visible?
  end
end

When /^I have the csrf token "([^\"]*)"$/ do |val|
  evaluate_script("$('input[name=\"csrf_token\"]').val(\"#{val}\")");
end

When /^I have no csrf token$/ do
  evaluate_script("$('input[name=\"csrf_token\"]').remove()");
end

When /^I enter the current date and time into "([^"]*)"$/ do |sel|
  steps %Q{
    When I enter "#{Time.new().strftime('%F %T')}" into "#{sel}"
  }
end

Given /^I go to the listview for (.*)$/ do |query|
    visit path_to("list view") + '?q=' + query
end
