Feature: Histogram reports
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
			| 2013-01-01 12:00:04 |        701 |  NULL |   NULL | win-server1   | PING                |     2 |    0 |     1 |           NULL | ERROR - tinky-winky |
			| 2013-01-01 12:00:05 |        701 |  NULL |   NULL | win-server1   | Swap Usage          |     3 |    0 |     1 |           NULL | UNKNOWN - out of teletubbies |
			| 2013-01-01 12:00:02 |        801 |  NULL |   NULL | linux-server2 |                     |     0 |    1 |     1 |           NULL | PRETTY OK - Jon Skolmen |
		And I have activated the configuration
		And I am logged in as administrator

	@configuration
	Scenario: Generate empty report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I click "Show report"
		Then I should see "Please select what objects to base the report on"
		And I should see "Report Settings"

	@configuration @reports
	Scenario: Generate report on empty hostgroup
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "EmptyGroup" from the multiselect "objects_tmp"
		Then "objects" should have option "EmptyGroup"
		When I click "Show report"
		Then I should see "The groups you selected (EmptyGroup) had no members, so cannot create a report from them"
		And I should see "Report Settings"

	@configuration @reports
	Scenario: Generate report on empty servicegroup
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "Servicegroups" from "Report type"
		And I select "empty" from the multiselect "objects_tmp"
		Then "objects" should have option "empty"
		When I click "Show report"
		Then I should see "The groups you selected (empty) had no members, so cannot create a report from them"
		And I should see "Report Settings"

	@configuration @reports
	Scenario: Generate single host report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "Hosts" from "Report type"
		And I select "linux-server1" from the multiselect "objects_tmp"
		Then "objects" should have option "linux-server1"
		When I select "Hard states" from "State types"
		And I select "Day of month" from "Statistics breakdown"
		And I select "This year" from "Reporting period"
		And I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included hosts"
		And I should see "linux-server1"
		And I shouldn't see "win-server1"

	@configuration @reports
	Scenario: Generate multi host report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "Hosts" from "Report type"
		And I select "linux-server1" from the multiselect "objects_tmp"
		And I select "win-server1" from the multiselect "objects_tmp"
		Then "objects" should have option "linux-server1"
		And "objects" should have option "win-server1"
		When I select "Monthly" from "Statistics breakdown"
		And I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included hosts"
		And I should see "linux-server1"
		And I should see "win-server1"

	@configuration @reports
	Scenario: Generate single service report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "Services" from "Report type"
		And I select "linux-server1;PING" from the multiselect "objects_tmp"
		Then "objects" should have option "linux-server1;PING"
		When I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included services"
		And I should see "linux-server1;PING"
		And I shouldn't see "win-server1"

	@configuration @reports
	Scenario: Generate multi service on same host report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "Services" from "Report type"
		And I select "linux-server1;PING" from the multiselect "objects_tmp"
		And I select "linux-server1;System Load" from the multiselect "objects_tmp"
		Then "objects" should have option "linux-server1;PING"
		And "objects" should have option "linux-server1;System Load"
		When I select "Day of week" from "Statistics breakdown"
		And I check "Ignore repeated states"
		And I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included services"
		And I should see "linux-server1;PING"
		And I should see "linux-server1;System Load"
		And I shouldn't see "linux-server2"
		And I shouldn't see "win-server1"

	@configuration @reports
	Scenario: Generate multi service on different host report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "Services" from "Report type"
		And I select "linux-server1;PING" from the multiselect "objects_tmp"
		And I select "linux-server2;System Load" from the multiselect "objects_tmp"
		Then "objects" should have option "linux-server1;PING"
		And "objects" should have option "linux-server2;System Load"
		When I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included services"
		And I should see "linux-server1;PING"
		And I should see "linux-server2;System Load"

	@configuration @reports
	Scenario: Generate single hostgroup report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		When I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included hosts"
		And I should see "linux-server1"
		And I should see "linux-server2"

	@configuration @reports
	Scenario: Generate multi hostgroup report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		And I select "WindowsServers" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		And "objects" should have option "WindowsServers"
		When I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included hosts"
		And I should see "linux-server1"
		And I should see "linux-server2"
		And I should see "win-server1"

	@configuration @reports
	Scenario: Generate hostgroup report with overlapping members
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		And I select "MixedGroup" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		And "objects" should have option "MixedGroup"
		When I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included hosts"
		And I should see "linux-server1"
		And I should see "linux-server2"
		And I shouldn't see "win-server1"

	@configuration @reports
	Scenario: Generate single servicegroup report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "Servicegroups" from "Report type"
		And I select "pings" from the multiselect "objects_tmp"
		Then "objects" should have option "pings"
		When I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included services"
		And I should see "linux-server1;PING"

	@configuration @reports
	Scenario: Generate multi servicegroup report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "Servicegroups" from "Report type"
		And I select "pings" from the multiselect "objects_tmp"
		And I select "empty" from the multiselect "objects_tmp"
		Then "objects" should have option "pings"
		And "objects" should have option "empty"
		When I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Included services"
		And I should see "linux-server1;PING"

	@configuration @reports
	Scenario: Generate report on custom report date
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		When I select "Custom" from "Reporting period"
		And I enter "2013-01-02" into "Start date"
		And I enter "23:31" into "time_start"
		And I enter "2013-04-03" into "End date"
		And I enter "22:32" into "time_end"
		When I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Reporting period: 2013-01-02 23:31:00 to 2013-04-03 22:32:00"

	@configuration @reports
	Scenario: Generate report on custom report date without time specified
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		And I select "LinuxServers" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		When I select "Custom" from "Reporting period"
		And I enter "2013-01-02" into "Start date"
		And I enter "" into "time_start"
		And I enter "2013-04-03" into "End date"
		And I enter "" into "time_end"
		When I click "Show report"
		Then I should see "Alert histogram"
		And I should see "Reporting period: 2013-01-02 00:00:00 to 2013-04-03 23:59:00"

	@configuration @reports
	Scenario: Save report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		Then I shouldn't see "Saved reports"
		#And "Saved reports" shouldn't have option "saved test report"
		When I select "LinuxServers" from the multiselect "objects_tmp"
		Then "objects" should have option "LinuxServers"
		# Toggle *everything*!
		When I select "Last month" from "Reporting period"
		And I select "Day of week" from "Statistics breakdown"
		And I select "Soft states" from "State types"
		And I check "Ignore repeated states"
		And I select "pink_n_fluffy" from "Skin"
		And I enter "This is a saved test report" into "Description"
		And I click "Show report"
		# I don't care where, but I want everything to be visible somehow
		Then I should see "Last month"
		And I should see "Alert histogram"
		And I should see "This is a saved test report"
		When I click "Save report"
		And I enter "saved test report" into "report_name"
		And I click "Save report" inside "#save_report_form"
		Then I should see "Report was successfully saved"

	@configuration @reports @unreliable
	Scenario: View saved report
		Given I am on the Host details page
		When I hover over the "Report" button
		And I click "Histogram"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report"
		When I select "saved test report" from "Saved reports"
		Then "objects" should have option "LinuxServers"
		And "Last month" should be selected from "Reporting period"
		And "Day of week" should be selected from "Statistics breakdown"
		And "Soft states" should be selected from "State types"
		And "Ignore repeated states" should be checked
		And "pink_n_fluffy" should be selected from "Skin"
		And "Description" should contain "This is a saved test report"
		When I click "Show report"
		Then I should see "Last month"
		And I should see "Alert histogram"
		And I should see "This is a saved test report"

	@configuration @reports
	Scenario: Delete previously created report
		Given I am on the Host details page
		And I hover over the "Report" menu
		And I hover over the "Histogram" menu
		When I click "Create Histogram Report"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report"
		When I select "saved test report"
		Then "objects" should have option "LinuxServers"
		When I click "Delete"
		# Test available first, to force capybara to wait for page reload
		Then "objects_tmp" should have option "LinuxServers"
		And "Saved reports" shouldn't have option "saved test report"
		And "objects" shouldn't have option "LinuxServers"
