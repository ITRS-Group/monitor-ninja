@csrf @configuration @asmonitor
Feature: CSRF Token
	Background:
		Given I have these hostgroups configured:
			| hostgroup_name | members                     |
			| LinuxServers   | linux-server1,linux-server2 |
			| WindowsServers | win-server1,win-server2     |
		And I have these hosts:
			| host_name     | contacts |
			| linux-server1 | monitor  |
			| linux-server2 |          |
			| win-server1   |          |
			| win-server2   |          |
		And I have these services:
			| service_description | host_name     | check_command   | notifications_enabled | active_checks_enabled |
			| System Load         | linux-server1 | check_nrpe!load | 1                     | 1                     |
			| System Load         | linux-server2 | check_nrpe!load | 1                     | 1                     |
			| PING                | win-server1   | check_ping      | 1                     | 0                     |
			| PING                | win-server2   | check_ping      | 0                     | 1                     |
		And I have activated the configuration

	Scenario: CSRF Should fail submission of POST form with invalid token

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Disable active checks"
		And I have the csrf token "invalidtokenfails"
		And I click "Submit"
		Then I should see "Forbidden"

	Scenario: CSRF Should fail submission of POST form with empty token

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Disable active checks"
		And I have the csrf token ""
		And I click "Submit"
		Then I should see "Forbidden"

	Scenario: CSRF Should fail submission of POST form with no token

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Disable active checks"
		And I have no csrf token
		And I click "Submit"
		Then I should see "Forbidden"
