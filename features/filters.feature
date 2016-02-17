@filters @listview
Feature: Filters & list views

	Scenario: List hosts
		Given I have these mocked hosts
			| name           |
			| Hue Vong       |
			| Imelda Angeles |
			| Manami Ikeda   |
			| Hoa Mi Chu     |
			| Yoki Houston   |
		And I am logged in
		When I go to the listview for [hosts] all
		Then I should see the mocked hosts

	Scenario: List hosts
		Given I have these mocked hosts
			| name |
			| Mai Kawasaki |
			| Tami Foster |
			| Tomasine Vogelstein |
			| Hue Tram |
			| Eun Shim |
		And I have these mocked services
			| description      | host |
			| Liam Monroy      | Mai Kawasaki |
			| Dae-Hyun Kim     | Tami Foster |
			| Julius Camarillo | Tomasine Vogelstein |
			| De Pham          | Hue Tram |
			| Dong-Sun Cheung  | Eun Shim |
		And I am logged in
		When I go to the listview for [services] all
		Then I should see the mocked services
		And I should see the mocked hosts

	Scenario: List hosts
		Given I have these mocked hosts
			| name |
			| Irish Acosta |
			| Najma Ashraf |
		And I have these mocked services
			| description      | host |
			| Molly Eisenstadt | Irish Acosta |
			| Anming Gu        | Najma Ashraf |
		And I am logged in
		When I go to the listview for [services] all
		Then I should see the mocked services
		And I should see "Irish Acosta"
		And I should see "Najma Ashraf"

	Scenario: List services with granular filter
		Ensure that filters work even when we specify more limiting
		filters.
		Given I have these mocked hosts
			| name           |
			| Zuzela Adkins  |
			| Zuzela Griffin |
		And I have these mocked services
			| description | host           | notifications_enabled | active_checks_enabled |
			| Munny Sum   | Zuzela Adkins  | 1                     | 0                     |
			| De Lieu     | Zuzela Griffin | 0                     | 1                     |
		And I am logged in
		When I go to the listview for [services] active_checks_enabled = 0 and notifications_enabled = 1
		Then I should see "Munny Sum"
		And I should see "Zuzela Adkins"
		But I shouldn't see "Zuzela Griffin"

	@configuration
	Scenario: Service detail listing column sorting
		Ensure that it is possible to sort by the columns in the listing.
		Sort by description.
		Given I have these hosts:
			| host_name |
			| linux-server1 |
		And I have these services:
			| service_description	| host_name		| check_command |
			| A-service				| linux-server1 | check_ping	|
			| B-service				| linux-server1 | check_ping	|
			| C-service				| linux-server1 | check_ping	|
			| D-service				| linux-server1 | check_ping	|
		And I have activated the configuration
		Given I am logged in as administrator
		And I am on the Service details page
		When I sort the filter result table by "description"
		Then The first row of the filter result table should contain "A-service"
		And The last row of the filter result table should contain "D-service"
		When I sort the filter result table by "description"
		Then The first row of the filter result table should contain "D-service"
		And The last row of the filter result table should contain "A-service"

	@configuration
	Scenario: Service detail listing column sorting
		Ensure that it is possible to sort by the columns in the listing.
		Sort by last checked.
		Given I have these hosts:
			| host_name |
			| linux-server1 |
		And I have these services:
			| service_description	| host_name		| check_command |
			| A-service				| linux-server1 | check_ping	|
			| B-service				| linux-server1 | check_ping	|
			| C-service				| linux-server1 | check_ping	|
			| D-service				| linux-server1 | check_ping	|
		And I have activated the configuration
		Given I am logged in as administrator
		And I have submitted a passive service check result "linux-server1;C-service;0;some output"
		And I am on the Service details page
		When I sort the filter result table by "last_check"
		Then The last row of the filter result table should contain "C-service"
		When I sort the filter result table by "last_check"
		Then The first row of the filter result table should contain "C-service"

	@configuration
	Scenario: Service detail listing column sorting
		Ensure that it is possible to sort by the columns in the listing.
		Sort by duration.
		Given I have these hosts:
			| host_name |
			| linux-server1 |
		And I have these services:
			| service_description	| host_name		| check_command |
			| A-service				| linux-server1 | check_ping	|
			| B-service				| linux-server1 | check_ping	|
			| C-service				| linux-server1 | check_ping	|
			| D-service				| linux-server1 | check_ping	|
		And I have activated the configuration
		Given I am logged in as administrator
		And I have submitted a passive service check result "linux-server1;B-service;0;some output"
		And I am on the Service details page
		When I sort the filter result table by "duration"
		Then The first row of the filter result table should contain "B-service"
		When I sort the filter result table by "duration"
		Then The last row of the filter result table should contain "B-service"

	@configuration
	Scenario: Service detail listing column sorting
		Ensure that it is possible to sort by the columns in the listing.
		Sort by status information.
		Given I have these hosts:
			| host_name |
			| linux-server1 |
		And I have these services:
			| service_description	| host_name		| check_command |
			| A-service				| linux-server1 | check_ping	|
			| B-service				| linux-server1 | check_ping	|
			| C-service				| linux-server1 | check_ping	|
			| D-service				| linux-server1 | check_ping	|
		And I have activated the configuration
		Given I am logged in as administrator
		And I have submitted a passive service check result "linux-server1;B-service;0;Apocryphal status information message"
		And I have submitted a passive service check result "linux-server1;A-service;1;Bereaved status information"
		And I have submitted a passive service check result "linux-server1;D-service;0;Curmudgeonly status information"
		And I have submitted a passive service check result "linux-server1;C-service;0;Dandy status information"
		And I am on the Service details page
		When I sort the filter result table by "status_information"
		Then The first row of the filter result table should contain "B-service"
		And The last row of the filter result table should contain "C-service"
		When I sort the filter result table by "status_information"
		Then The last row of the filter result table should contain "B-service"
		And The first row of the filter result table should contain "C-service"

	@configuration
	Scenario: Service detail listing column sorting
		Ensure that it is possible to sort by the columns in the listing.
		Sort by state.
		Given I have these hosts:
			| host_name |
			| linux-server1 |
		And I have these services:
			| service_description	| host_name		| check_command |
			| A-service				| linux-server1 | check_ping	|
			| B-service				| linux-server1 | check_ping	|
			| C-service				| linux-server1 | check_ping	|
		And I have activated the configuration
		Given I am logged in as administrator
		And I have submitted a passive service check result "linux-server1;A-service;1;Bereaved status information"
		And I have submitted a passive service check result "linux-server1;B-service;0;Apocryphal status information message"
		And I have submitted a passive service check result "linux-server1;C-service;2;Dandy status information"
		And I am on the Service details page
		When I sort the filter result table by "state"
		Then The first row of the filter result table should contain "C-service"
		And The last row of the filter result table should contain "B-service"
		When I sort the filter result table by "state"
		Then The last row of the filter result table should contain "C-service"
		And The first row of the filter result table should contain "B-service"

	@configuration @unreliable @integration
	Scenario: Save filter
		Given I am logged in as administratior
		And I am on the Host details page
		And I click "Show/Edit Text Filter"
		And I enter "Ernie" into "lsfilter_save_filter_name"
		And I click "Save Filter"
		And I wait for 1 second
		And I hover over the "Manage" menu
		When I click "Manage filters"
		Then I should see "Ernie"
		When I hover over the "Monitor" menu
		And I hover over the "Hosts" menu
		Then I should see these menu items:
			| Ernie |
