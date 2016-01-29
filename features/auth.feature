@menu
Feature: Auth

	@enable_get_login
	Scenario: I can login through GET variables, and stay logged in
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=monitor&password=monitor"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info"
		Then I should see "Notifications enabled?"

	@enable_get_login
	Scenario: I can't login through GET variables with invalid password
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=monitor&password=invalid"
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
		When I am on address "/index.php/extinfo/show_process_info?username=monitor&password=monitor&auth_method=Default"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info"
		Then I should see "Notifications enabled?"

	@enable_get_login
	Scenario: I can login through GET variables definingn auth_method embedded in username
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=monitor$Default&password=monitor"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info"
		Then I should see "Notifications enabled?"

	@enable_get_login
	Scenario: I can't login through GET variables with invalid auth_method
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=monitor&password=monitor&auth_method=invalid"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=monitor$invalid&password=monitor"
		Then I shouldn't see "Notifications enabled?"

	@enable_get_login
	Scenario: I can't login using GET variables when already logged in
		When I am on address "/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=monitor&password=monitor"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info"
		Then I should see "Notifications enabled?"
		When I am on address "/index.php/extinfo/show_process_info?username=invalid&password=invalid"
		Then I should see "Notifications enabled?"
