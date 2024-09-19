Feature: Dashboards

	Background:
		Given I am logged in as administrator
		And I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | administrator | 1,2,3  |
		And I have these mocked dashboard_widgets
			| id | dashboard_id | name      | position      | setting                       |
			| 1  | 1            | tac_hosts | {"c":0,"p":0} | {"title":"A friendly widget"} |

	@gian
	Scenario: Create new dashboard
		When I create a new dashboard with name "My new dashboard"
		Then I should see "My new dashboard"

	@gian
	Scenario: Edit dashboard
		When I create a new dashboard with name "My new dashboard"
		And I hover over the "Dashboards" menu
		And I click the span with text "My new dashboard"
		And I hover over the "Dashboard options" menu
		Then I should see all elements in the UI
		#And I click the element with data-menu-id "rename_this_dashboard"
		#And I enter "Renamed dashboard" into "name"
		#And I click "save"
		#Then I should see "Renamed dashboard"
	
	@gian
	Scenario: Delete dashboard
		When I hover over the "Dashboards" menu
		Then I should see all elements in the UI
		#And I click the span with text "Renamed dashboard"
		#And I click the element with data-menu-id "delete_this_dashboard"
		#And I click "yes"
		#Then I shouldn't see "Renamed dashboard"


