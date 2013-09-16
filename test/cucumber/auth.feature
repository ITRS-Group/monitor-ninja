@menu
Feature: Auth

	@enable_get_login
	Scenario: I can login through GET variables
		When I am on address "/monitor/index.php/extinfo/show_process_info"
		Then I shouldn't see "Notifications enabled?"
		When I am on address "/monitor/index.php/extinfo/show_process_info?username=monitor&password=monitor"
		Then I should see "Notifications enabled?"
