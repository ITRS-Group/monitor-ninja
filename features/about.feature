@menu-about
Feature: Menu About

	Background:
		Given I have these mocked status
			| enable_flap_detection | enable_notifications | enable_event_handlers | execute_service_checks | execute_host_checks | accept_passive_service_checks | accept_passive_host_checks |
			| 1                     | 1                    | 1                     | 1                      | 1                   | 1                             | 1                          |
		And I am logged in
		And I am on the main page

	Scenario: See that the about menu option is rendered
		When I hover the branding
		And I click "About"
		Then I should see "Version"
		And I should see "License"
		And I should see "Release"
