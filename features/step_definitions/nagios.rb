Given /^I have (\d+) services configured on host "(.*?)"$/ do |num_services, host_name|
  service_hashes = []
  num_services.to_i.times { |service_n|
	service_hashes.push({
		"service_description" => "my-service%d" % service_n,
		"host_name" => host_name,
		"check_command" => "check_ping" #or something, I dunno
	})
  }
  @configuration["service"] = service_hashes
end

Given /^I have these ([^ ]+)(?: configured)?:$/ do |type, table|
  type.chomp!('s')
  @configuration[type] = table.hashes
end

Given /^I have these report data entries:$/ do |table|
  table.map_column!('timestamp') { | timestamp |
    DateTime.parse(timestamp, '%F %T').strftime('%s') rescue timestamp
  }
  insert_sql_data_into_table("merlin", "report_data", table);
end

When /^I have submitted a passive ([a-z]+) check result "(.*)"$/ do |type, check_result|
  if type == 'host' then
    @configuration.add_host_check_result check_result
  else
    @configuration.add_service_check_result check_result
  end
  @configuration.submit_passive_check_results
end

When /^I have host "(.*?)" in downtime$/ do |host|
  @configuration.set_host_in_downtime host
end

When /^I have service "(.*?)" in downtime$/ do |service|
  @configuration.set_service_in_downtime service
end

Then /^I should see the configured (.*)?$/ do |obj_type|
  obj_type.chomp!('s')
  @configuration.objects[obj_type].all? {
    |obj| page.should have_content(obj.name)
  }
end

And /^I have activated the configuration$/ do
  #XXX: This is a bit cheesy, but I haven't been able to find a way to
  # inject code between the execution of the background and the actual
  # scenario. I probably just haven't looked hard enough...?
  @configuration.activate
end
