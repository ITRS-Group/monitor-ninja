#iFrame related step definitions

#Host
When /^I create a host with hostname "([^"]*)" and host address "([^"]*)"$/ do |hname, addr|
  within_frame(find('iframe')) do
    # Fill in the hostname
    fill_in 'new_host[-1][host_name]', with: hname

    # Fill in the address
    fill_in 'new_host[-1][address]', with: addr
  
    # Select an option from the dropdown
    select 'Yes', from: 'new_host[-1][Add this host?]'
  
    # Click the Add Services button
    find_button('scanBtn').click

    # Click the Finish button
    find_button('finish_submit').click
  end
end

#Hostgroup
When /^I create a host with hostgroup "([^"]*)"$/ do |hgroup|
  within_frame(find('iframe')) do
    # Fill in the hostgroup name
    fill_in 'hostgroup[new][hostgroup_name]', with: hgroup

    find('input[type="submit"][name="action"][value="Submit"]').click
  end
end

#Servicegroup
When /^I create a host with servicegroup "([^"]*)"$/ do |sgroup|
  within_frame(find('iframe')) do
    # Fill in the servicegroup name
    fill_in 'servicegroup[new][servicegroup_name]', with: sgroup

    find('input[type="submit"][name="action"][value="Submit"]').click
  end
end


#iframe saving configuration
When /^I save the changes in OP5$/ do
  find("a[href='/monitor/index.php/configuration/configure?page=export.php']").click
  within_frame(find('iframe')) do
    find('input[type="submit"][name="x"][value="Save"]').click
  end
end