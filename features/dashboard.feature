Feature: Dashboards

	Background:
		Given I am logged in as administrator
		And I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | administrator | 1,2,3  |
		And I have these mocked dashboard_widgets
			| id | dashboard_id | name      | position      | setting                       |
			| 1  | 1            | tac_hosts | {"c":0,"p":0} | {"title":"A friendly widget"} |

	Scenario: Create new dashboard
		When I create a new dashboard with name "My new dashboard"
		Then I should see "My new dashboard"
		And I should see all elements in the UI

	@addedhappypath
	Scenario: Edit dashboard
<<<<<<< HEAD
		When I create a new dashboard with name "My new dashboard"
		And I hover over the "Dashboards" menu
		And I click the span with text "My new dashboard"
		And I hover over the element with data-menu-id "dashboard_options"
=======
		When I click the span with text "My new dashboard"
>>>>>>> 8b34fe267 (Added new step definition to proceed with edit and delete dashboard)
		And I click the element with data-menu-id "rename_this_dashboard"
		And I enter "Renamed dashboard" into "name"
		And I click "save"
		Then I should see "Renamed dashboard"
	
	@addedhappypath
	Scenario: Delete dashboard
<<<<<<< HEAD
		When I create a new dashboard with name "My new dashboard"
		And I hover over the "Dashboards" menu
		And I click the span with text "My new dashboard"
		And I hover over the element with data-menu-id "dashboard_options"
		And I click the element with data-menu-id "delete_this_dashboard"
		And I click "yes"
		Then I shouldn't see "My new dashboard"
=======
		When I click the span with text "Renamed dashboard"
		And I click the element with data-menu-id "delete_this_dashboard"
		And I click "yes"
		Then I shouldn't see "Renamed dashboard"
>>>>>>> 8b34fe267 (Added new step definition to proceed with edit and delete dashboard)


