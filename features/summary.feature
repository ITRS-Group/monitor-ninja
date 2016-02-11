@summary @configuration @reports
Feature: Summary reports
	Warning: Assumes the time format is ISO-8601 (the default)

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
			| 2012-01-01 12:00:02 |        701 |  NULL |   NULL | linux-server1 | System Load         |     0 |    1 |     1 |           NULL | OK - Åke Cato       |
			| 2013-01-01 12:00:00 |        100 |  NULL |   NULL |               |                     |     0 |    0 |     0 |           NULL | NULL                |
			| 2013-01-01 12:00:01 |        801 |  NULL |   NULL | win-server1   |                     |     0 |    1 |     1 |           NULL | OK - laa-laa        |
			| 2013-01-01 12:00:02 |        801 |  NULL |   NULL | linux-server1 |                     |     0 |    1 |     1 |           NULL | OK - Sven Melander  |
			| 2013-01-01 12:00:03 |        701 |  NULL |   NULL | win-server1   | PING                |     0 |    1 |     1 |           NULL | OK - po             |
			| 2013-01-01 12:00:04 |        701 |  NULL |   NULL | win-server1   | PING                |     1 |    0 |     1 |           NULL | ERROR - tinky-winky |
			| 2013-01-01 12:00:05 |        701 |  NULL |   NULL | win-server1   | Swap Usage          |     1 |    0 |     1 |           NULL | ERROR - out of teletubbies |
			| 2013-01-01 12:00:02 |        801 |  NULL |   NULL | linux-server2 |                     |     0 |    1 |     1 |           NULL | PRETTY OK - Jon Skolmen |
		And I have activated the configuration
		And I am logged in

	Scenario: See that the default-custom selector works
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		Then "Standard" should be checked
		And I should see "Standard type"
		And I should see "Items to show"
		And I should see button "Show report"
		And I shouldn't see "Reporting period"
		And I shouldn't see "Host states"
		And I shouldn't see "objects"
		When I choose "Custom"
		Then I shouldn't see "Standard type"
		And I should see "Items to show"
		And I should see button "Show report"
		And I should see "Reporting period"
		And I should see "Host states"
		When I choose "Standard"
		Then I should see "Standard type"
		And I should see "Items to show"
		And I should see button "Show report"
		And I shouldn't see "Reporting period"
		And I shouldn't see "Host states"

	Scenario: Generate report without objects
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I click "Show report"
		Then I should see "Please select what objects to base the report on"
		And I should see "Report Settings"

	Scenario: Generate report on empty hostgroup
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "EmptyGroup" from the multiselect "objects_tmp"
		Then "objects" should have option "EmptyGroup"
		And I click "Show report"
		Then I should see "No objects could be found in your selected groups to base the report on"
		And I should see "Report Mode"

	Scenario: Generate report on empty servicegroup
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "Servicegroups" from "Report type"
		And I select "empty" from the multiselect "objects_tmp"
		Then "objects" should have option "empty"
		And I click "Show report"
		Then I should see "No objects could be found in your selected groups to base the report on"
		And I should see "Report Mode"

	Scenario: Generate report for host should by default include service alerts
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "Hosts" from "Report type"
		And I select "win-server1" from the multiselect "objects_tmp"
		Then "objects" should have option "win-server1"
		When I select "Forever" from "Reporting period"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "win-server1"
		And I should see "PING"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server2"
		And I shouldn't see "System Load"
		And I should see "Host alert"
		And I should see "Service alert"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "win-server1"
		And I should see "PING"
		# FIXME: would look better with a generic table content helper...
		# The number of host alerts
		And I should see "2"
		# The number of service alerts
		And I should see "1"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server2"
		And I shouldn't see "System Load"
		When I click "Edit settings"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for win-server1"
		And I should see "Service alerts for win-server1"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server2"
		And I shouldn't see "System Load"

	Scenario: Generate multi host report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "Hosts" from "Report type"
		And I select "win-server1" from the multiselect "objects_tmp"
		And I select "linux-server1" from the multiselect "objects_tmp"
		Then "objects" should have option "win-server1"
		When I select "Forever" from "Reporting period"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "win-server1"
		And I should see "linux-server1"
		And I should see "PING"
		# FIXME: would look better with a generic table content helper...
		And I should see "1"
		And I should see "2"
		And I shouldn't see "linux-server2"
		And I shouldn't see "win-server2"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I check "Include full output"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "win-server1"
		And I should see "linux-server1"
		And I should see "PING"
		And I shouldn't see "linux-server2"
		And I shouldn't see "win-server2"
		And I should see "Host alert"
		And I should see "Service alert"
		When I click "Edit settings"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for win-server1"
		And I should see "Service alerts for win-server1"
		And I should see "Host alerts for linux-server1"
		And I should see "Service alerts for linux-server1"
		And I should see "1"
		And I should see "2"
		And I shouldn't see "linux-server2"
		And I shouldn't see "win-server2"

	Scenario: Generate single service report should by default include host alerts
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "Services" from "Report type"
		And I select "win-server1;PING" from the multiselect "objects_tmp"
		Then "objects" should have option "win-server1;PING"
		When I select "Forever" from "Reporting period"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for win-server1"
		And I should see "Service alerts for win-server1;PING"
		And I shouldn't see "Host alerts for win-server1;PING"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server2"
		And I shouldn't see "System Load"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "win-server1"
		And I should see "PING"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server2"
		And I shouldn't see "System Load"
		And I should see "Host alert"
		And I should see "Service alert"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "win-server1"
		And I should see "PING"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server2"
		And I shouldn't see "System Load"

	Scenario: Generate multi service on same host report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "Services" from "Report type"
		And I select "win-server1;PING" from the multiselect "objects_tmp"
		And I select "win-server1;Swap Usage" from the multiselect "objects_tmp"
		Then "objects" should have option "win-server1;PING"
		And "objects" should have option "win-server1;Swap Usage"
		When I select "Forever" from "Reporting period"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for win-server1"
		And I should see "Service alerts for win-server1;PING"
		And I should see "Service alerts for win-server1;Swap Usage"
		And I shouldn't see "Host alerts for win-server1;PING"
		And I shouldn't see "Host alerts for win-server1;Swap Usage"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server2"
		And I shouldn't see "System Load"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "win-server1"
		And I should see "PING"
		And I should see "Swap Usage"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server2"
		And I shouldn't see "System Load"
		And I should see "Host alert"
		And I should see "Service alert"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "win-server1"
		And I should see "PING"
		And I should see "Swap Usage"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server2"
		And I shouldn't see "System Load"

	Scenario: Generate multi service on different host report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "Services" from "Report type"
		And I select "linux-server1;System Load" from the multiselect "objects_tmp"
		And I select "win-server1;PING" from the multiselect "objects_tmp"
		Then "objects" should have option "linux-server1;System Load"
		And "objects" should have option "win-server1;PING"
		When I select "Forever" from "Reporting period"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for linux-server1"
		And I should see "Host alerts for win-server1"
		And I should see "Service alerts for linux-server1;System Load"
		And I should see "Service alerts for win-server1;PING"
		And I shouldn't see "Host alerts for linux-server1;System Load"
		And I shouldn't see "Host alerts for win-server1;PING"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "linux-server2"
		And I shouldn't see "win-server2"
		And I shouldn't see "Swap Usage"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "win-server1"
		And I should see "linux-server1"
		And I should see "PING"
		And I should see "System Load"
		And I shouldn't see "win-server2"
		And I shouldn't see "Swap Usage"
		And I should see "Host alert"
		And I should see "Service alert"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "win-server1"
		And I should see "linux-server1"
		And I should see "PING"
		And I should see "System Load"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "linux-server2"
		And I shouldn't see "win-server2"
		And I shouldn't see "Swap Usage"

	Scenario: Generate single hostgroup report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		When I select "Forever" from "Reporting period"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for LinuxServers"
		And I should see "Service alerts for LinuxServers"
		# The number of host alerts
		And I should see "2"
		# The number of service alerts
		And I should see "1"
		And I shouldn't see "PING"
		And I shouldn't see "Swap Usage"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "linux-server1"
		And I should see "linux-server2"
		And I shouldn't see "win-server"
		And I shouldn't see "Swap Usage"
		And I should see "Host alert"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "linux-server1"
		And I should see "linux-server2"
		# The number of host alerts
		And I should see "2"
		And I shouldn't see "win-server"
		And I shouldn't see "PING"
		And I shouldn't see "Swap Usage"

	Scenario: Generate multi hostgroup report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		And I select "WindowsServers" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		And "objects" should have option "WindowsServers"
		When I select "Forever" from "Reporting period"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for LinuxServers"
		And I should see "Service alerts for LinuxServers"
		And I should see "Host alerts for WindowsServers"
		And I should see "Service alerts for WindowsServers"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "PING"
		And I shouldn't see "Swap Usage"
		And I shouldn't see "System Load"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "linux-server1"
		And I should see "linux-server2"
		And I should see "win-server1"
		And I should see "Swap Usage"
		And I should see "PING"
		And I should see "Host alert"
		And I should see "Service alert"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "linux-server1"
		And I should see "linux-server2"
		And I should see "win-server1"
		And I should see "PING"
		And I should see "Swap Usage"
		# The number of host alerts
		And I should see "1"

	Scenario: Generate hostgroup report with overlapping members
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		And I select "MixedGroup" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		And "objects" should have option "MixedGroup"
		When I select "Forever" from "Reporting period"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for LinuxServers"
		And I should see "Service alerts for LinuxServers"
		And I should see "Host alerts for MixedGroup"
		And I should see "Service alerts for MixedGroup"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "PING"
		And I shouldn't see "Swap Usage"
		And I shouldn't see "System Load"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "linux-server1"
		And I should see "linux-server2"
		And I shouldn't see "PING"
		And I shouldn't see "win-server1"
		And I shouldn't see "Swap Usage"
		And I should see "Host alert"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "linux-server1"
		And I should see "linux-server2"
		And I shouldn't see "win-server1"
		And I shouldn't see "PING"
		And I shouldn't see "Swap Usage"
		# The number of host alerts
		And I should see "1"

	Scenario: Generate single servicegroup report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "Servicegroups" from "Report type"
		And I select "pings" from the multiselect "objects_tmp"
		Then "objects" should have option "pings"
		When I select "Forever" from "Reporting period"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for pings"
		And I should see "Service alerts for pings"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "PING"
		And I shouldn't see "Swap Usage"
		And I shouldn't see "System Load"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "linux-server1"
		And I should see "win-server1"
		And I should see "Swap Usage"
		And I shouldn't see "linux-server2"
		And I shouldn't see "System Load"
		And I should see "Host alert"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "linux-server1"
		And I should see "win-server1"
		And I should see "PING"
		And I should see "Swap Usage"
		# The number of host alerts
		And I should see "1"
		And I shouldn't see "linux-server2"
		And I shouldn't see "System Load"

	Scenario: Generate multi servicegroup report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "Servicegroups" from "Report type"
		And I select "pings" from the multiselect "objects_tmp"
		And I select "empty" from the multiselect "objects_tmp"
		Then "objects" should have option "pings"
		And "objects" should have option "empty"
		When I select "Forever" from "Reporting period"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Host alerts for pings"
		And I should see "Service alerts for pings"
		# The number of host alerts
		And I should see "1"
		# The number of service alerts
		And I should see "2"
		And I shouldn't see "PING"
		And I shouldn't see "Swap Usage"
		And I shouldn't see "System Load"
		And I shouldn't see "linux-server"
		And I shouldn't see "win-server"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "linux-server1"
		And I should see "win-server1"
		And I should see "Swap Usage"
		And I shouldn't see "linux-server2"
		And I shouldn't see "System Load"
		And I should see "Host alert"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "linux-server1"
		And I should see "win-server1"
		And I should see "PING"
		And I should see "Swap Usage"
		# The number of host alerts
		And I should see "1"
		And I shouldn't see "linux-server2"
		And I shouldn't see "System Load"

	Scenario: Generate report on custom report date
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		When I select "Custom" from "Reporting period"
		And I enter "2013-01-02" into "Start date"
		And I enter "23:31" into "time_start"
		And I enter "2013-04-03" into "End date"
		And I enter "22:32" into "time_end"
		And I select "workhours" from "Report time period"
		And I select "Alert totals" from "Summary type"
		And I click "Show report"
		Then I should see "Alert totals"
		And I should see "Reporting period: 2013-01-02 23:31:00 to 2013-04-03 22:32:00 - workhours"
		When I click "Edit settings"
		And I select "Most recent alerts" from "Summary type"
		And I click "Show report"
		Then I should see "Most recent alerts"
		And I should see "Reporting period: 2013-01-02 23:31:00 to 2013-04-03 22:32:00 - workhours"
		When I click "Edit settings"
		And I select "Top alert producers" from "Summary type"
		And I click "Show report"
		Then I should see "Top alert producers"
		And I should see "Reporting period: 2013-01-02 23:31:00 to 2013-04-03 22:32:00 - workhours"

	Scenario: Save report with misc options
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I choose "Custom"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		# Toggle *everything*!
		When I select "Most recent alerts" from "Summary type"
		And I enter "50" into "Items to show"
		And I select "Custom" from "Reporting period"
		And I enter "2013-01-01" into "Start date"
		And I enter "01:31" into "time_start"
		And I enter "2013-04-03" into "End date"
		And I enter "22:32" into "time_end"
		And I select "workhours" from "Report time period"
		And I select "Hard states" from "State types"
		And I check "Up"
		And I uncheck "Down"
		And I uncheck "Unreachable"
		And I uncheck "Ok"
		And I uncheck "Undetermined"
		And I check "Warning"
		And I check "Critical"
		And I check "Unknown"
		And I uncheck "Undetermined"
		And I select "pink_n_fluffy" from "Skin"
		And I enter "This is a saved test report" into "Description"
		And I click "Show report"
		# I don't care where, but I want everything to be visible somehow
		Then I should see "2013-01-01 01:31:00 to 2013-04-03 22:32:00"
		And I should see "workhours"
		And I should see "Showing Hard states in up, warning, critical, unknown"
		And I should see "Sven Melander"
		# Tänk på pocenten, helge!
		And I should see "This is a saved test report"
		When I click "Save report"
		And I enter "saved test report" into "report_name"
		And I click "Save report" inside "#save_report_form"
		Then I should see "Report was successfully saved"

	@unreliable
	Scenario: View saved report
		Given I am on the Host details page
		When I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report"
		When I select "saved test report" from "Saved reports"
		Then "Custom" should be checked
		And "objects" should have option "LinuxServers"
		And "Most recent alerts" should be selected from "Summary type"
		And "Items to show" should contain "50"
		And "Custom" should be selected from "Reporting period"
		And "Start date" should contain "2013-01-01"
		And "time_start" should contain "01:31"
		And "End date" should contain "2013-04-03"
		And "time_end" should contain "22:32"
		And "workhours" should be selected from "Report time period"
		And "Host alerts" should be selected from "Alert types"
		And "Hard states" should be selected from "State types"
		And "Up" should be checked
		And "Warning" should be checked
		And "Critical" should be checked
		And "Unknown" should be checked
		And "pink_n_fluffy" should be selected from "Skin"
		And "Description" should contain "This is a saved test report"
		When I click "Show report"
		Then I should see "2013-01-01 01:31:00 to 2013-04-03 22:32:00"
		And I should see "workhours"
		And I should see "Hard states"
		And I should see "Host up states"
		And I should see "Sven Melander"
		And I should see "This is a saved test report"

	Scenario: Delete previously created report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report"
		When I select "saved test report"
		Then "objects" should have option "LinuxServers"
		When I click "Delete"
		Then "Saved reports" shouldn't have option "saved test report"
		And "objects" shouldn't have option "LinuxServers"

	# FIXME: all the standard report tests are crap, because I don't yet have
	# a way to create alerts for the last 7 days that won't break in a week
	Scenario: Standard Most recent hard alerts
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I select "Most recent hard alerts" from "Standard type"
		And I click "Show report"
		Then I should see "Most recent alerts"

	Scenario: Standard Most recent hard host alerts
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I select "Most recent hard host alerts" from "Standard type"
		And I click "Show report"
		Then I should see "Most recent alerts"

	Scenario: Standard Most recent hard service alerts
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I select "Most recent hard host alerts" from "Standard type"
		And I click "Show report"
		Then I should see "Most recent alerts"

	Scenario: Standard Top hard alerts
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I select "Top hard alert producers" from "Standard type"
		And I click "Show report"
		Then I should see "Top alert producers"

	Scenario: Standard Top hard host alerts
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I select "Top hard host alert producers" from "Standard type"
		And I click "Show report"
		Then I should see "Top alert producers"

	Scenario: Standard Top hard service alerts
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		And I select "Top hard service alert producers" from "Standard type"
		And I click "Show report"
		Then I should see "Top alert producers"
