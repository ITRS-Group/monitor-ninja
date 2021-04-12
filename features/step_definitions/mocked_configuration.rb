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

When /^I am logged in as administrator$/ do

  steps %Q{
    Given I have an admins user group with all rights
  }

  username = 'administrator'
  password = '123123'

  @mock.mock('authmodules', [{
    "modulename" => "Default",
    "properties" => {
      "driver" => "Default"
    }
  }])

  @mock.mock('users', [{
    'username' => username,
    'realname' => 'Administrator',
    'password' => '$1$lrF9ydB5$G1EfpAnNTlzsrHF5My6Eg.',
    'password_algo' => 'crypt',
    'groups' => ['admins'],
    'modules' => ['Default']
  }])

  page.driver.headers = {'X-op5-mock' => @mock.file}

  steps %Q{
    When I am on the main page
    And I enter "#{username}" into "username"
    And I enter "#{password}" into "password"
    And I click "Log in"
  }

  @mock.mock_class("op5MayI", {
      "mock_class" => "MockMayI",
      "args" => {}
  })

end

# Set up an adminitrator account but do not log in.
When /^I have an administrator account$/ do
  steps %Q{
    Given I have an admins user group with all rights
  }

  username = 'administrator'
  password = '123123'

  @mock.mock('authmodules', [{
    "modulename" => "Default",
    "properties" => {
      "driver" => "Default"
    }
  }])

  @mock.mock('users', [{
    'username' => username,
    'realname' => 'Administrator',
    'password' => '$1$lrF9ydB5$G1EfpAnNTlzsrHF5My6Eg.',
    'password_algo' => 'crypt',
    'groups' => ['admins'],
    'modules' => ['Default']
  }])

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

Given /^I have no users configured$/ do
	@mock.mock('users', [])
	page.driver.headers = {'X-op5-mock' => @mock.file}
end

Given /^I have the default authentication module configured$/ do
	@mock.mock('authmodules', [
		{
			"modulename" => "Default",
			"properties" => {
				"driver" => "Default"
			}
		}
	])
	page.driver.headers = {'X-op5-mock' => @mock.file}
end

When /^I am logged in as "(.*)"$/ do |user|

	steps %Q{
		Given I have an admins user group with all rights
	}

	username = user
	password = '123123'

	@mock.mock('authmodules', [{
		"modulename" => "Default",
		"properties" => {
			"driver" => "Default"
		}
	}])

	@mock.mock('users', [{
		'username' => username,
		'realname' => username,
		'password' => '$1$lrF9ydB5$G1EfpAnNTlzsrHF5My6Eg.',
		'password_algo' => 'crypt',
		'groups' => ['admins'],
		'modules' => ['Default']
	}])

	page.driver.headers = {'X-op5-mock' => @mock.file}

	steps %Q{
		When I am on the main page
		And I enter "#{username}" into "username"
		And I enter "#{password}" into "password"
		And I click "Log in"
	}

	@mock.mock_class("op5MayI", {
		"mock_class" => "MockMayI",
		"args" => {}
	})

end

Given /^I have an (.*) user group with all rights$/ do |group|
	@mock.mock('usergroups', [
		{
			"groupname" => group,
			"rights" => [
				"system_information",
				"configuration_information",
				"system_commands",
				"api_command",
				"api_config",
				"api_report",
				"api_status",
				"host_add_delete",
				"host_view_all",
				"host_edit_all",
				"test_this_host",
				"host_template_add_delete",
				"host_template_view_all",
				"host_template_edit_all",
				"service_add_delete",
				"service_view_all",
				"service_edit_all",
				"test_this_service",
				"service_template_add_delete",
				"service_template_view_all",
				"service_template_edit_all",
				"hostgroup_add_delete",
				"hostgroup_view_all",
				"hostgroup_edit_all",
				"management_pack_add_delete",
				"management_pack_view_all",
				"management_pack_edit_all",
				"servicegroup_add_delete",
				"servicegroup_view_all",
				"servicegroup_edit_all",
				"hostdependency_add_delete",
				"hostdependency_view_all",
				"hostdependency_edit_all",
				"servicedependency_add_delete",
				"servicedependency_view_all",
				"servicedependency_edit_all",
				"hostescalation_add_delete",
				"hostescalation_view_all",
				"hostescalation_edit_all",
				"serviceescalation_add_delete",
				"serviceescalation_view_all",
				"serviceescalation_edit_all",
				"contact_add_delete",
				"contact_view_all",
				"contact_edit_all",
				"contact_template_add_delete",
				"contact_template_view_all",
				"contact_template_edit_all",
				"contactgroup_add_delete",
				"contactgroup_view_all",
				"contactgroup_edit_all",
				"timeperiod_add_delete",
				"timeperiod_view_all",
				"timeperiod_edit_all",
				"command_add_delete",
				"command_view_all",
				"command_edit_all",
				"test_this_command",
				"export",
				"configuration_all",
				"wiki",
				"wiki_admin",
				"nagvis_edit",
				"nagvis_view",
				"nagvis_add_delete",
				"nagvis_admin",
				"logger_access",
				"logger_configuration",
				"logger_schedule_archive_search",
				"FILE",
				"access_rights",
				"pnp",
				"saved_filters_global",
				"manage_trapper",
				"management_pack_view_all",
				"management_pack_edit_all",
				"management_pack_add_delete",
				"api_command",
				"manage_trapper",
				"logger_access",
				"logger_configuration",
				"logger_schedule_archive_search",
				"host_command_acknowledge",
				"host_command_add_comment",
				"host_command_schedule_downtime",
				"host_command_check_execution",
				"host_command_event_handler",
				"host_command_flap_detection",
				"host_command_notifications",
				"host_command_obsess",
				"host_command_passive_check",
				"host_command_schedule_check",
				"host_command_send_notification",
				"service_command_acknowledge",
				"service_command_add_comment",
				"service_command_schedule_downtime",
				"service_command_check_execution",
				"service_command_event_handler",
				"service_command_flap_detection",
				"service_command_notifications",
				"service_command_obsess",
				"service_command_passive_check",
				"service_command_schedule_check",
				"service_command_send_notification",
				"hostgroup_command_schedule_downtime",
				"hostgroup_command_check_execution",
				"hostgroup_command_send_notifications",
				"servicegroup_command_schedule_downtime",
				"servicegroup_command_check_execution",
				"servicegroup_command_send_notifications",
				"business_services_access",
			]
		}
	])
	page.driver.headers = {'X-op5-mock' => @mock.file}
end

Before do |scenario|
  @mock = Mock::Mock.new
end

After do |scenario|
  case scenario
  when Cucumber::Core::Ast::ScenarioOutline
    name = scenario.scenario_outline.name
  when Cucumber::Core::Ast::Scenario
    name = scenario.name
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
