Feature: Monitoring

	Background:
		Given I have these host groups configured:
			| Name				| Hosts 					 	|
			| LinuxServers		| linux-server1,linux-server2	|
			| WindowsServers    | win-server1,win-server2		|

		And I have these services:
			| Description	| Host 			| CheckCommand		| Notifications |
			| System Load	| linux-server1 | check_nrpe!load	| Enabled		|
			| System Load	| linux-server2 | check_nrpe!load	| Enabled		|
			| PING			| win-server1 	| check_ping		| Enabled		|
			| PING			| win-server2 	| check_ping		| Disabled		|
		And I have activated the configuration
		And I am on the Host details page

	@configuration @asmonitor @case-642
	Scenario: Host details page links

		Ensure that all links on the host details
		page work, and verify the tables' content
		reflects the current configuration.

		When I click "Service status detail"
		Then I should see the configured services
		When I click "Status overview"
		Then I should see the configured hosts
		When I click "Status summary"
		Then I should see the configured hostgroups

	@configuration @asmonitor @case-643
	Scenario: Host details filter

		Ensure that the filters on the host details
		page works as expected.

		When I click "4 Hosts"
		Then I should see the configured hosts
		When I click "4 Services"
		Then I should see the configured services
		When I have submitted a passive host check result "linux-server2;1;some output"
		And I click "Refresh"
		And I click "1 Down"
		Then I should see "linux-server2"

	@configuration @asmonitor @case-645
	Scenario: Host details extinfo page check links

		Verify that all links on the extinfo page for a given host
		point to the right place. Status detail link.

		When I click "linux-server1"
		And I click "Status detail"
		Then I should be on url "/monitor/index.php/status/service?name=linux-server1"

	@configuration @asmonitor @case-645
	Scenario: Host details extinfo page check links

		Verify that all links on the extinfo page for a given host
		point to the right place. Alert history link.

		When I click "linux-server1"
		And I click "Alert history"
		Then I should be on url "/monitor/index.php/alert_history/generate?host_name[]=linux-server1"

	@configuration @asmonitor @case-645
	Scenario: Host details extinfo page check links

		Verify that all links on the extinfo page for a given host
		point to the right place. Alert histogram link.

		When I click "linux-server1"
		And I click "Alert histogram"
		Then I should be on url "/monitor/index.php/histogram/generate?host_name[]=linux-server1"

	@configuration @asmonitor @case-645
	Scenario: Host details extinfo page check links

		Verify that all links on the extinfo page for a given host
		point to the right place. Availability report link.

		When I click "linux-server1"
		And I click "Availability report"
		Then I should be on url "/monitor/index.php/avail/generate?host_name[]=linux-server1"

	@configuration @asmonitor @case-645
	Scenario: Host details extinfo page check links

		Verify that all links on the extinfo page for a given host
		point to the right place. Notifications link.

		When I click "linux-server1"
		And I click "Notifications"
		Then I should be on address "/monitor/index.php/notifications/host/linux-server1"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Locate host on map

		Verify that the "Locate host on map" host command
		works correctly.

		When I click "linux-server1"
		And I click "Locate host on map"
		Then I should be on address "/monitor/index.php/nagvis/automap/host/linux-server1"
		And I should see "linux-server1"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Disable active checks

		Verify that the "Disable active checks" host command
		works correctly.

		When I click "linux-server1"
		And I click "Disable active checks"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Active checks" should be shown as "Disabled"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Reschedule check

		Verify that the "Reschedule next check" host command
		works correctly.

		When I click "linux-server1"
		And I click "Re-schedule the next check"
		And I note the value of "field_check_time"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Next scheduled active check" should be shown as the value of "field_check_time"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Submit passive check

		Verify that the "Submit passive check" host command
		works correctly.

		When I click "linux-server1"
		And I click "Submit passive check"
		And I select "Down" from "field_status_code"
		And I enter "Some output" into "field_plugin_output"
		And I enter "2" into "field__perfdata"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Current status" should be shown as "Down"


	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Disable passive check

		Verify that the "Disable passive check" host command
		works correctly.

		When I click "linux-server1"
		And I click "Stop accepting passive checks"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Passive checks" should be shown as "Disabled"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Disable notifications

		Verify that the "Disable notifications" host command
		works correctly.

		When I click "linux-server1"
		And I click "Disable notifications"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Notifications" should be shown as "Disabled"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Send custom host notification

		Verify that the "Send custom host notification" host command
		works correctly.

		When I click "linux-server1"
		And I click "Send custom host notification"
		And I enter "Some comment" into "cmd_param[comment]"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Notifications"
		Then I should see "linux-server1"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Schedule downtime

		Verify that the "Schedule downtime" host command
		works correctly.

		When I click "linux-server1"
		And I click "Schedule downtime"
		And I enter "Some comment" into "cmd_param[comment]"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Refresh"
		Then I should see "This host has been scheduled for fixed downtime"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Stop obsessing

		Verify that the "Stop obsessing" host command
		works correctly.

		When I click "linux-server1"
		And I click "Stop obsessing"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then "Obsessing" should be shown as "Disabled"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Disable notifications for services

		Verify that the "Disable notifications for all services"
		host command works correctly.

		When I click "linux-server1"
		And I click "Disable notifications for all services"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "System Load"
		Then "Notifications" should be shown as "Disabled"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Enable notifications for services

		Verify that the "Enable notifications for all services"
		host command works correctly.

		When I click "linux-server1"
		And I click "Enable notifications for all services"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "System Load"
		Then "Notifications" should be shown as "Enabled"
