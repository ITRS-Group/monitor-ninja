When /^Debug$/ do
  puts current_path
  puts page.html
end

When /^I dump network resources$/ do
  page.driver.network_traffic.each { | request |
    puts "#{request.method} #{request.url} => \n" + request.response_parts.map { | response | "\t#{response.status} #{response.status_text}" }.join("\n")
  }
end

When /^Screenshot "([^"]*)"$/ do |fname|
  if ENV['CUKE_SCREEN_DIR']
    screen_dir = ENV['CUKE_SCREEN_DIR']
  else
    screen_dir = './screenshots'
  end
  Dir::mkdir(screen_dir) if not File.directory?(screen_dir)
  screenshot = File.join(screen_dir, fname)
  screenshot_embed_filename = screen_dir + '/' + fname
  page.driver.render(screenshot, :full => true)
  embed screenshot_embed_filename, 'image/png'
end

# This will not work from buildbot slaves - run your tests locally.
# To debug, connect with chrome to http://<vm_ip>:9664
When(/^I let someone remote-debug$/) do
  page.driver.debug
end

#
# URL stuff
#

# url should be "https://...."
Given /^I am on "(.*)"$/ do |url|
  visit url
end

Given /^I'm on the list view for query "(.*)"$/ do |query|
  visit path_to("list view") + '?q=' + query
end

# page_name could be "login page" etc
Given /^I am on the ([^"]*)$/ do |page_name|
  visit path_to(page_name)
end

# path should be "/monitor/index.php/..."
Given /^I am on address "(.*)"$/ do |path|
  visit url_for(path)
end

# What is this, and why is it here? Logserver?
Given /^these log messages have been sent:$/ do |table|
  #symbolize keys for easier interfacing with LogEntry
  table_symkeys = table.hashes.map{|hash|
    hash.inject({}){|memo, (k,v)|
    memo[k.downcase.to_sym] = v
    memo
    }
  }
  entries = table_symkeys.map{|msg_hash|
    LogEntry.new msg_hash
  }
  syslog = SimpleSyslog.new
  syslog.log entries
end

When /^I spider "([^"]+)"/ do |page_name|
  spider(path_to(page_name))
end

When /^I hover over "(.*)"$/ do |element|
	page.find(:css, "[title=\"#{element}\"], [id=\"#{element}\"], [label=\"#{element}\"]").hover
end

# This should be removed when MON-7582 (new menus, the only thing
# requiring this) is merged.
When /^I hover over the "(.*)" button$/ do |element|
	page.find("##{element.downcase}-button").hover
end

#use to include querystrings in the match
Then /^I should be on url "([^"]*)"$/ do |url|
	#prepend right op with https://localhost for matching
	current_url.should ==  url_for(url)
end

Then /^I should be on the (.*)$/ do |page_name|
  url_for(current_path).should == path_to(page_name)
end

Then /^I should be on address "([^"]*)"$/ do |page_name|
  url_for(current_path).should == url_for(page_name)
end

#
# Navigating around forms and pages
#

When /^I doubleclick "([^"]*)" from "([^"]*)"$/ do |opt, sel|
  # if we're using qtwebkit, sending a doubleclick is easy.
  # but, of course, we're not. balls.
  sel = sel.gsub('[', '\\\[').gsub(']', '\\\]')
  script = "$('select[id=\\'#{sel}\\']').find('option:contains(#{opt})').dblclick()"
  page.execute_script script
end

When /^I doubleclick "([^"]*)"$/ do |opt|
  find('*', :text => opt).trigger(:dblclick)
end

When /^I click the delete icon for comment (\d+)$/ do |comment_id|
	id = "delete_comment_#{comment_id}"
	page.find(:xpath, "id('#{id}')").trigger(:click)
end

When /^I click link "([^"]*)"$/ do |link|
  click_link link
end

When /^I click button "([^"]*)"$/ do |button|
  click_button(button)
end

When /^I click "([^"]*)"$/ do |id|
  click_on id
end

When /^I check "([^"]*)"$/ do |id|
  check(id)
end

When /^I uncheck "([^"]*)"$/ do |id|
  uncheck(id)
end

When /^I select "([^"]*)"$/ do |opt|
  select(opt)
end

When /^I choose "([^"]*)"$/ do |opt|
  choose(opt)
end

When /^I deselect options in "([^"]*)"$/ do |sel|
  find(:xpath, XPath::HTML.select(sel)).all('option').each do |opt|
    if opt.selected? then
      opt.unselect_option
    end
  end
end


When /^I select "(.*)" from "([^"]*)"$/ do |opt, sel|
  select(opt, :from => sel)
end

When /^I click xpath "([^"]*)"$/ do |xp|
  find(:xpath, xp).click
end

When /^I click css "([^"]*)"$/ do |xp|
  find(:css, xp).click
end

When /^I save (?:the )value of "([^"]*)" as "([^"]*)"$/ do |sel, var|
  @params[var.to_sym] = find("input[name=#{sel}]", :visible => false).value
end

When /^I enter "([^"]*)" into "([^"]*)"$/ do |val, sel|
  fill_in(sel % @params, :with => val)
end

When(/^I attach "(.*?)" to "(.*?)"$/) do |filename, sel|
  attach_file(sel, filename)
end

When /^I note the value of "(.*)"$/ do |field|
	@remembered = Hash.new
	@remembered[field.to_sym] = find_field(field).value
end

Then /^"(.*?)" should be shown as the value of "(.*?)"$/ do |key, remembered_value|
	ext_info_table_lookup(key).should eq @remembered[remembered_value.to_sym]
end

Then /^"(.*?)" should be shown as "(.*?)"$/ do |key, value|
	ext_info_table_lookup(key).should include value
end

When /^I hover over the "(.*)" menu$/ do |element|
  page.find('a span', :text => element, :match => :prefer_exact, :visible => true).hover
end

Then /^I should see these menu items:$/ do |table|
  rows = table.raw
  rows.each do |row|
    page.should have_link(row[0])
  end
end

#
# Text matching in output
#
Then /^I should see the "(.*)" table$/ do |tablename|
  page.has_table? tablename
end

Then /^I should see (?:([\d]+) )?"([^"]*)"$/ do |n, string|
  page.should have_content(string, :count => n)
end

Then /^I should see "([^"]*)", compensating for DST$/ do |string|
  #Ruby has a retarded stdlib which doesn't support manipulating datetimes
  #(timedeltas, anyone?) properly, so enjoy this filthy, filthy hack.
  #
  #NOTE: Only works for strings of the form Nd, where N is some integer between
  # 1 and 31, inclusive. FML.
  days = DateTime.strptime(string,"%dd").day
  was_dst = (DateTime.now - days).to_time.dst?
  is_dst = DateTime.now.to_time.dst?
  if was_dst and not is_dst then
    #plus one hour
    string = string + " 1h"
  elsif is_dst and not was_dst
    #minus one hour
    days = days - 1
    string = days.to_s + "d 23h"
  end
  page.should have_content(string)
end

Then /^I shouldn't see "([^"]*)"$/ do |string|
  page.should_not have_content(string)
end

Then /^I should see regex "([^"]*)"$/ do |regex|
  /#{regex}/.should match page.document.text
end

Then /^I shouldn't see regex "([^"]*)"$/ do |regex|
  /#{regex}/.should_not match page.document.text
end

Then /^I should see (?:([\d]+) )?link "([^"]*)"$/ do |n, string|
  page.should have_link(string, :count => n)
end

Then /^I should see button "([^"]*)"$/ do |selector|
  page.should have_button(selector)
end

Then /^I shouldn't see button "([^"]*)"$/ do |selector|
	page.should have_no_button(selector)
end

Then /^I should see (?:([\d]+) )?xpath "([^"]*)"$/ do |n, selector|
  page.should have_selector(:xpath, selector, :count => n)
end

Then /^I should see (?:([\d]+) )?css "([^"]*)"$/ do |n, selector|
  page.should have_css(selector, :count => n)
end

Then /^I shouldn't see css "([^"]*)"$/ do |selector|
  page.should_not have_css(selector)
end

Then /^Link "([^"]*)" should contain "(.*)"$/ do |linkel, value|
  find_link(linkel).text.strip.should =~ /#{value}/
end

Then /^"([^"]*)" should contain "(.*)"$/ do |element, value|
  find_field(element % @params).value.strip.should == value
end

Then /^css "([^"]*)" should contain "(.*)"$/ do |element, value|
  find(element).value.strip.should == value
end

Then /^"([^"]*)" shouldn't contain "(.*)"$/ do |element, value|
  find_field(element).value.strip.should_not == value
end

Then /^"([^"]*)" should contain regex "(.*)"$/ do |element, value|
	expect(find_field(element).value).to match(/#{value}/)
end

Then /^"([^"]*)" shouldn't contain regex "(.*)"$/ do |element, value|
	expect(find_field(element).value).to_not match(/#{value}/)
end

Then /^"([^"]*)" should be checked$/ do |id|
  page.should have_checked_field(id)
end

Then /^"([^"]*)" should be unchecked$/ do |id|
  page.should have_unchecked_field(id)
end

Then /^"([^"]*)" should be selected from "([^"]*)"$/ do |opt, sel|
  page.should have_select(sel, :selected => opt)
end

Then /^"([^"]*)" shouldn't be selected from "([^"]*)"$/ do |opt, sel|
  page.should have_no_select(sel, :selected => opt)
end

Then /^"([^"]*)" should have option "(.*)"$/ do |sel, opt|
  page.should have_select(sel, :with_options => [opt])
end

Then /^"([^"]*)" shouldn't have option "(.*)"$/ do |sel, opt|
  page.should_not have_select(sel, :with_options => [opt])
end

Then /^"([^"]*)" should be enabled$/ do |sel|
  page.should have_field(sel, :disabled => false)
end

Then /^"([^"]*)" should be disabled$/ do |sel|
  page.should have_field(sel, :disabled => true)
end

Then /^the response code should be "(\d+)"$/ do |status|
  page.status_code.should == status.to_i
end

# WARNING: should be used with the row selector in the hacks section below, such as
#   Then the "flubb" column should be "boing" on the row where "moggie" is "yes"
Then /^the "(.*?)" column should be "([^"]*?)"$/ do |header, value|
  should have_xpath("./td[contains(.,'" + value + "') and count(preceding-sibling::td) = count(../../../thead[position()=last()]/tr/th[contains(.,'" + header + "')]/preceding-sibling::th)]")
end

Then /^there should be a ([^ ]+) shield$/ do |color|
  should have_xpath("./td/span[@title='#{color}']")
end

#
# Misc hacks
#
Before('@APS') do
	steps %Q{
		Given I exec "mkdir -p /etc/sysctl.d"
		Then I should get exitcode "0"
		Given I exec "touch /etc/sysctl.d/99-op5.conf"
		Then I should get exitcode "0"
	}
end

After('@APS') do
	steps %Q{
		Given I exec "rm -rf /etc/sysctl.d"
		Then I should get exitcode "0"
	}
end

When /^(.*) on the row where "(.*?)" is "(.*?)"$/ do |action, header, value|
  within(:xpath, "//table[not(contains(@class, 'setup-tbl'))]/tbody/tr[./td[contains(.,'" + value + "') and count(preceding-sibling::td) = count(../../../thead[position()=last()]/tr/th[contains(.,'" + header + "')]/preceding-sibling::th)]]") do
    step action
  end
end

When /^(.*) waiting patiently$/ do |action|
  # Needed by nacoma, because it's infinitely slow
  using_wait_time(20) do
    step action
  end
end

Then /^(?:I )?wait for "?(\d+)"? seconds?$/ do | arg1 |
	sleep(Integer(arg1))
end

When /^I click "(.*?)" inside "(.*?)"$/ do |arg1, arg2|
  within(arg2) do
    click_on arg1
  end
end

When /^(.*) within frame "(.*?)"$/ do |action, arg2|
  within_frame(arg2) do
    step action
  end
end

When /^(.*) within "(.*?)"$/ do |action, arg2|
  within(arg2) do
    step action
  end
end
#
# Deprecated
#

When /^I accept alert$/ do
  # poltergeist always returns true for confirm, so it's a noop
  # page.driver.browser.switch_to.alert.accept
end

When /^I confirm popup$/ do
  # found at http://stackoverflow.com/a/5976160/49879
  # poltergeist always returns true for confirm, so it's a noop
  #page.driver.browser.switch_to.alert.accept
end

When /^I execute javascript "([^"]+)"$/ do |js|
  page.evaluate_script(js)
end
