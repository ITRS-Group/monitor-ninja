Feature: Alert history reports
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
		And I have these report data entries:
			| timestamp           | event_type | flags | attrib | host_name     | service_description | state | hard | retry | downtime_depth | output |
			| 2013-01-01 12:00:00 |        100 |  NULL |   NULL |               |                     |     0 |    0 |     0 |           NULL | NULL                |
			| 2013-01-01 12:00:01 |        801 |  NULL |   NULL | win-server1   |                     |     0 |    1 |     1 |           NULL | OK - laa-laa        |
			| 2013-01-01 12:00:02 |        801 |  NULL |   NULL | linux-server1 |                     |     0 |    1 |     1 |           NULL | OK - Sven Melander  |
			| 2013-01-01 12:00:03 |        701 |  NULL |   NULL | win-server1   | PING                |     0 |    1 |     1 |           NULL | OK - po             |
			| 2013-01-01 12:00:04 |        701 |  NULL |   NULL | win-server1   | PING                |     1 |    0 |     1 |           NULL | ERROR - tinky-winky |
			| 2013-01-01 12:00:05 |        701 |  NULL |   NULL | win-server1   | Swap Usage          |     1 |    0 |     1 |           NULL | ERROR - out of teletubbies |
			| 2013-01-01 12:00:02 |        801 |  NULL |   NULL | linux-server2 |                     |     0 |    1 |     1 |           NULL | PRETTY OK - Jon Skolmen |

		And I have activated the configuration

	@configuration @asmonitor
	Scenario: Single host alert history
		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Alert history"
		Then I should see "OK - Sven Melander"
		And I should see "Reporting period: Forever"
		And I shouldn't see "win-server"
		When I click "Edit settings"
		Then "Show all" should be unchecked
		And "objects" should have option "linux-server1"
		When I uncheck "Up"
		And I click "Update"
		Then I shouldn't see "Sven Melander"

	@bug-7083
	@configuration @asmonitor
	Scenario: Service with host alert history
		Given I am on the Service details page
		When I click "Swap Usage"
		And I click "Alert history"
		Then I should see "ERROR - out of teletubbies"
		And I should see "OK - laa-laa"
		And I should see "win-server"
		And I shouldn't see "linux"
		And I shouldn't see "PING"
		And I should see "Reporting period: Forever"
		When I click "Edit settings"
		Then "Show all" should be unchecked
		And "objects" should have option "win-server1;Swap Usage"
		When I uncheck "Ok"
		And I uncheck "Up"
		And I click "Update"
		Then I should see "ERROR - out of teletubbies"
		And I shouldn't see "OK - laa-laa"

	@configuration @asmonitor
	Scenario: Host with service alert history
		Given I am on the Host details page
		When I click "win-server1"
		And I click "Alert history"
		Then I should see "OK - laa-laa"
		And I should see "ERROR - tinky-winky"
		And I should see "ERROR - out of teletubbies"
		When I click "Edit settings"
		And I select "Host alerts"
		And I click "Update"
		Then I shouldn't see "ERROR - out of teletubbies"
		And I shouldn't see "ERROR - tinky-winky"
		And I should see "OK - laa-laa"

	@bug-7083
	@configuration @asmonitor
	Scenario: Switch object
		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Alert history"
		Then I should see "OK - Sven Melander"
		When I click "Edit settings"
		Then "objects_tmp" should have option "win-server1"
		And "objects" should have option "linux-server1"
		When I deselect "linux-server1" from the multiselect "objects"
		Then "objects_tmp" should have option "linux-server1"
		When I select "win-server1" from "objects_tmp"
		Then "objects" should have option "win-server1"
		When I click "Update"
		Then I should see "ERROR - out of teletubbies"
		And I should see "ERROR - tinky-winky"
		And I should see "OK - laa-laa"

	# Henrik claims I broke this once, so let's prove him wrong forever
	@configuration @asmonitor
	Scenario: Change option from all objects
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Alert History"
		Then I should see "ERROR - out of teletubbies"
		And I should see "OK - Sven Melander"
		When I click "Edit settings"
		And I uncheck "Up"
		And I click "Update"
		Then I should see "ERROR - out of teletubbies"
		And I shouldn't see "OK - Sven Melander"

	@bug-6341 @bug-6646
	@configuration @asmonitor
	Scenario: Pagination
		Given I am on the Host details page
		When I click "win-server1"
		And I click "Alert history"
		Then I should see "OK - laa-laa"
		And I should see "OK - po"
		And I should see "ERROR - tinky-winky"
		And I should see "ERROR - out of teletubbies"
		When I click "Edit settings"
		And I enter "1" into "Items to show"
		And I check "Older entries first"
		And I click "Update"
		Then I should see "OK - laa-laa"
		And I shouldn't see "OK - po"
		And I shouldn't see "ERROR - tinky-winky"
		And I shouldn't see "ERROR - out of teletubbies"
		When I click "Next"
		Then I shouldn't see "OK - laa-laa"
		And I should see "OK - po"
		And I shouldn't see "ERROR - tinky-winky"
		And I shouldn't see "ERROR - out of teletubbies"
		When I click "Next"
		Then I shouldn't see "OK - laa-laa"
		And I shouldn't see "OK - po"
		And I should see "ERROR - tinky-winky"
		And I shouldn't see "ERROR - out of teletubbies"
		When I click "Previous"
		Then I should see "OK - po"
