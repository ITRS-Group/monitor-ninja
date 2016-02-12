When /^I have these mocked (.*)$/ do |type, table|
  @mock.mock(type, table.hashes)
  page.driver.headers = {'X-op5-mock' => @mock.file}
end

When /^these authpoints are denied$/ do |table|
  @mock.mock_class("op5auth", {
      "mock_class" => "MockAuth",
      "args" => { "denied_authpoints" => table.hashes[0].values}
    })
end

When /^these actions are denied$/ do |table|
  denied_actions = {}
  table.hashes.each {|h|
    denied_actions[h["action"]] = {"message" => h["message"]}
  }
  @mock.mock_class("op5MayI", {
    "mock_class" => "MockMayI",
    "args" =>
    {
      "denied_actions" => denied_actions
    }
  })
end

When /^I am logged in as admin$/ do


end

When /^I am logged in$/ do
  @mock.mock('authmodules', [{
      "modulename" => "Default",
      "properties" => {
           "driver" => "Default"
      }
  }])

  @mock.mock_class("op5auth", {
      "mock_class" => "MockAuth",
      "args" => {}
  })

  @mock.mock_class("op5MayI", {
      "mock_class" => "MockMayI",
      "args" => {}
  })

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

Before do |scenario|
  @mock = Op5Cucumber::Mock::Mock.new
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
      puts "mock data stored in #{@mock.file}"
    else
      @mock.delete!
    end
  end
  @mock = nil
end
