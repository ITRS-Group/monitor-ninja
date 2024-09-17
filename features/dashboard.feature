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
		And I hover "My new dashboard" from the "Dashboards" menu
		And I click link "My new dashboard"
		And I  hover "Rename this dashboard" from the "Dashboard options" menu
		And I click link "Rename this dashboard"
		And I enter "Renamed dashboard" into "name"
		And I click button "Save"
		Then I should see "Renamed dashboard"
	
	@gian
	Scenario: Delete dashboard
		And I hover "Renamed dashboard" from the "Dashboards" menu
		And I click link "Renamed dashboard"
		And I  hover "Delete this dashboard" from the "Dashboard options" menu
		And I click link "Delete this dashboard"
		And I click button "Delete"
		Then I shouldn't see "Renamed dashboard"