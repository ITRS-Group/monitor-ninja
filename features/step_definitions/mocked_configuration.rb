When /^I have these mocked (.*)$/ do |type, table|
  @mock.mock(type, table.hashes)
  page.driver.headers = {'X-op5-mock' => @mock.filename}
end
