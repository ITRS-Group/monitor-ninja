Feature: Monitoring

	Background:
		Given I have these host groups configured:
			| Name				| Hosts 					 	|
			| LinuxServers		| linux-server1,linux-server2	|
			| WindowsServers    | win-server1,win-server2		|

		And I have these services:
			| Description	| Host 			| CheckCommand		|
			| System Load	| linux-server1 | check_nrpe!load	| 
			| System Load	| linux-server2 | check_nrpe!load	| 
			| PING			| win-server1 	| check_ping		| 
			| PING			| win-server2 	| check_ping		|
		And I have activated the configuration

	@configuration @asmonitor @case-642
	Scenario: Host details page links

		Ensure that all links on the host details
		page work, and verify the tables' content
		reflects the current configuration.

		When I am on the Host details page
		And I click "Service status detail"
		Then I should see the configured services
		When I click "Status overview"
		Then I should see the configured hosts
		When I click "Status summary"
		Then I should see the configured hostgroups

	@configuration @asmonitor @case-643
	Scenario: Host details filter

		Ensure that the filters on the host details
		page works as expected.

		When I am on the Host details page
		And I click "4 Hosts"
		Then I should see the configured hosts
		When I click "4 Services"
		Then I should see the configured services
		When I have submitted a passive host check result "linux-server2;1;some output"
		And I click "Down"
		Then I should see "linux-server2"
