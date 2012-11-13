@configuration
Feature: Monitoring

	Background:
		Given A configuration like this:
			| Host			|
			| linux-server1 |
			| linux-server2 |
			| linux-server3 |

	@asmonitor @case-642
	Scenario: Host details page links

		Ensure that all links on the host details
		page work, and verify the tables' content
		reflects the current configuration.

		Given I am on address "/monitor/index.php/status/host/all"
		Then I should see the configured hosts
