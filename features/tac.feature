@tac
Feature: Tactical Overview, TAC
	Widgets

	Background:
		Given I have these mocked status
			| enable_flap_detection | enable_notifications | enable_event_handlers | execute_service_checks | execute_host_checks | accept_passive_service_checks | accept_passive_host_checks |
			| 1                     | 1                    | 1                     | 1                      | 1                   | 1                             | 1                          |

	@widget
	Scenario: All widgets should be reachable
		Given I am logged in as administrator
		Then I shouldn't see "This widget failed to load"
