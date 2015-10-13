@search
Feature: Search
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

	@configuration @asmonitor
	Scenario: Global search autocomplete default behaviour
		Given I am on the Host details page
		And I enter "win" into "query"
		Then waiting until I see "win-server2"

	@configuration @asmonitor
	Scenario: Global search autocomplete colon filter behaviour
		Given I am on the Host details page
		And I enter "h:win" into "query"
		Then waiting until I see "win-server2"
		And I enter "h:win AND s:PING" into "query"
		Then waiting until I see "win-server2;PING"