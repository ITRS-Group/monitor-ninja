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

When /^I hover the profile$/ do
  page.find('div#profile').hover
end

When /^I hover css "([^\"]+)"$/ do |css|
  page.find(css).hover
end

Given /^I select "([^\"]+)" from the "([^\"]+)" menu$/ do |submenu, menu|

  entries = (menu.split ">").concat(submenu.split ">")
  node = page.find('body');

  entries.each do |entry|
    node = node.find(:xpath, '..').find("a[data-menu-id]", :text => entry.strip)
    node.hover
  end

  node.click

end

Given /^I hover "([^\"]+)" from the "([^\"]+)" menu$/ do |submenu, menu|

  entries = (menu.split ">").concat(submenu.split ">")
  node = page.find('body');

  entries.each do |entry|
    node = node.find(:xpath, '..').find("a[data-menu-id]", :text => entry.strip)
    node.hover
  end

end

Then /^I should see menu items:$/ do |table|
  rows = table.raw
  rows.each do |row|
    page.find('a span', :text => row[0]).visible?
  end
end

Then /^I should not see menu items:$/ do |table|
  rows = table.raw
  rows.each do |row|
    page.assert_no_selector('a span', :text => row[0])
  end
end

When /^I have the csrf token "([^\"]*)"$/ do |val|
  evaluate_script("_csrf_token = '#{val}'");
end

When /^I have no csrf token$/ do
  evaluate_script("_csrf_token = ''");
end

When /^I enter the current date and time into "([^"]*)"$/ do |sel|
  steps %Q{
    When I enter "#{Time.new().strftime('%F %T')}" into "#{sel}"
  }
end

When /^I search for "([^"]*)"$/ do |query|
	fill_in('query', :with => query)
  page.execute_script("$('#query').keyup();");
end

When /^I submit the search$/ do
  page.execute_script("$('#query').parent('form').submit();");
end

Then /^I should see the search result:$/ do |table|
  rows = table.raw
  rows.each do |row|
    page.find('.autocomplete a', :text => row[0]).visible?
  end
end

Given /^I go to the listview for (.*)$/ do |query|
    visit Op5Cucumber::NavigationHelpers.path_to("list view") + '?q=' + query
end

Then /^I should be logged in as "([^\"]+)"$/ do |user|
  page.should have_css("a[data-username=\"#{user}\"]", :visible => true)
end

Then /^I should see these strings$/ do |strings|
  rows = strings.raw
  rows.each do |string|
    steps %Q{
      Then I should see "#{string[0]}"
    }
  end
end

And /^I display raw performance data$/ do
  find(".information-performance-raw-show").click
end

Then /^I should see a warning icon in the raw performance data table$/ do
  find(".information-performance-raw td .icon-state-warning", :visible => true)
end

Then /^I should see a critical icon in the raw performance data table$/ do
  find(".information-performance-raw td .icon-state-critical", :visible => true)
end

When /^I edit widget "([^\"]+)"$/ do |widget_name|
  header = find('.widget-header', :text => widget_name)
  header.hover;
  header.find('.widget-editlink').click
end

# Wont work until we have support for flexbox in testing
Then /^I should see a dialog with title "([^\"]+)"$/ do |title|
  find(".lightbox .lightbox-header", :text => title)
end
