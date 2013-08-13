Given /^I have PNP data for "(.+)"/ do |object|
	if object =~ /;/ then
		objs = object.split(";")
		host = objs[0]
		service = objs[1]
	else
		service = "_HOST_"
		host = object
	end
	host.gsub!(/[ :\/\\\]/, '_')
	service.gsub!(/[ :\/\\\]/, '_')

	FileUtils.mkdir_p("/opt/monitor/op5/pnp/perfdata/" + host)
	FileUtils.touch("/opt/monitor/op5/pnp/perfdata/" + host + "/" + service + ".xml")
end

Then /^I should see this status:$/ do |table|
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

# Because all our projects have their own helptext implementation...
Then /^all helptexts should be defined$/ do
  all(:css, '.helptext_target', :visible => true).each { | elem |
    elem.click
    page.should have_css(".qtip-content", :visible => true)
    # "This helptext (%s) is not translated yet" is only printed by convention, but it appears we follow it
    page.should have_no_content "This helptext"
    # Hide helptext - only doable by clicking elsewhere
    page.find(".logo").click
    page.should have_no_css(".qtip-content", :visible => true)
  }
end
