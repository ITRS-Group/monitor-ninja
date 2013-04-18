@availability
Feature: Availability reports
	Warning: Assumes the time format is ISO-8601 (the default)

	Background:
		Given I have these hostgroups configured:
			| hostgroup_name | alias      |
			| LinuxServers   | HGALIAS-ls |
			| WindowsServers | HGALIAS-ws |
			| MixedGroup     | HGALIAS-mg |
			| EmptyGroup     | HGALIAS-eg |
		And I have these hosts:
			| host_name      | host_groups               | alias      |
			| linux-server1  | LinuxServers,MixedGroup   | HALIAS-ls1 |
			| linux-server2  | LinuxServers              | HALIAS-ls2 |
			| win-server1    | WindowsServers            | HALIAS-ws1 |
			| win-server2    | WindowsServers,MixedGroup | HALIAS-ws2 |
		And I have these servicegroups:
			| servicegroup_name | alias     |
			| pings             | SGALIAS-p |
			| empty             | SGALIAS-e |
		And I have these services:
			| service_description | host_name     | check_command   | notifications_enabled | active_checks_enabled | service_groups |
			| System Load         | linux-server1 | check_nrpe!load | 1                     | 1                     |                |
			| PING                | linux-server1   | check_ping    | 1                     | 0                     | pings          |
			| System Load         | linux-server2 | check_nrpe!load | 1                     | 1                     |                |
			| PING                | win-server1   | check_ping      | 1                     | 0                     | pings          |
			| PING                | win-server2   | check_ping      | 0                     | 1                     | pings          |
		And I have these report data entries:
			| timestamp           | event_type | flags | attrib | host_name     | service_description | state | hard | retry | downtime_depth | output |
			| 2013-01-01 12:00:00 |        100 |  NULL |   NULL |               |                     |     0 |    0 |     0 |           NULL | NULL                |
			| 2013-01-01 12:00:01 |        801 |  NULL |   NULL | win-server1   |                     |     0 |    1 |     1 |           NULL | OK - laa-laa        |
			| 2013-01-01 12:00:02 |        801 |  NULL |   NULL | linux-server1 |                     |     0 |    1 |     1 |           NULL | OK - Sven Melander  |
			| 2013-01-01 12:00:03 |        701 |  NULL |   NULL | win-server1   | PING                |     0 |    1 |     1 |           NULL | OK - po             |
			| 2013-01-01 12:00:03 |        701 |  NULL |   NULL | win-server1   | PING                |     1 |    0 |     1 |           NULL | ERROR - tinky-winky |

		And I have activated the configuration

	@configuration @asmonitor @reports
	Scenario: Generate report without objects
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I click "Show report"
		Then I should see "Please select what objects to base the report on"
		And I should see "Report Settings"

	@configuration @asmonitor @reports
	Scenario: Generate report on empty hostgroup
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "EmptyGroup" from "Available hostgroups"
		And I doubleclick "EmptyGroup" from "hostgroup_tmp[]"
		Then "Selected hostgroups" should have option "EmptyGroup"
		When I click "Show report"
		Then I should see "No objects could be found in your selected groups to base the report on"
		And I should see "Report Settings"

	@configuration @asmonitor @reports
	Scenario: Generate report on empty servicegroup
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "Servicegroups" from "Report type"
		And I select "empty" from "Available servicegroups"
		And I doubleclick "empty" from "servicegroup_tmp[]"
		Then "Selected servicegroups" should have option "empty"
		When I click "Show report"
		Then I should see "No objects could be found in your selected groups to base the report on"
		And I should see "Report Settings"

	@configuration @asmonitor @reports
	Scenario: Generate single host report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "Hosts" from "Report type"
		And I select "linux-server1" from "Available hosts"
		And I doubleclick "linux-server1" from "host_tmp[]"
		Then "Selected hosts" should have option "linux-server1"
		When I click "Show report"
		Then I should see "Host details for linux-server1"
		And I should see "Selected services"
		And I shouldn't see "Selected hosts"
		And I should see "PING"
		And I shouldn't see "linux-server2"
		And I shouldn't see "win-server1"
		And I should see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate multi host report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "Hosts" from "Report type"
		And I select "linux-server1" from "Available hosts"
		And I doubleclick "linux-server1" from "host_tmp[]"
		And I select "win-server1" from "Available hosts"
		And I doubleclick "win-server1" from "host_tmp[]"
		Then "Selected hosts" should have option "linux-server1"
		And "Selected hosts" should have option "win-server1"
		When I click "Show report"
		Then I should see "Host state breakdown"
		And I should see "Selected hosts"
		And I shouldn't see "Selected services"
		And I should see "linux-server1"
		And I should see "win-server1"
		And I shouldn't see "linux-server2"
		And I shouldn't see "win-server2"
		And I should see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate single service report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "Services" from "Report type"
		And I select "linux-server1;PING" from "Available services"
		And I doubleclick "linux-server1;PING" from "service_tmp[]"
		Then "Selected services" should have option "linux-server1;PING"
		When I click "Show report"
		Then I should see "Service details for PING on host linux-server1"
		And I shouldn't see "System Load"
		And I shouldn't see "win-server"
		And I shouldn't see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate multi service on same host report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "Services" from "Report type"
		And I select "linux-server1;PING" from "Available services"
		And I doubleclick "linux-server1;PING" from "service_tmp[]"
		And I select "linux-server1;System Load" from "Available services"
		And I doubleclick "linux-server1;System Load" from "service_tmp[]"
		Then "Selected services" should have option "linux-server1;PING"
		And "Selected services" should have option "linux-server1;System Load"
		When I check "Use alias"
		And I click "Show report"
		Then I should see "Service state breakdown"
		And I should see "Services on host: HALIAS-ls1 (linux-server1)"
		And I should see "PING"
		And I should see "System Load"
		And I shouldn't see "linux-server2"
		And I shouldn't see "win-server1"
		And I should see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate multi service on different host report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "Services" from "Report type"
		And I select "linux-server1;PING" from "Available services"
		And I doubleclick "linux-server1;PING" from "service_tmp[]"
		And I select "linux-server2;System Load" from "Available services"
		And I doubleclick "linux-server2;System Load" from "service_tmp[]"
		Then "Selected services" should have option "linux-server1;PING"
		And "Selected services" should have option "linux-server2;System Load"
		When I click "Show report"
		Then I should see "Service state breakdown"
		And I should see "Services on host: linux-server1"
		And I should see "PING"
		And I should see "Services on host: linux-server2"
		And I should see "System Load"
		And I shouldn't see "win-server"
		And I should see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate single hostgroup report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "LinuxServers" from "Available hostgroups"
		And I doubleclick "LinuxServers" from "hostgroup_tmp[]"
		Then "Selected hostgroups" should have option "LinuxServers"
		When I check "Use alias"
		And I click "Show report"
		Then I should see "Hostgroup breakdown"
		And I should see "HGALIAS-ls (LinuxServers)"
		And I should see "HALIAS-ls1 (linux-server1)"
		And I should see "HALIAS-ls2 (linux-server2)"
		And I shouldn't see "win-server1"
		And I shouldn't see "win-server2"
		And I should see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate multi hostgroup report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "LinuxServers" from "Available hostgroups"
		And I doubleclick "LinuxServers" from "hostgroup_tmp[]"
		And I select "WindowsServers" from "Available hostgroups"
		And I doubleclick "WindowsServers" from "hostgroup_tmp[]"
		Then "Selected hostgroups" should have option "LinuxServers"
		And "Selected hostgroups" should have option "WindowsServers"
		When I click "Show report"
		Then I should see "Hostgroup breakdown"
		And I should see "Average and group availability for LinuxServers"
		And I should see "Average and group availability for WindowsServers"
		And I should see "linux-server1"
		And I should see "linux-server2"
		And I should see "win-server1"
		And I should see "win-server2"
		And I should see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate hostgroup report with overlapping members
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "LinuxServers" from "Available hostgroups"
		And I doubleclick "LinuxServers" from "hostgroup_tmp[]"
		And I select "MixedGroup" from "Available hostgroups"
		And I doubleclick "MixedGroup" from "hostgroup_tmp[]"
		Then "Selected hostgroups" should have option "LinuxServers"
		And "Selected hostgroups" should have option "MixedGroup"
		When I click "Show report"
		Then I should see "Hostgroup breakdown"
		And I should see "Average and group availability for LinuxServers"
		And I should see "Average and group availability for MixedGroup"
		And I should see "linux-server1"
		And I should see "linux-server2"
		And I shouldn't see "win-server1"
		And I should see "win-server2"
		And I should see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate single servicegroup report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "Servicegroups" from "Report type"
		And I select "pings" from "Available servicegroups"
		And I doubleclick "pings" from "servicegroup_tmp[]"
		Then "Selected servicegroups" should have option "pings"
		When I check "Use alias"
		And I click "Show report"
		Then I should see "Servicegroup breakdown"
		And I should see "SGALIAS-p (pings)"
		And I should see "Services on host: HALIAS-ls1 (linux-server1)"
		And I should see "Services on host: HALIAS-ws1 (win-server1)"
		And I should see "Services on host: HALIAS-ws2 (win-server2)"
		And I should see "PING"
		And I shouldn't see "linux-server2"
		And I shouldn't see "System Load"
		And I should see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate multi servicegroup report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "Servicegroups" from "Report type"
		And I select "pings" from "Available servicegroups"
		And I doubleclick "pings" from "servicegroup_tmp[]"
		And I select "empty" from "Available servicegroups"
		And I doubleclick "empty" from "servicegroup_tmp[]"
		Then "Selected servicegroups" should have option "pings"
		And "Selected servicegroups" should have option "empty"
		When I click "Show report"
		Then I should see "Servicegroup breakdown"
		And I should see "Average and group availability for pings"
		And I shouldn't see "Average and group availability for empty"
		And I should see "Services on host: linux-server1"
		And I should see "Services on host: win-server1"
		And I should see "Services on host: win-server2"
		And I should see "PING"
		And I shouldn't see "linux-server2"
		And I shouldn't see "System Load"
		And I should see "Group availability (SLA)"

	@configuration @asmonitor @reports
	Scenario: Generate report on custom report date
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		And I select "LinuxServers" from "Available hostgroups"
		And I doubleclick "LinuxServers" from "hostgroup_tmp[]"
		Then "Selected hostgroups" should have option "LinuxServers"
		When I select "Custom" from "Reporting period"
		And I enter "2013-01-02" into "Start date"
		And I enter "23:31" into "time_start"
		And I enter "2013-04-03" into "End date"
		And I enter "22:32" into "time_end"
		And I select "workhours" from "Report time period"
		When I click "Show report"
		Then I should see "Hostgroup breakdown"
		And I should see "Reporting period: 2013-01-02 23:31:00 to 2013-04-03 22:32:00 - workhours"

	@configuration @asmonitor @reports
	Scenario: Save report with misc options
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		Then I should see "Saved reports"
		And "Saved reports" shouldn't have option "saved test report"
		When I select "LinuxServers" from "Available hostgroups"
		And I doubleclick "LinuxServers" from "hostgroup_tmp[]"
		Then "Selected hostgroups" should have option "LinuxServers"
		# Toggle *everything*!
		When I select "Last month" from "Reporting period"
		And I select "workhours" from "Report time period"
		And I check "Down"
		And I select "Average" from "SLA calculation method"
		And I select "Uptime, with difference" from "Count scheduled downtime as"
		And I select "Undetermined" from "Count program downtime as"
		And I check "Include soft states"
		And I check "Use alias"
		And I check "Include trends graph"
		And I check "Include pie charts"
		And I select "pink_n_fluffy" from "Skin"
		And I enter "This is a saved test report" into "Description"
		And I click "Show report"
		# I don't care where, but I want everything to be visible somehow
		Then I should see "Last month"
		And I should see "workhours"
		And I should see "Showing hosts in state: up, unreachable, pending"
		And I should see "Average"
		And I shouldn't see "SLA"
		And I should see "Uptime, with difference"
		And I shouldn't see "Counting program downtime"
		And I should see "Including soft states"
		And I should see "HALIAS-ls1"
		And I should see "HALIAS-ls2"
		And I should see "HGALIAS-ls"
		And I should see "This is a saved test report"
		When I click "Save report"
		And I enter "saved test report" into "report_name"
		And I click "Save report" inside "#save_report_form"
		# <magic page reload/>
		# ensure we see content before testing for non-content, or we won't
		# always wait for page to load
		Then I should see "Hostgroup breakdown"
		And I shouldn't see "Save report"

	@configuration @asmonitor @reports
	Scenario: View saved report
		Given I am on the Host details page
		When I hover over the "Reporting" button
		And I click "Availability"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report"
		When I select "saved test report" from "Saved reports"
		Then "Selected hostgroups" should have option "LinuxServers"
		And "Last month" should be selected from "Reporting period"
		And "workhours" should be selected from "Report time period"
		And "Down" should be checked
		And "Average" should be selected from "SLA calculation method"
		And "Uptime, with difference" should be selected from "Count scheduled downtime as"
		And "Undetermined" should be selected from "Count program downtime as"
		And "Include soft states" should be checked
		And "Use alias" should be checked
		And "Include trends graph" should be checked
		And "Include pie charts" should be checked
		And "pink_n_fluffy" should be selected from "Skin"
		And "Description" should contain "This is a saved test report"
		When I click "Show report"
		Then I should see "Last month"
		And I should see "workhours"
		And I should see "Showing hosts in state: up, unreachable, pending"
		And I should see "Average"
		And I shouldn't see "SLA"
		And I should see "Uptime, with difference"
		And I shouldn't see "Counting program downtime"
		And I should see "Including soft states"
		And I should see "HALIAS-ls1"
		And I should see "HALIAS-ls2"
		And I should see "HGALIAS-ls"
		And I should see "This is a saved test report"

	@configuration @asmonitor @reports
	Scenario: Delete previously created report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report"
		When I select "saved test report"
		Then "Selected hostgroups" should have option "LinuxServers"
		When I click "Delete"
		# Test available first, to force capybara to wait for page reload
		Then "Available hostgroups" should have option "LinuxServers"
		And "Saved reports" shouldn't have option "saved test report"
		And "Selected hostgroups" shouldn't have option "LinuxServers"
