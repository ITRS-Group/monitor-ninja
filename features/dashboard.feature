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
