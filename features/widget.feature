@widgets
Feature: Widgets

	@unreliable @unreliable_el7
	Scenario: External widget listview
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I have these mocked dashboard_widgets
			| id | dashboard_id | name      | position      | setting                       |
			| 1  | 1            | tac_hosts | {"c":0,"p":0} | {"title":"A friendly widget"} |
		And I have these mocked hosts
			| name        |
			| Kira Powers |
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

	Scenario: Widget settings when widget uses new conditional forms

		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		Given I have these mocked dashboard_widgets
			|id|dashboard_id | name         | position      | setting |
			|1 |1            | bignumber    | {"c":0,"p":0} | {"title":"My widget name"}|

		And I am logged in
		When I am on the main page
		Then I should see "My widget name"
		When I edit widget "My widget name"
		Then I select "Host" from "content_from"
		Then the hidden required field "main_filter_id" should not be required

	Scenario: Big number widgets with zero result
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I have these mocked dashboard_widgets
			|id|dashboard_id | name         | position      | setting |
			|1 |1            | bignumber    | {"c":0,"p":0} | {"title":"bignumber widget with zero result1","refresh_interval":"60","content_from":"filter","main_filter_id":"-51","selection_filter_id":"-50","display_type":"number_of_total","threshold_onoff":true,"threshold_type":"less_than","threshold_warn":"95","threshold_crit":"90"} |
			|2 |1            | bignumber    | {"c":1,"p":0} | {"title":"bignumber widget with zero result2","refresh_interval":"60","content_from":"filter","main_filter_id":"-51","selection_filter_id":"-50","display_type":"number_of_total","threshold_onoff":true,"threshold_type":"less_than","threshold_warn":"0","threshold_crit":"0"} |
		And I have these mocked hosts
			| name        |state|last_check| plugin_output    |
			| Jadyn Elvan | 0   | 99999    | Gabba-gabba-hey! |

		And I am logged in
		When I am on the main page
		Then I should see "0 / 0"
		And I should see css ".critical"
		And I should see css ".ok"

	@gian
	Scenario: Add widget - Acknowledged problems
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "acknowledged_problems"
		Then I should see the span with class "widget-title" and text "Acknowledged problems"
	
	@gian
	Scenario: Add widget - Acknowledged service problems
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "acknowledged_service_problems"
		Then I should see the span with class "widget-title" and text "Acknowledged service problems"
	
	@gian
	Scenario: Add widget - Big numbers
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "big_numbers"
		Then I should see the span with class "widget-title" and text "All hosts: OK hosts"

	@gian
	Scenario: Add widget - Business Services
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "business_services"
		Then I should see the span with class "widget-title" and text "Business Services"
	
	@gian
	Scenario: Add widget - Disabled checks
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "disabled_checks"
		Then I should see the span with class "widget-title" and text "Disabled checks"
	
	@gian
	Scenario: Add widget - Geomap
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "geomap"
		Then I should see the span with class "widget-title" and text "Geomap"
	
	@gian
	Scenario: Add widget - Getting started with OP5 Monitor
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "getting_started_with_op5_monitor"
		Then I should see the span with class "widget-title" and text "Getting started with OP5 Monitor"
	
	@gian
	Scenario: Add widget - Host performance
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "host_performance"
		Then I should see the span with class "widget-title" and text "Host performance"
	
	@gian
	Scenario: Add widget - Hosts
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "hosts"
		Then I should see the span with class "widget-title" and text "Hosts"

	@gian
	Scenario: Add widget - List View
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "list_view"
		Then I should see the span with class "widget-title" and text "List of hosts"
	
	@gian
	Scenario: Add widget - Merlin Node Status
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "merlin_node_status"
		Then I should see the span with class "widget-title" and text "Merlin node status"

	@gian
	Scenario: Add widget - Monitoring features
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "monitoring_features"
		Then I should see the span with class "widget-title" and text "Monitoring features"

	@gian
	Scenario: Add widget - Monitoring performance
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "monitoring_performance"
		Then I should see the span with class "widget-title" and text "Monitoring performance"
	
	@gian
	Scenario: Add widget - NagVis
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "nagvis"
		Then I should see the span with class "widget-title" and text "automap"

	@gian
	Scenario: Add widget - Network health
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "network_health"
		Then I should see the span with class "widget-title" and text "Network health"

	@gian
	Scenario: Add widget - Network outages
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "network_outages"
		Then I should see the span with class "widget-title" and text "Network outages"

	@gian
	Scenario: Add widget - Network outages
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "scheduled_downtime"
		Then I should see the span with class "widget-title" and text "Scheduled downtime"

	@gian
	Scenario: Add widget - Services
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "services"
		Then I should see the span with class "widget-title" and text "Services"

	@gian
	Scenario: Add widget - State summary
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "state_summary"
		Then I should see the span with class "widget-title" and text "State Summary of \"All hosts\""

	@gian
	Scenario: Add widget - Table Stat
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "table_stat"
		Then I should see the span with class "widget-title" and text "Table Stat"

	@gian
	Scenario: Add widget - Unacknowledged service problems
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "unacknowledged_service_problems"
		Then I should see the span with class "widget-title" and text "Unacknowledged service problems"
	
	@gian
	Scenario: Add widget - Unhandled problems
		Given I am logged in as administrator
		And I am on the main page
		When I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "unhandled_problems"
		Then I should see the span with class "widget-title" and text "Unhandled problems"