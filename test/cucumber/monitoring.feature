Feature: Monitoring
	Background:
		Given I have these host groups configured:
			| Name				| Hosts 					 	|
			| LinuxServers		| linux-server1,linux-server2	|
			| WindowsServers    | win-server1,win-server2		|
		And I have these services:
			| Description	| Host 			| Check command		| Notifications | Active checks |
			| System Load	| linux-server1 | check_nrpe!load	| Enabled		| Enabled		|
			| System Load	| linux-server2 | check_nrpe!load	| Enabled		| Enabled		|
			| PING			| win-server1 	| check_ping		| Enabled		| Disabled		|
			| PING			| win-server2 	| check_ping		| Disabled		| Enabled		|
		And I have activated the configuration

	@configuration @asmonitor @case-642
	Scenario: Host details page links
		Ensure that all links on the host details
		page work, and verify the tables' content
		reflects the current configuration.

		Given I am on the Host details page
		When I click "Services total"
		Then I should see the configured services

	@configuration @asmonitor @case-643
	Scenario: Host details filter
		Ensure that the filters on the host details
		page works as expected.

		Given I am on the Host details page
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

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Status detail"
		Then I should be on url "/monitor/index.php/status/service?name=linux-server1"

	@configuration @asmonitor @case-645
	Scenario: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place. Alert history link.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Alert history"
		Then I should be on url "/monitor/index.php/alert_history/generate?host_name[]=linux-server1"

	@configuration @asmonitor @case-645
	Scenario: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place. Alert histogram link.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Alert histogram"
		Then I should be on url "/monitor/index.php/histogram/generate?host_name[]=linux-server1"

	@configuration @asmonitor @case-645
	Scenario: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place. Availability report link.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Availability report"
		Then I should be on url "/monitor/index.php/avail/generate?host_name[]=linux-server1"

	@configuration @asmonitor @case-645
	Scenario: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place. Notifications link.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Notifications"
		Then I should be on address "/monitor/index.php/notifications/host/linux-server1"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Locate host on map
		Verify that the "Locate host on map" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Locate host on map"
		Then I should be on address "/monitor/index.php/nagvis/automap/host/linux-server1"
		And I should see "linux-server1"

	@configuration @asmonitor @case-646
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

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Reschedule check
		Verify that the "Reschedule next check" host command
		works correctly.

		Given I am on the Host details page
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

		Given I am on the Host details page
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


	@configuration @asmonitor @case-646
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

	@configuration @asmonitor @case-646
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

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Send custom host notification
		Verify that the "Send custom host notification" host command
		works correctly.

		Given I am on the Host details page
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

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Schedule downtime"
		And I enter "Some comment" into "cmd_param[comment]"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Refresh"
		Then I should see "This host has been scheduled for fixed downtime"
	
	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Disable notifications for services
		Verify that the "Disable notifications for all services"
		host command works correctly.

		Given I am on the Host details page
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

		Given I am on the Host details page
		When I click "win-server2"
		And I click "Enable notifications for all services"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "PING"
		Then "Notifications" should be shown as "Enabled"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Schedule check for all services
		Verify that the "Schedule check for all services" host command
		works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Schedule a check of all services"
		And I note the value of "field_check_time"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "System Load"
		Then "Next scheduled check" should be shown as the value of "field_check_time"

	@configuration @asmonitor @case-646
	Scenario: Host details host commands - Disable checks of all services
		Verify that the "Disable checks of all services" host command works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Disable checks of all services"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I click "Status detail"
		And I click "System Load"
		Then "Active checks" should be shown as "Disabled"


	@configuration @asmonitor @case-646
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

	@configuration @asmonitor @case-646
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

	@configuration @asmonitor @case-646
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

	@configuration @asmonitor @case-647
	Scenario: Host details host extinfo page configure
		Verify that the "Configure" link works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Configure"
		Then I should be on the Configure page

	@configuration @asmonitor @case-647
	Scenario: Host details host extinfo page show performance graph
		Verify that the "Show performance graph" link works correctly.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Show performance graph"
		Then I should be on the PNP page

	@configuration @asmonitor @case-648
	Scenario: Host details Add/delete comment
		Verify that adding and deleting comments on hosts
		works.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Add comment"
		And I enter "A comment for this host" into "cmd_param[comment]"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		And I should see "A comment for this host"
		When I click the delete icon for comment 1
		Then I should see "You are trying to delete a host comment"
		And I click "Submit"
		Then I should see "Your command was successfully submitted"
		When I click "Done"
		Then I shouldn't see "A comment for this host"

	@configuration @asmonitor @case-649
	Scenario: Service details check page-links
		Verify that page-links points to correct address.

		Given I am on the Host details page
		When I click "linux-server1"
		And I click "Status detail"
		And I click "System Load"
		And I click "Information for this host"
		Then I should see "linux-server1"
		And I should see "Current status"
