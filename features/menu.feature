@menu
Feature: Menu

	Background:
		Given I am on the main page

	@asmonitor
	Scenario: See that the about menu displays properly on hover
		When I hover over the "About" button
		Then I should see these menu items:
			| op5 Portal |
			| op5 Monitor Manual |
			| op5 Support Portal |

	@asmonitor
	Scenario: See that the monitor menu displays properly on hover
		When I hover over the "Monitoring" button
		Then I should see these menu items:
			| Tactical Overview |
			| Host Detail |
			| Service Detail |
			| Hostgroup Summary |
			| Servicegroup Summary |
			| Network Outages |
			| Comments |
			| Scheduled Downtime |
			| Recurring Downtime |
			| Process Info |
			| Performance Info |
			| Scheduling Queue |
			| NagVis |

	@asmonitor
	Scenario: See that the graphs menu displays properly on hover
		When I hover over the "Reporting" button
		Then I should see these menu items:
			| Graphs |
			| Alert History |
			| Alert Summary |
			| Notifications |

	@asmonitor
	Scenario: See that the config menu displays properly on hover
		When I hover over the "Configuration" button
		Then I should see these menu items:
			| View Config |
			| My Account |
			| Backup/Restore |
			| Configure |

	@asmonitor
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

	@asmonitor
	Scenario: Remove quicklink
		When I click "Manage quickbar"
		# The dialog will fade in, and if it's not done, it won't fade out properly
		And wait for "1" seconds
		Then I should see css "#dojo-icon-container .x16-enable"
		When I check "Make my day"
		And I click "Save"
		Then I shouldn't see "Add new quicklink" waiting patiently
		And I shouldn't see css "a[href='google.com'][title='Make my day']" within "#header"
