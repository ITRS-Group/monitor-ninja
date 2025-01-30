#iFrame related step definitions
When /^I create a host with hostname "([^"]*)" and host address "([^"]*)"$/ do |hname, addr|
    within_frame(find('iframe')) do
      # Fill in the hostname
      fill_in 'new_host[-1][host_name]', with: 'hname'

      # Fill in the address
      fill_in 'new_host[-1][address]', with: 'addr'
  
      # Select an option from the dropdown
      select 'Yes', from: 'new_host[-1][Add this host?]'
  
      # Click the Add Services button
      find_button('scanBtn').click

      # Click the Finish button
      find_button('finish_submit').click
    end
  end