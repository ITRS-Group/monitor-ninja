# TODO: Fix this test when we have a new default TAC
@skip
Feature: Monitoring features widget
	Background:
		Given I have these mocked status
			| enable_flap_detection | enable_notifications | enable_event_handlers | execute_service_checks | execute_host_checks | accept_passive_service_checks | accept_passive_host_checks |
			| 1                     | 1                    | 1                     | 1                      | 1                   | 1                             | 1                          |

	Scenario: Monitoring features should display command links
		When I am logged in
		And I am on the main page
		Then I should see "Monitoring features"
		And I should see link "Flap detection enabled"
		And I should see link "Notifications enabled"
		And I should see link "Event handlers enabled"
		And I should see link "Active Host checks enabled"
		And I should see link "Active Service checks enabled"
		And I should see link "Passive Host checks enabled"
		And I should see link "Passive Service checks enabled"
