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
