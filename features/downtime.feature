Feature: Schedule recurring downtime
	Test that you can schedule a recurring downtime

	Background:
		Given I have these hostgroups configured:
			| hostgroup_name |
			| LinuxServers   |
			| WindowsServers |
			| MixedGroup     |
			| EmptyGroup     |
		And I have these hosts:
			| host_name      | host_groups               |
			| linux-server1  | LinuxServers,MixedGroup   |
			| linux-server2  | LinuxServers              |
			| win-server1    | WindowsServers            |
			| win-server2    | WindowsServers,MixedGroup |
		And I have these servicegroups:
			| servicegroup_name | alias                           |
			| pings             | ping services plus one non-ping |
			| empty             | nothing in here                 |
		And I have these services:
			| service_description | host_name     | check_command   | notifications_enabled | active_checks_enabled | service_groups |
			| System Load         | linux-server1 | check_nrpe!load | 1                     | 1                     |                |
			| PING                | linux-server1 | check_ping      | 1                     | 0                     | pings          |
			| System Load         | linux-server2 | check_nrpe!load | 1                     | 1                     |                |
			| PING                | win-server1   | check_ping      | 1                     | 0                     | pings          |
			| Swap Usage          | win-server1   | check_swap      | 1                     | 0                     | pings          |
			| PING                | win-server2   | check_ping      | 0                     | 1                     | pings          |
		And I have activated the configuration

	@configuration @asmonitor @recurringdowntime
	Scenario: Save recurring downtime, don't schedule same day
		Given I am on the Host details page
		And I hover over the "Monitoring" button
		When I click "Recurring Downtime"
		Then I should see "No entries found using filter"
		Then I click "New"
		Then I select "Services" from "report_type"
		When I select "linux-server1;PING" from the multiselect "objects_tmp"
		Then "objects" should have option "linux-server1;PING"
		Then I enter "Daily downtime for linux-server1" into "comment"
		Then I click "select-all-days"
		Then I click "select-all-months"
		Then I click "Add Schedule"
		Then I click "Cancel"
		Then I should see "linux-server1;PING"
