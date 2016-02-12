@menu
Feature: Menu

	Background:
		Given I have these mocked status
			| enable_flap_detection | enable_notifications | enable_event_handlers | execute_service_checks | execute_host_checks | accept_passive_service_checks | accept_passive_host_checks |
			| 0                     | 0                    | 0                     | 0                      | 0                   | 0                             | 0                          |
		Given I am logged in
		And I am on the main page

	Scenario: See that the about menu displays properly on hover
		When I hover the branding
		Then I should see menu items:
			| op5 Portal |
			| op5 Manual |
			| op5 Support |

	Scenario: See that the monitor menu displays properly on hover
		When I hover over the "Monitor" menu
		Then I should see menu items:
			| Tactical Overview |
			| Network Outages |
			| Hosts |
			| Services |
			| Hostgroups |
			| Servicegroups |
			| Downtimes |
			| NagVis |

	Scenario: See that the graphs menu displays properly on hover
		When I hover over the "Report" menu
		Then I should see menu items:
			| Graphs |
			| Availability |
			| SLA |
			| Histogram |
			| Summary |
			| Alert history |
			| Notifications |

	Scenario: See that the config menu displays properly on hover
		When I hover over the "Manage" menu
		Then I should see menu items:
			| View active config |
			| Backup/Restore |
			| Manage filters |
			| Scheduling queue |
			| Process information |
			| Performance information |

	Scenario: Add quicklink
		When I click "Manage quickbar"
		# The dialog will fade in, and if it's not done, it won't fade out properly
		And wait for "1" seconds
		Then I should see css "#dojo-icon-container .x16-enable"
		When I enter "google.com" into "URI"
		And I enter "Make my day" into "Title"
		And I click css "#dojo-icon-container .x16-enable"
		And I click "Save"
		Then I should see css "a[href='google.com'][title='Make my day']" within "#header"
		And I shouldn't see "Add new quicklink"

	Scenario: Remove quicklink
		When I click "Manage quickbar"
		# The dialog will fade in, and if it's not done, it won't fade out properly
		And wait for "1" seconds
		Then I should see css "#dojo-icon-container .x16-enable"
		When I check "Make my day"
		And I click "Save"
		Then I shouldn't see "Add new quicklink" waiting patiently
		And I shouldn't see css "a[href='google.com'][title='Make my day']" within "#header"

	Scenario: Verify that the Manual link goes to the KB
		When I hover the branding
		Then I should see css "a[href='https://kb.op5.com/display/DOC']"
