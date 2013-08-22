@menu
Feature: Mainmenu

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