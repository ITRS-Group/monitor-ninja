@menu
Feature: Auth
	Background:
		Given I have an administrator account

	@enable_get_login
	Scenario: I can login through GET variables, and stay logged in
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=administrator&password=123123"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info"
		Then I should see "Notifications enabled?"

	@enable_get_login
	Scenario: I can't login through GET variables with invalid password
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=administrator&password=invalid"
		Then I shouldn't see "Notifications enabled?"

	@enable_get_login
	Scenario: I can't login through GET variables with invalid username and password
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=invalid&password=invalid"
		Then I shouldn't see "Notifications enabled?"

	@enable_get_login
	Scenario: I can login through GET variables definingn auth_method
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=administrator&password=123123&auth_method=Default"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info"
		Then I should see "Notifications enabled?"

	@enable_get_login
	Scenario: I can login through GET variables definingn auth_method embedded in username
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=administrator$Default&password=123123"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info"
		Then I should see "Notifications enabled?"

	@enable_get_login
	Scenario: I can't login through GET variables with invalid auth_method
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=administrator&password=123123r&auth_method=invalid"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=administrator$invalid&password=123123"
		Then I shouldn't see "Notifications enabled?"

	@enable_get_login
	Scenario: I can't login using GET variables when already logged in
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=administrator&password=123123"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=invalid&password=invalid"
		Then I should see "Notifications enabled?"

	Scenario: I can only reach the login form on 404 if not logged in
		When I am on a non existing page
		Then I should see "Username"
		And I should see "Password"

	Scenario: I can see the full menu on 404 while logged in
		Given I am logged in as administrator
		When I am on a non existing page
		Then I hover "All Dashboards" from the "Dashboards" menu
		And I hover "Hosts" from the "Monitor" menu
		And I hover "Availability" from the "Report" menu
		And I hover "Configure" from the "Manage" menu

	@addedhappypath
	Scenario: I can't login through GET variables with missing username
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?password=123123"
		Then I shouldn't see "Notifications enabled?"
	
	@addedhappypath
	Scenario: I can't login through GET variables with missing password
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=administrator"
		Then I shouldn't see "Notifications enabled?"