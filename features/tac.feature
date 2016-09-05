@tac
Feature: Tactical Overview, TAC

	Background:
		Given I have these mocked status
			| enable_flap_detection | enable_notifications | enable_event_handlers | execute_service_checks | execute_host_checks | accept_passive_service_checks | accept_passive_host_checks |
			| 1                     | 1                    | 1                     | 1                      | 1                   | 1                             | 1                          |
		And I am logged in
		And I am on the main page

	Scenario: No failed widget should be visible
		Then I shouldn't see "This widget failed to load"

	Scenario: Info about no dashboard
		Then I should see "No dashboard"

	Scenario: Info about no widgets
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I am on the main page
		Then I should see "No widgets"

	Scenario: Create dashboard
		When I hover over the "Dashboards" menu
		Then I should see menu items:
			| New dashboard |
		And I click "New dashboard"
		Then I should see "New dashboard"
		And I enter "Ny däshbörd" into "name"
		And I click "Save"
		Then I should see "Ny däshbörd"
		And I should see "No widgets"
		And I delete all dashboards

	Scenario: Switch dashboard
		When I hover over the "Dashboards" menu
		And I click "New dashboard"
		And I enter "Ny däshbörd1" into "name"
		And I click "Save"
		And I hover over the "Dashboards" menu
		And I click "New dashboard"
		And I enter "Ny däshbörd2" into "name"
		And I click "Save"
		Then I should see "Ny däshbörd2"
		And I hover over the "Dashboards" menu
		Then I should see menu items:
			| Ny däshbörd1 |
		And I click "Ny däshbörd1"
		Then I should see "Ny däshbörd1"
		And I delete all dashboards

	Scenario: Delete dashboard
		When I hover over the "Dashboards" menu
		And I click "New dashboard"
		And I enter "Ny däshbörd1" into "name"
		And I click "Save"
		And I hover over the "Dashboard options" menu
		And I click "Delete this dashboard"
		And I click "Yes"
		Then I shouldn't see "Ny däshbörd1"
		And I should see "No dashboard"

	Scenario: Rename dashboard
		When I hover over the "Dashboards" menu
		And I click "New dashboard"
		And I enter "Ny däshbörd1" into "name"
		And I click "Save"
		And I hover over the "Dashboard options" menu
		And I click "Rename this dashboard"
		Then I should see "Rename dashboard"
		And I enter "Kallekula" into "name"
		And I click "Save"
		Then I should see "Kallekula"
		And I shouldn't see "Ny däshbörd1"
		And I delete all dashboards

	Scenario: Dashboard options are available for my dashboard
		Given I have these mocked dashboards
			| id | name       | username   | layout |
			| 1  | Dashboard1 | mockeduser | 1,2,3  |
		And I am on the main page
		When I hover over the "Dashboard options" menu
		Then I should see menu items:
			| Add widget |
			| Select layout |
			| Set as login dashboard |
			| Rename this dashboard |
			| Share this dashboard |
			| Delete this dashboard |

	Scenario: Dashboard options are not available for shared dashboard
		Given I have these mocked dashboards
			| id | name       | username    | layout | read_perm |
			| 1  | Dashboard1 | anotheruser | 1,2,3  | ,1,       |
		And I have these mocked permission_quarks
			| id | type       | name        |
			| 1  | user       | mockeduser  |
		And I am on the main page
		When I hover over the "Dashboard options" menu
		Then I should see menu items:
			| Set as login dashboard |
		Then I should not see menu items:
			| Add widget |
			| Select layout |
			| Rename this dashboard |
			| Share this dashboard |
			| Delete this dashboard |
	Scenario: Dashboard overview
		Given I have these mocked dashboards
			| id | name         | username   | layout |
			| 1  | Mai Kawasaki | mockeduser | 1,2,3  |
			| 2  | Tami Foster  | mockeduser | 1,2,3  |
		When I go to the listview for [dashboards] all
		Then I should see "Mai Kawasaki"
		And I should see "Tami Foster"
