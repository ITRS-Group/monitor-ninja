@menu
Feature: Menu

	Background:
		Given I am logged in
		And I am on the main page
		And I check for cookie bar

	Scenario: See that the about menu displays properly on hover
		When I hover the branding
		Then I should see menu items:
			| About |

	Scenario: See that the monitor menu displays properly on hover
		When I hover over the "Monitor" menu
		Then I should see menu items:
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
		And I wait for 1 second
		Then I should see css "#dojo-icon-container .x16-enable"
		When I enter "google.com" into "URI"
		And I enter "Make my day" into "Title"
		And I click css "#dojo-icon-container .x16-enable"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I should see css "a[href='google.com'][title='Make my day']" within "#header"
		And I shouldn't see "Add new quicklink"

	Scenario: Remove quicklink
		Then I wait for 2 seconds
		When I click "Manage quickbar"
		# The dialog will fade in, and if it's not done, it won't fade out properly
		And I wait for 1 second
		Then I should see css "#dojo-icon-container .x16-enable"
		When I check "Make my day"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I shouldn't see "Add new quicklink" waiting patiently
		And I shouldn't see css "a[href='google.com'][title='Make my day']" within "#header"

	@unreliable_el7 @unreliable
	Scenario: Verify that the Manual link goes to the KB
		When I hover the branding
		Then I should see css "a[href='https://docs.itrsgroup.com/docs/op5-monitor/']"

	Scenario: Validate quicklink absolute URL
		When I click "Manage quickbar"
		And I wait for 1 second
		Then I should see css "#dojo-icon-container .x16-notification"
		When I enter "https://monitor01/index.php/configuration/configure" into "URI"
		And I enter "absolute URL" into "Title"
		And I click css "#dojo-icon-container .x16-notification"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I should see css "a[href='https://monitor01/index.php/configuration/configure'][title='absolute URL']" within "#header"

	Scenario: Validate quicklink internal URL
		When I click "Manage quickbar"
		And I wait for 1 second
		Then I should see css "#dojo-icon-container .x16-monitoring"
		When I enter "/monitor/index.php/configuration/configure" into "URI"
		And I enter "internal URL" into "Title"
		And I click css "#dojo-icon-container .x16-monitoring"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I should see css "a[href='/monitor/index.php/configuration/configure'][title='internal URL']" within "#header"

	Scenario: Validate quicklink
		When I click "Manage quickbar"
		And I wait for 1 second
		Then I should see css "#dojo-icon-container .x16-cli"
		When I enter "javascript:alert(1);" into "URI"
		And I enter "XSS test" into "Title"
		And I click css "#dojo-icon-container .x16-cli"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I should see css "a[title='XSS test']" within "#header"
		And I shouldn't see "Add new quicklink"
		When I click css ".x16-cli" within "#header"
		Then I shouldn't see "Not Found"
