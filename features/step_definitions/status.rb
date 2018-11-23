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

Then /^I should see an icon with title "([^\"]+)"$/ do |title|
  find("span[class^=\"icon\"][title=\"#{title}\"]", :visible => true)
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

Then /^I should see a notification$/ do
  Synchronization::wait_until do
    page.all('div.notify-notification').count > 0
  end
end

Then /^waiting until I see (?:([\d]+) )?"([^"]*)"$/ do |n, string|
  Synchronization::wait_until do
    page.should have_content(string, :count => n)
  end
end


Then /^I should see (a|an) (error|info|success|warning) notification$/ do |ignore, type|
  Synchronization::wait_until do
    page.all("div.notify-notification.#{type}").count > 0
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

Then /^I should see (?:a|an) (error|info|success|warning) notification containing the text "([^\"]*)"$/ do |type, notification_text|
  page.should(have_selector("div.notify-notification.#{type}", text: /#{notification_text}/))
end

When /^I sort the filter result table by "(.*?)"$/ do |arg1|
  Synchronization::wait_until do
    page.evaluate_script('$.active') == 0
  end
  page.find("div#filter_result table thead:first-child th[data-column=#{arg1}]").trigger(:click)
end

Then /^I should see this status:$/ do |table|
  Synchronization::wait_until do
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
  Synchronization::wait_until do
    page.evaluate_script('$.active') == 0
  end
  page.all('div#filter_result table tbody tr').count == numrows.to_i
end

Then /^The (.*?) row of the filter result table should contain "(.*?)"$/ do |pos, str|
  Synchronization::wait_until do
    page.evaluate_script('$.active') == 0
  end
  within(:css, "div#filter_result table tbody tr:#{pos}-child") do
    page.should have_content(str)
  end
end

Then /^I should see trend graph have background color "([^\"]+)"$/ do |color|
  page.find("div", '[style="background: #{color};"]', :visible => true)
end