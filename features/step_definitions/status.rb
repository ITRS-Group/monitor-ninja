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
  page.find('a[title="branding"]').hover
end

When /^I reload the page$/ do
  visit current_url
  # page.execute_script("document.location.reload();")
end

Then /^I should see menu items:$/ do |table|
  rows = table.raw
  rows.each do |row|
    page.find('a span', :text => row[0]).visible?
  end
end