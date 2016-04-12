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

	Scenario: Listview widgets are rendered on TAC
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I have these mocked dashboard_widgets
			| id | dashboard_id | name     | position      | setting                       |
			| 1  | 1            | listview | {"c":0,"p":0} | {"title":"A friendly widget"} |
		And I am logged in
		When I am on the main page
		Then I should see "A friendly widget"

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
