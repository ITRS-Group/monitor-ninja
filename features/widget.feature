@widgets
Feature: Widgets

	@unreliable
	Scenario: External widget listview
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I have these mocked dashboard_widgets
			| id | dashboard_id | name      | position      | setting                       |
			| 1  | 1            | tac_hosts | {"c":0,"p":0} | {"title":"A friendly widget"} |
		And I have these mocked hosts
			| name			|
			| Kira Powers   |
		Given I am logged in
		And I expose the widget "listview"
		And I am on address "/index.php/external_widget/listview"
		Then I should see "Kira Powers"
		When I am on the main page
		Then I should see "Password"

	Scenario: Requesting non-existing widget
		When I expose the widget "tac_problems"
		And I am on address "/index.php/external_widget/pippilottarullgardina"
		Then I should see "Widget not found"
		When I am on the main page
		Then I should see "Password"

	Scenario: User configured name overrides default
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		Given I have these mocked dashboard_widgets
			|id|dashboard_id | name         | position      | setting |
			|1 |1            | listview     | {"c":0,"p":0} | {"title":"My widget name"}|
		And I am logged in
		When I am on the main page
		Then I should see "My widget name"

	Scenario: Listview widgets are rendered on TAC with default title
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		Given I have these mocked dashboard_widgets
			|id|dashboard_id | name         | position      | setting |
			|1 |1            | listview     | {"c":0,"p":0} | {} |
		And I am logged in
		When I am on the main page
		# Default filter table for listview widget is hosts
		Then I should see "List of hosts"

	Scenario: Non-installed widgets are reported as such
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I have these mocked dashboard_widgets
			| id | dashboard_id | name                      | position      | setting                         |
			| 1  | 1            | not-an-actual-widget-type | {"c":0,"p":0} | {"title":"An imaginary widget"} |
		And I am logged in
		When I am on the main page
		Then I should see "An imaginary widget"
		And I should see "Widget type 'not-an-actual-widget-type' does not seem to be installed"

	Scenario: Widgets that fails to render are rendered with error message
		Given I have a widget that fails to render with error message "Widget failed to render"
		And I am logged in
		And I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I have these mocked dashboard_widgets
			| id | dashboard_id | name         | position      | setting                       |
			| 1  | 1            | unrenderable | {"c":0,"p":0} | {"title":"Unrendered Widget"} |
		And I am on the main page
		Then I should see "Widget failed to render"
		And I should see "Widget unrenderable"
		But I shouldn't see "Stack Trace"

	Scenario: Widgets that fail to instantiate are rendered with error message
		Given I have a broken widget with error message "This is a dead widget"
		And I am logged in
		And I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I have these mocked dashboard_widgets
			| id | dashboard_id | name   | position      | setting                   |
			| 1  | 1            | broken | {"c":0,"p":0} | {"title":"Broken Widget"} |
		And I am on the main page
		Then I should see "This is a dead widget"
		And I should see "Broken Widget"
		But I shouldn't see "Stack Trace"

	@MON-8504
	Scenario: Listview widgets with custom columns render correctly
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I have these mocked dashboard_widgets
			|id|dashboard_id | name         | position      | setting |
			|1 |1            | listview     | {"c":0,"p":0} | {"query":"[hosts] name = \"Jadyn Elvan\"","columns":"state, name, last_check, status_information","limit":"20","order":""} |
			|2 |1            | listview     | {"c":1,"p":0} | {"query":"[services] description = \"Reyes Kennedy\"","columns":"state, description, last_check, status_information","limit":"20","order":""} |
		And I have these mocked hosts
			| name        |state|last_check| plugin_output    |
			| Jadyn Elvan | 0   | 99999    | Gabba-gabba-hey! |
		And I have these mocked services
			| host        | description   | state | last_check | plugin_output   |
			| Jadyn Elvan | Reyes Kennedy | 1     | 12341234   | I AM THE BATMAN |

		And I am logged in
		When I am on the main page
		# Default filter table for listview widget is hosts
		Then I should see these strings
			| List of hosts    |
			| Jadyn Elvan      |
			| Gabba-gabba-hey! |
			| List of services |
			| Reyes Kennedy    |
			| I AM THE BATMAN  |

		And I should see css ".widget-content span[class='icon-16 x16-shield-up']"
		And I should see css ".widget-content span[class='icon-16 x16-shield-warning']"
