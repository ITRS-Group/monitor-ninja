Feature: Filters & list views

	@configuration @asmonitor @filters
	@bug-7012 @todo
	Scenario: Service multi-delete
		Given I have these hosts:
			| host_name     |
			| linux-server1 |
		And I have these services:
			| service_description | host_name     | check_command   | notifications_enabled | active_checks_enabled |
			| System Load         | linux-server1 | check_nrpe!load | 1                     | 1                     |
			| PING                | linux-server1   | check_ping      | 1                     | 0                     |
		And I have activated the configuration
		And I'm on the list view for query "[services] state != 200 and acknowledged = 0"
		Then I should see "System Load"
		And I should see "PING"
		When I check "select_all"
		And I click "Send multi action"
		And I select "Delete services" from "multi_action"
		And I click "Submit"
		Then I should be on the Configure page
		Then I should see "There are 2 changes to 2 service objects" within frame "iframe"
		When I click "More info" within frame "iframe"
		Then I should see "Deleted service object linux-server1;System Load" within frame "iframe"
		And I should see "Deleted service object linux-server1;PING" within frame "iframe"
		Then I should see button "Save objects I have changed" within frame "iframe"
		And I should see button "Save everything" within frame "iframe"
		And I click button "Save objects I have changed" within frame "iframe"
		Then I should see "Preflight configuration turned out ok." within frame "iframe"


	@configuration @asmonitor @filters
	Scenario: List hosts
		Given I have these hosts:
			| host_name |
			| linux-server1 |
			| linux-server2 |
			| linux-server3 |
			| linux-server4 |
			| linux-server5 |
		And I have these services:
			| service_description | host_name		| check_command	|
			| PING                | linux-server1   | check_ping	|
			| PING                | linux-server2   | check_ping	|
			| PING                | linux-server3   | check_ping	|
			| PING                | linux-server4   | check_ping	|
			| PING                | linux-server5   | check_ping	|
		And I have activated the configuration
		And I'm on the list view for query "[hosts] all"
		Then I should see the configured hosts

	@configuration @asmonitor @filters
	Scenario: List hosts
		Given I have these hosts:
			| host_name |
			| linux-server1 |
			| linux-server2 |
			| linux-server3 |
			| linux-server4 |
			| linux-server5 |
		And I have these services:
			| service_description | host_name		| check_command	|
			| PING                | linux-server1   | check_ping	|
			| PING                | linux-server2   | check_ping	|
			| PING                | linux-server3   | check_ping	|
			| PING                | linux-server4   | check_ping	|
			| PING                | linux-server5   | check_ping	|
		And I have activated the configuration
		And I'm on the list view for query "[services] all"
		Then I should see the configured services
		And I should see the configured hosts
