Feature: Shared Dashboard

	Background:
		Given I have these mocked dashboards
			| id | name       | username   | layout | read_perm |
			| 101 | Dashboard101 | user-a | 1,2,3  | ,1,2,3,4,5,6,7,8,9,10,        |
		Given I have these mocked dashboard_widgets
			| id | dashboard_id | name         | position      | setting |
			|101 |101           | listview     | {"c":0,"p":0} | {"title":"My Listview"}|

	Scenario: View shared dashboard in own user account
		Given I am logged in as "user-a"
		When I am on the main page
		Then I should see "My Listview"
		And I should see css ".editable"

	Scenario: View shared dashboard in shared user account
		Given I am logged in as "user-b"
		When I am on the main page
		Then I should see "My Listview"
		And I shouldn't see css ".widget-editlink"
