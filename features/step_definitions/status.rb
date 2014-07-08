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
  all(:css, '.helptext_target', :visible => true).each { | elem |
    elem.trigger(:mouseover)
    sleep(1)
    page.should have_css(".qtip-content", :visible => true)
    # "This helptext (%s) is not translated yet" is only printed by convention, but it appears we follow it
    page.should have_no_content "This helptext"
    find(".qtip-content", :visible => true).text.length.should_not be 0
    elem.trigger(:mouseout)
    page.should have_no_css(".qtip-content", :visible => true)
  }
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