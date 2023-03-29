Feature: Alert history reports
	Background:
		Given I have these mocked hostgroups
			| name           |
			| LinuxServers   |
			| WindowsServers |
			| MixedGroup     |
			| EmptyGroup     |
		And I have these mocked hosts
			| name           | groups                    |
			| linux-server1  | LinuxServers,MixedGroup   |
			| linux-server2  | LinuxServers              |
			| win-server1    | WindowsServers            |
			| win-server2    | WindowsServers,MixedGroup |
		And I have these mocked servicegroups
			| name  | alias                           |
			| pings | ping services plus one non-ping |
			| empty | nothing in here                 |
		And I have these mocked services
			| description | host          | check_command   | notifications_enabled | active_checks_enabled | groups |
			| System Load | linux-server1 | check_nrpe!load | 1                     | 1                     |        |
			| PING        | linux-server1 | check_ping      | 1                     | 0                     | pings  |
			| System Load | linux-server2 | check_nrpe!load | 1                     | 1                     |        |
			| PING        | win-server1   | check_ping      | 1                     | 0                     | pings  |
			| Swap Usage  | win-server1   | check_swap      | 1                     | 0                     | pings  |
			| PING        | win-server2   | check_ping      | 0                     | 1                     | pings  |
		And I have these report data entries:
			| timestamp           | event_type | flags | attrib | host_name     | service_description | state | hard | retry | downtime_depth | output                     |
			| 2013-01-01 12:00:00 |        100 |  NULL |   NULL |               |                     |     0 |    0 |     0 |           NULL | NULL                       |
			| 2013-01-01 12:00:01 |        801 |  NULL |   NULL | win-server1   |                     |     0 |    1 |     1 |           NULL | OK - laa-laa               |
			| 2013-01-01 12:00:02 |        801 |  NULL |   NULL | linux-server1 |                     |     0 |    1 |     1 |           NULL | OK - Sven Melander         |
			| 2013-01-01 12:00:03 |        701 |  NULL |   NULL | win-server1   | PING                |     0 |    1 |     1 |           NULL | OK - po                    |
			| 2013-01-01 12:00:04 |        701 |  NULL |   NULL | win-server1   | PING                |     1 |    0 |     1 |           NULL | ERROR - tinky-winky        |
			| 2013-01-01 12:00:05 |        701 |  NULL |   NULL | win-server1   | Swap Usage          |     1 |    0 |     1 |           NULL | ERROR - out of teletubbies |
			| 2013-01-01 12:00:02 |        801 |  NULL |   NULL | linux-server2 |                     |     0 |    1 |     1 |           NULL | PRETTY OK - Jon Skolmen    |
		And I am logged in
		And I am on the main page
		And I check for cookie bar

	@configuration
	Scenario: Single host alert history
		Given I visit the alert history page for host "linux-server1"
		And I have these additional report data entries on current timestamp:
			| timestamp           | event_type | flags | attrib | host_name     | service_description | state | hard | retry | downtime_depth | output			|
			| 2023-01-01 12:00:01 |        801 |  NULL |   NULL | win-server1   |                     |     0 |    1 |     1 |           NULL | OK - Alpha		|
			| 2023-01-01 12:00:02 |        801 |  NULL |   NULL | linux-server1 |                     |     0 |    1 |     1 |           NULL | OK - Bravo		|
			| 2023-01-01 12:00:03 |        701 |  NULL |   NULL | win-server1   | PING                |     0 |    1 |     1 |           NULL | OK - Charlie    |
			| 2023-01-01 12:00:04 |        701 |  NULL |   NULL | win-server1   | PING                |     1 |    0 |     1 |           NULL | ERROR - Mike    |
		Then I should see "Reporting period: Today"
		And I should see "OK - Bravo"
		And I shouldn't see "win-server"

	@configuration
	Scenario: See that host edit settings form content rendered correct
		When I view a "alert_history" report with these settings:
		| report_type    | objects       | report_period	|
		| hosts          | linux-server1 | forever			|
		Then "Show all" should be unchecked
		And "objects" should have option "linux-server1"
		When I uncheck "Up"
		And I click "Update"
		Then I shouldn't see "Sven Melander"

	@configuration
	Scenario: Service with host alert history
		Given I visit the alert history page for service "win-server1;Swap Usage"
		Then I should see "ERROR - out of teletubbies"
		And I should see "OK - laa-laa"
		And I should see "win-server"
		And I shouldn't see "linux"
		And I shouldn't see "PING"
		And I should see "Reporting period: Forever"

	@configuration
	Scenario: See that service edit settings form content rendered correct
		When I view a "alert_history" report with these settings:
		| report_type    | objects                |
		| services       | win-server1;Swap Usage |
		Then "Show all" should be unchecked
		And "objects" should have option "win-server1;Swap Usage"
		When I uncheck "Ok"
		And I uncheck "Up"
		And I click "Update"
		Then I should see "ERROR - out of teletubbies"
		And I shouldn't see "OK - laa-laa"

	@configuration
	Scenario: Host with service alert history
		Given I visit the alert history page for host "win-server1"
		Then I should see "OK - laa-laa"
		And I should see "ERROR - tinky-winky"
		And I should see "ERROR - out of teletubbies"

	@configuration
	Scenario: See that host with service edit settings form content rendered correct
		When I view a "alert_history" report with these settings:
		| report_type    | objects     |
		| hosts          | win-server1 |
		And I uncheck "Ok"
		And I uncheck "Warning"
		And I uncheck "Critical"
		And I uncheck "Unknown"
		And I click "Update"
		Then I shouldn't see "ERROR - out of teletubbies"
		And I shouldn't see "ERROR - tinky-winky"
		And I should see "OK - laa-laa"

	@configuration
	Scenario: Switch object
		Given I visit the alert history page for host "linux-server1"
		Then I should see "OK - Sven Melander"

	@configuration
	Scenario: See that switch object edit settings form content rendered correct
		When I view a "alert_history" report with these settings:
		| report_type    | objects       |
		| hosts          | linux-server1 |
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
	@configuration
	Scenario: Change option from all objects
		Given I am on the Host details page
		And I hover over the "Report" menu
		When I click "Alert history"
		Then I should see "ERROR - out of teletubbies"
		And I should see "OK - Sven Melander"

	@configuration
	Scenario: See that option from all object edit settings form content rendered correct
		When I am on address "/index.php/alert_history/edit_settings?with_chrome=1"
		And I uncheck "Up"
		And I click "Update"
		Then I should see "ERROR - out of teletubbies"
		And I shouldn't see "OK - Sven Melander"

	@configuration
	Scenario: Changes to start and end times are properly updated
		Given I am on the Host details page
		And I hover over the "Report" menu
		Then I click "Alert history"
		Then I should see "Alert history"

	@configuration
	Scenario: See that changes to start and end times are properly updated on edit settings form
		When I am on address "/index.php/alert_history/edit_settings?with_chrome=1"
		And I select "Custom" from "Reporting period"
		And I enter "2000-01-01" into "cal_start"
		And I enter "2016-01-01" into "cal_end"
		And I enter "10:00" into "time_start"
		And I enter "10:00" into "time_end"
		And I click "Update"
		Then I should see "2000-01-01 10:00:00 to 2016-01-01 10:00:00"

	@configuration
	Scenario: Pagination
		Given I visit the alert history page for host "win-server1"
		Then I should see "OK - laa-laa"
		And I should see "OK - po"
		And I should see "ERROR - tinky-winky"
		And I should see "ERROR - out of teletubbies"

	@configuration
	Scenario: See that pagination edit settings form content rendered correct
		When I view a "alert_history" report with these settings:
		| report_type    | objects       | report_period	|
		| hosts          | win-server1   | forever			|
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
