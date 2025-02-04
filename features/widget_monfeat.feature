# TODO: Fix this test when we have a new default TAC
#@skip
Feature: Monitoring features widget
	#Background:
	#	Given I have these mocked status
	#		| enable_flap_detection | enable_notifications | enable_event_handlers | execute_service_checks | execute_host_checks | accept_passive_service_checks | accept_passive_host_checks |
	#		| 1                     | 1                    | 1                     | 1                      | 1                   | 1                             | 1                          |

	@gian_edited
	Scenario: Monitoring features should display command links
		When I am logged in
		And I am on the main page
		And I hover over the element with data-menu-id "dashboard_options"
		And I hover over the element with data-menu-id "add_widget"
		And I click the element with data-menu-id "monitoring_features"
		Then I should see "Monitoring features"
		And I should see link "Flap detection enabled"
		And I should see link "Notifications enabled"
		And I should see link "Event handlers enabled"
		And I should see link "Active Host checks enabled"
		And I should see link "Active Service checks enabled"
		And I should see link "Passive Host checks enabled"
		And I should see link "Passive Service checks enabled"
