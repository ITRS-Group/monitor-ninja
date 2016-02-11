@pagination
Feature: Pagination

	@configuration @case-651
	Scenario: Service details pagination
		Verify that pagination shows 100 rows at
		a time and loads 100 new rows when requested

		Given I have these hosts:
			| host_name |
			| linux-server1 |
		And I have 300 services configured on host "linux-server1"
		And I have activated the configuration
		And I am logged in
		When I am on the Service details page
		Then Link "Services total" should contain "300"
		#+1 row for the "Load 100 more rows" row
		Then the filter result table should have 101 rows
		And I click "Load 100 more rows"
		#+1 row for the "Load 100 more rows" row
		Then the filter result table should have 201 rows
		And I click "Load 100 more rows"
		Then the filter result table should have 300 rows
		Then I should see the configured services
