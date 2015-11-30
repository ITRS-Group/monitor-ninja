@monitoring @configuration
Feature: Monitoring
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

	@asmonitor @case-643 @integration
	Scenario: Host details filter
		Ensure that the filters on the host details
		page works as expected.

		Given I am on the Host details page
		When I click "Hosts total"
		Then I should see the configured hosts
		When I have submitted a passive host check result "linux-server2;1;some output"
		And I click "Hosts down"
		Then I should see "linux-server2"
		When I click "Services total"
		Then I should see the configured services

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Locate host on map
		Verify that the "Locate host on map" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Locate host on map"
		Then I should be on address "/monitor/index.php/nagvis/automap/host/linux-server1"
		And I should see "linux-server1" within frame "nagvis"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Disable active checks
		Verify that the "Disable active checks" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Disable active checks"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Active checks" should be shown as "Disabled"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Reschedule check
		Verify that the "Reschedule next check" host command
		works correctly.

		Given I am on the Host details page
		And I have submitted a passive host check result "linux-server1;0;Everything was OK"
		When I click "linux-server1"
		And I click "Re-schedule next host check"
		And I enter the time in 5 minutes into "field_check_time"
		And I note the value of "field_check_time"
		And I check "field_forced"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Next scheduled check" should be shown as the value of "field_check_time"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Submit passive check
		Verify that the "Submit passive check" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Submit passive check"
		And I select "Down" from "field_status_code"
		And I enter "Some output" into "field_plugin_output"
		And I enter "2" into "field_perf_data"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Current status" should be shown as "Down"


	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Stop obsessing
		Verify that the "Stop obsessing" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Stop obsessing"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Obsessing" should be shown as "Disabled"


	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Disable passive check
		Verify that the "Disable passive check" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Stop accepting passive checks"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Passive checks" should be shown as "Disabled"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Disable notifications
		Verify that the "Disable notifications" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Disable notifications"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Notifications" should be shown as "Disabled"

	@asmonitor @case-646 @unreliable @integration
	Scenario: Host details host commands - Send custom notification
		Verify that the "Send custom notification" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		Then "Notifications" should be shown as "Enabled"
		When I click "Send custom notification"
		And I enter "Some comment" into "Comment"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		# ninja -> nagios -> merlin -> mysql...
		And wait for "10" seconds
		And I click "Notifications"
		Then I should see "linux-server1"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Schedule downtime
		Verify that the "Schedule downtime" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Schedule downtime"
		And I enter "Some comment" into "field_comment"
		And I enter "2023-03-14 14:40" into "field_start_time"
		And I click "Submit"
		Then I should see "ERROR: 2023-03-14 14:40 is not a valid date, please adjust it"
		And I shouldn't see "Fatal error"
		And I click "Back"
		Then I should see "Schedule downtime"
		And I should see "linux-server1"
		And I enter "2023-03-14 14:40:00" into "field_start_time"
		And I enter "2023-03-14 14:50:00" into "field_end_time"
		And I enter "Some comment" into "field_comment"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I reload the page
		Then I should see "This host has been scheduled for fixed downtime"

	@asmonitor @trial_license @integration
	Scenario: Schedule downtime retro - before first event in db
		MON-8606

		Given I am on the Host details page
		And I have these report data entries:
                        | timestamp           | event_type | flags | attrib | host_name | service_description | state | hard | retry | downtime_depth | output |
                        | 2013-01-01 12:00:00 |        100 |  NULL |   NULL |           |                     |     0 |    0 |     0 |           NULL | NULL   |
		When I click "linux-server1"
		And I click "Schedule downtime"
		And I enter "Ghost of Christmas past" into "field_comment"
		And I enter "1970-06-06 11:00:03" into "field_start_time"
		And I click "Submit"
		Then I should see "Adjusting start of downtime to start when Monitor was installed"
		When I go to the listview for [downtimes] host.name = "linux-server1"
		Then I should see "Ghost of Christmas past"
		And I should see "2013-01-01"

	@asmonitor @trial_license @integration
	Scenario: Schedule downtime retro - after first event in db
		MON-8606

		Given I am on the Host details page
		And I have these report data entries:
                        | timestamp           | event_type | flags | attrib | host_name | service_description | state | hard | retry | downtime_depth | output |
                        | 2013-01-01 12:00:00 |        100 |  NULL |   NULL |           |                     |     0 |    0 |     0 |           NULL | NULL   |
		When I click "linux-server1"
		And I click "Schedule downtime"
		And I enter "lullull" into "field_comment"
		And I enter "2014-01-01 00:00:01" into "field_start_time"
		And I click "Submit"
		Then I should see "Scheduled retrospectively for reporting"
		When I click "Done"
		Then I should see "lullull"

	@asmonitor @trial_license @integration
	Scenario: Schedule downtime now
		MON-8606

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Schedule downtime"
		And I enter "I love lamp" into "field_comment"
		And I enter the current date and time into "field_start_time"
		And I click "Submit"
		Then I should see "Scheduled retrospectively for reporting"
		When I click "Done"
		Then I should see "I love lamp"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Disable notifications for services
		Verify that the "Disable notifications for all services"
		host command works correctly.

		Given I am on the Host details page
		And I have submitted a passive service check result "linux-server1;System Load;0;Everything was OK"
		When I click "linux-server1"
		And I click "Disable notifications for all services"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "System Load"
		Then "Notifications" should be shown as "Disabled"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Enable notifications for services
		Verify that the "Enable notifications for all services"
		host command works correctly.

		Given I am on the Host details page
		And I have submitted a passive service check result "win-server2;PING;0;Everything was OK"
		When I click "win-server2"
		And I click "Enable notifications for all services"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "PING"
		Then "Notifications" should be shown as "Enabled"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Schedule check for all services
		Verify that the "Schedule check for all services" host command
		works correctly.

		Given I am on the Host details page
		And I have submitted a passive service check result "linux-server1;System Load;0;Everything was OK"
		When I click "linux-server1"
		And I click "Schedule a check of all services"
		And I enter the time in 5 minutes into "field_check_time"
		And I note the value of "field_check_time"
		And I check "field_forced"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "System Load"
		Then "Next scheduled active check" should be shown as the value of "field_check_time"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Disable checks of all services
		Verify that the "Disable checks of all services" host command works correctly.

		Given I am on the Host details page
		And I have submitted a passive service check result "linux-server1;System Load;0;Everything was OK"
		When I click "linux-server1"
		And I click "Disable checks of all services"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "System Load"
		Then "Active checks" should be shown as "Disabled"


	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Enable checks of all services
		Verify that the "Enable checks of all services" host command works correctly.

		Given I am on the Host details page
		When I click "win-server1"
		And I click "Enable checks of all services"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "PING"
		Then "Active checks" should be shown as "Enabled"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Disable event handler
		Verify that the "Disable event handler" host command works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Disable event handler"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Event handler" should be shown as "Disabled"
		And I should see "Enable event handler"

	@asmonitor @case-646 @integration
	Scenario: Host details host commands - Disable flap detection
		Verify that the "Disable flap detection" host command works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Disable flap detection"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Flap detection" should be shown as "Disabled"
		And I should see "Enable flap detection"

	@asmonitor @case-647 @integration
	Scenario: Host details host extinfo page show performance graph
		Verify that the "Show performance graph" link works correctly.

		Given I have PNP data for "linux-server1"
		And I am on the Host details page
		When I click "linux-server1"
		And I click "Show performance graph"
		Then I should be on the PNP page

	@asmonitor @case-648 @integration
	Scenario: Host details Add/delete comment
		Verify that adding and deleting comments on hosts
		works.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Add a new comment"
		And I enter "A comment for this host" into "comment"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I should see "A comment for this host"
		When I click "Delete comment"
		Then I should see "Delete comment"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then I shouldn't see "A comment for this host"

	@asmonitor @bug-6933 @integration
	Scenario: Disable passive checks and obsess over this host
		Verify that after disable passive checks for this host and
		stop obsess over this host it is possible to start them both.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Stop accepting passive checks"
		Then I should see "Stop accepting passive checks"
		And I should see "linux-server1"
		When I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Stop obsessing over this host"
		Then I should see "Stop obsessing over this host"
		And I should see "linux-server1"
		When I click "Submit"
		Then I should see "Your command was successfully submitted"
		And I click "Done"
		When I click "Start obsessing over this host"
		Then I should be on url "/monitor/index.php/cmd?command=start_obsessing&table=hosts&object=linux-server1"

	@asmonitor @case-650 @integration
	Scenario: Service details filter
		Verify that filter links work as expected

		Given I am on the Service details page
		Then I should see the configured services
		Then Link "Services total" should contain "4"
		And I click link "Services total"
		Then I should see the configured services

	@asmonitor @case-650 @integration
	Scenario: Service details filter
		Verify that filter link counts are correct
		for various states

		Given I have submitted a passive service check result "linux-server2;System Load;2;some output"
		And I have submitted a passive service check result "linux-server1;System Load;1;some output"
		And I am on the Service details page
		Then I should see the configured services
		And Link "Services total" should contain "4"
		And Link "Services critical" should contain "1"
		And Link "Services warning" should contain "1"


	@asmonitor @case-650 @integration
	Scenario: Service details filter
		Verify that I can go back to showing all by
		services after having filtered on Ok ones
		by clicking the table name.

		Given I have submitted a passive service check result "linux-server2;System Load;2;some output"
		And I am on the Service details page
		Then I should see the configured services
		And Link "Services total" should contain "4"
		And Link "Services critical" should contain "1"
		When I click link "Services critical"
		Then I should see "linux-server2"
		And I should see "System Load"
		But I shouldn't see "PING"
		When I click link "Services"
		Then I should see the configured services

	@asmonitor @case-655 @integration
	Scenario: Service extinfo page service commands
		Test disabling active checks of service from
		service extinfo page.

		Given I have submitted a passive service check result "linux-server1;System Load;0;Everything was OK"
		And I am on the Service details page
		When I click "System Load"
		And I click "Disable active checks"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Active checks" should be shown as "Disabled"

	@asmonitor @case-655 @integration
	Scenario: Service extinfo page service commands
		Test rescheduling next check from service extinfo page.

		Given I have submitted a passive service check result "linux-server1;System Load;0;Everything was OK"
		And I am on the Service details page
		When I click "System Load"
		And I click "Re-schedule next service check"
		And I enter the time in 5 minutes into "field_check_time"
		And I note the value of "field_check_time"
		And I check "field_forced"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Next scheduled active check" should be shown as the value of "field_check_time"

	@asmonitor @case-655 @integration
	Scenario: Service extinfo page service commands
		Test submitting a passive check result from the service
		extinfo page.

		Given I have submitted a passive service check result "linux-server1;System Load;0;Everything was OK"
		And I am on the Service details page
		When I click "System Load"
		And I click "Submit passive check"
		And I select "Critical" from "field_status_code"
		And I enter "Something went horribly wrong!" into "field_plugin_output"
		And I enter "2" into "field_perf_data"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Current status" should be shown as "Critical"

	@asmonitor @case-656 @integration
	Scenario: Service extinfo page check performance graph link
		Verify that the performance graph link on the extinfo page for a given service
		point to the right place.

		Given I have PNP data for "linux-server1;System Load"
		And I am on the Service details page
		When I click "System Load"
		And I click "Show performance graph"
		Then I should be on url "/monitor/index.php/pnp/?host=linux-server1&srv=System%20Load"
		And I should see "linux-server1" within frame "iframe"

	@asmonitor @case-657 @integration
	Scenario: Service details Add/delete comment
		Verify that adding and deleting comments on services
		works.

		Given I am on the Service details page
		When I click "System Load"
		And I click "Add a new comment"
		And I enter "A comment for this service" into "field_comment"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I should see "A comment for this service"
		When I click "Delete comment"
		Then I should see "Delete comment"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then I shouldn't see "A comment for service host"

	@asmonitor @bug-6780 @integration
	Scenario: Unhandled problems - host in downtime
		Verify that hosts in downtime doesn't appear in unhandled problems

		Given I am on the main page
		When I have submitted a passive host check result "linux-server2;1;some output"
		And I click "uh_host_problems"
		Then I should see "linux-server2"
		When I have host "linux-server2" in downtime
		And I reload the page
		Then I shouldn't see "linux-server2"

	@asmonitor @bug-6780 @integration
	Scenario: Unhandled problems - service in downtime
		Verify that a service in downtime doesn't appear in unhandled problems

		Given I am on the main page
		And I have submitted a passive host check result "linux-server1;0;Under load"
		And I have submitted a passive service check result "linux-server1;System Load;2;Under load"
		And I click "uh_service_problems"
		Then I should see "linux-server1"
		And I should see "System Load"
		When I have service "linux-server1;System Load" in downtime
		And I reload the page
		Then I shouldn't see "linux-server1"
		And I shouldn't see "System Load"


	@asmonitor @bug-6780 @integration
	Scenario: Unhandled problems - service on host in downtime
		Verify that a service on a host in downtime doesn't appear in unhandled problems

		Given I have submitted a passive host check result "linux-server1;0;Under load"
		And I have submitted a passive service check result "linux-server1;System Load;2;Under load"
		And I am on address "/monitor/index.php/listview/?q=%5Bservices%5D%20in%20%22unhandled%20service%20problems%22%20or%20host%20in%20%22unhandled%20host%20problems%22"
		Then I should see "linux-server1"
		And I should see "System Load"
		When I have host "linux-server1" in downtime
		And I reload the page
		Then I shouldn't see "linux-server1"
		And I shouldn't see "System Load"

	@asmonitor @bug-7870 @integration
	Scenario: I can use commands
		When I hover over the "Manage" menu
		And I click "Process information"
		And I click "Disable notifications"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then I shouldn't see "Disable notifications"
		And I should see "Enable notifications"

	@asmonitor @bug-8022 @integration
	Scenario: Service comments in list view
		Verify that service comments are shown when hovering the "comments" icon for a service in the list views

		Given I am on the Service details page
		When I click "System Load"
		And I click "Add a new comment"
		And I enter "Zombocom" into "comment"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I should see "Zombocom"
		And I am on the Service details page
		And I hover over "Comments"
		Then I should see "Zombocom"
