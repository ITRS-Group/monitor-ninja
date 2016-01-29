When /^I have these mocked (.*)$/ do |type, table|
  @mock.mock(type, table.hashes)
  page.driver.headers = {'X-op5-mock' => @mock.file}
end

When /^I am logged in$/ do
  @mock.mock("MockedClasses",
             [
               {
                 "real_class" => "op5config",
                 "mock_class" => "MockConfig",
                 "args" => {
                   "auth" => {
                     "common" => {
                       "session_key" => "auth_user",
                       "default_auth" => "Default"
                     },
                     "Default" => {
                       "driver" => "default"
                     }
                   }

                 }
               },
               {
                 "real_class" => "op5auth",
                 "mock_class" => "MockAuth",
                 "args" => {}
               },
               {
                 "real_class" => "op5MayI", "mock_class" => "MockMayI",
                 "args" => {}
               }
  ]
  )
  page.driver.headers = {'X-op5-mock' => @mock.file}
end

Then /^I should see the mocked (.*)$/ do | type |
  @mock.data(type).all? { |obj|
    if type == 'services'
      expected_content = obj['description']
    else
      expected_content = obj['name']
    end

    page.should have_content(expected_content)
  }
end

After do |scenario|
  case scenario
  when Cucumber::Ast::Scenario
    name = scenario.name
  when Cucumber::Ast::OutlineTable::ExampleRow
    name = scenario.scenario_outline.name
  end

  if @mock.active?
    if scenario.failed?
      puts "Scenario #{name} failed, mock data stored in #{@mock.file}"
    else
      @mock.delete!
    end
  end
  @mock = nil
end
