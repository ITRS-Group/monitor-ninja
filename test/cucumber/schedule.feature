Feature: Scheduled reports
	Test that reports can be scheduled, that scheduled can be deleted, that
	deleting schedules deletes reports...

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

	@configuration @asmonitor @reports
	Scenario: Save avail report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		When I select "LinuxServers" from "Available hostgroups"
		And I doubleclick "LinuxServers" from "hostgroup_tmp[]"
		Then "Selected hostgroups" should have option "LinuxServers"
		When I click "Show report"
		Then I should see "Hostgroup breakdown"
		And I should see "LinuxServers"
		And I should see "linux-server1"
		When I click "Save report"
		And I enter "saved test report" into "report_name"
		And I click "Save report" inside "#save_report_form"
		# <magic page reload/>
		# ensure we see content before testing for non-content, or we won't
		# always wait for page to load
		Then I should see "Hostgroup breakdown"
		And I shouldn't see "Save report"

	@configuration @asmonitor @reports
	Scenario: Schedule avail report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Schedule Reports"
		Then I should see "New Schedule"
		And "Availability report" should be selected from "Select report type"
		When I select "Weekly" from "Report interval"
		And I select "saved test report" from "Select report"
		And I enter "dev@op5.com" into "Recipients"
		And I enter "This report comes from a cuke test. If the test worked, it would have been deleted, so if you're reading this, you've got work to do to fix tests. Chop, chop!" into "Description"
		And I select "Yes" from "Attach description"
		And I click "Save"
		Then I shouldn't see "There are no scheduled availability reports"
		And I should see "saved_test_report_Weekly.pdf"
		And I should see "dev@op5.com"

	@configuration @asmonitor @reports
	Scenario: View scheduled avail report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Schedule Reports"
		Then I should see "New Schedule"
		And "Availability report" should be selected from "Select report type"
		And "Select report" should have option "saved test report"
		And I should see "saved_test_report"
		When I click "View report" on the row where "Report" is "saved test report"
		Then I should see "Hostgroup breakdown"
		And I should see "LinuxServers"
		And I should see "linux-server1"

	@configuration @asmonitor @reports
	Scenario: Add second avail schedule
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Schedule Reports"
		Then I should see "New Schedule"
		And "Availability report" should be selected from "Select report type"
		And "Weekly" should be selected from "Report interval"
		When I select "saved test report" from "Select report"
		Then "Filename" should contain "saved_test_report_Weekly.pdf"
		When I select "Monthly" from "Report interval"
		Then "Filename" should contain "saved_test_report_Monthly.pdf"
		When I enter "dev@op5.com" into "Recipients"
		And I click "Save"
		Then I should see "saved_test_report_Monthly.pdf"
		And I shouldn't see "&nbsp;"
		And I shouldn't see "*Scheduled*" on the row where "Filename" is "saved_test_report_Monthly.pdf"
		# Description comes before persistent path, so that's where we'll click
		When I doubleclick "Double click to edit" on the row where "Filename" is "saved_test_report_Monthly.pdf"
		And I enter "A description" into "newvalue" on the row where "Filename" is "saved_test_report_Monthly.pdf"
		And I click "OK" on the row where "Filename" is "saved_test_report_Monthly.pdf"
		Then the "Description" column should be "A description" on the row where "Filename" is "saved_test_report_Monthly.pdf"

	@configuration @asmonitor @reports
	Scenario: Delete previously created avail report
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Availability"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report ( *Scheduled* )"
		When I select "saved test report"
		Then "Selected hostgroups" should have option "LinuxServers"
		When I click "Delete"
		# Test available first, to force capybara to wait for page reload
		Then "Available hostgroups" should have option "LinuxServers"
		And "Saved reports" shouldn't have option "saved test report"
		And "Selected hostgroups" shouldn't have option "LinuxServers"

	@configuration @asmonitor @reports
	Scenario: Ensure previously added schedule is gone
		Given I am on the Host details page
		And I hover over the "Reporting" button
		When I click "Schedule Reports"
		Then I should see "New Schedule"
		And I shouldn't see "saved_test_report"
		And I shouldn't see "saved test report"
		And "Select report" shouldn't have option "saved test report"
