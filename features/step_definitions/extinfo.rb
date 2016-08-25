When /^I toggle operating status "([^\"]+)"$/ do |toggle|
  page
    .find('div.information-cell-header', text: toggle)
    .find(:xpath, "..")
    .find('div[data-setting-toggle-command]')
    .click
end

Then /^the operating status toggle "([^\"]+)" should be active$/ do |toggle|
  toggle = page
    .find('div.information-cell-header', text: toggle)
    .find(:xpath, "..")
    .find('div[data-setting-toggle-command^="disable_"],div[data-setting-toggle-command^="stop_"]')
end

Then /^the operating status toggle "([^\"]+)" should be inactive$/ do |toggle|
  toggle = page
    .find('div.information-cell-header', text: toggle)
    .find(:xpath, "..")
    .find('div[data-setting-toggle-command^="enable_"],div[data-setting-toggle-command^="start_"]')
end

Then /^the timestamp "([^\"]+)" should show the datetime "([^\"]+)"$/ do |text, datetime|
  toggle = page
    .find('div.information-cell-header', text: text)
    .find(:xpath, "..")
    .find('div.information-cell-raw')
    .text.should eq(datetime)
end

# Don't cry over this step definition, it's there to use the same timezone as
# the one configured in PHP's ini.
Then /^the timestamp "([^\"]+)" should show the datetime for "(\d+)"$/ do |text, timestamp|
  toggle = page
    .find('div.information-cell-header', text: text)
    .find(:xpath, "..")
    .find('div.information-cell-raw')
    .text.should eq(`php -r '$tz = new DateTimeZone(date_default_timezone_get()); $dt = new DateTime("now", $tz); $dt->setTimestamp(#{timestamp}); echo $dt->format("Y-m-d H:i:s");'`.strip)
end

Then /^the timestamp "([^\"]+)" should show the relative time "([^\"]+)"$/ do |text, reltime|
  toggle = page
    .find('div.information-cell-header', text: text)
    .find(:xpath, "..")
    .find('div.information-cell-value')
    .text.should eq(reltime)
end

Then /^the object details field "([^\"]+)" should show "([^\"]+)"$/ do |text, value|
  toggle = page
    .find('div.information-cell-header', text: text)
    .find(:xpath, "..")
    .find('div.information-cell-value')
    .text.should eq(value)
end

Then /^the object details field "([^\"]+)" should match "([^\"]+)"$/ do |text, value|
  toggle = page
    .find('div.information-cell-header', text: text)
    .find(:xpath, "..")
    .find('div.information-cell-value')
    .text.should match(value)
end
