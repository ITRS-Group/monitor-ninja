@monitoring
Feature: Monitoring Host

	Background:
		Given I am logged in as administrator

	Scenario Outline: Host object details displays state properly

		Given I have these mocked hosts
			| name       | state   |
			| Babaruajan | <state> |

		Given I visit the object details page for host "Babaruajan"
		Then I should see "Soft <text> after 0 out of 0 check attempts"

		# States that are invalid for a host result in unknown
		Examples:
			| state | text        |
			| 0     | UP          |
			| 1     | DOWN        |
			| 2     | UNREACHABLE |
			| 3     | UNKNOWN     |
			| 4     | UNKNOWN     |
			| 5     | UNKNOWN     |
			| -1    | UNKNOWN     |

	Scenario Outline: Host object detail displays toggles properly

		Given I have these mocked hosts
			| name       | <property>     |
			| Babaruajan | 1              |
		And I visit the object details page for host "Babaruajan"
		Then the operating status toggle "<toggle>" should be active
		And I toggle operating status "<toggle>"
		Then the operating status toggle "<toggle>" should be inactive

		Examples:
			| property                 | toggle           |
			| active_checks_enabled    | Active checks    |
			| accept_passive_checks    | Passive checks   |
			| obsess                   | Obsessing        |
			| notifications_enabled    | Notifications    |
			| event_handler_enabled    | Event handler    |
			| flap_detection_enabled   | Flap detection   |

	Scenario Outline: Host object details displays timestamps properly

		Given I have these mocked hosts
			| name       | <property> | active_checks_enabled |
			| Babaruajan | <value>    | 1                     |

		Given I visit the object details page for host "Babaruajan"
		Then the timestamp "<label>" should show the datetime for "<value>"

		Examples:
			| property          | value      | label             |
			| last_check        | 1456789043 | LAST CHECK        |
			| next_check        | 1341789046 | NEXT CHECK        |
			| last_state_change | 1254723013 | LAST CHANGE       |
			| last_notification | 1341356135 | LAST NOTIFICATION |

	Scenario: Host object details information regarding last check local

		Given I have these mocked hosts
			| name       | latency   | execution_time | check_type | check_source    |
			| Babaruajan | 0.0334    | 0.12345        | 0          | Core Worker 123 |

		And I visit the object details page for host "Babaruajan"
		Then the object details field "LATENCY" should show "0.03sec"
		And the object details field "DURATION" should show "0.12sec"
		And the object details field "TYPE" should show "active"
		And the object details field "SOURCE" should match "local"

	Scenario: Host object details information regarding last check remote

		Given I have these mocked hosts
			| name       | latency   | execution_time | check_type | check_source    |
			| Shishish   | 0.0334    | 0.12345        | 0          | Merlin remote 123 |

		And I visit the object details page for host "Shishish"
		Then the object details field "LATENCY" should show "0.03sec"
		And the object details field "DURATION" should show "0.12sec"
		And the object details field "TYPE" should show "active"
		And the object details field "SOURCE" should show "123 (remote)"

	Scenario: Host object details information regarding last check with defaults

		Given I have these mocked hosts
			| name       |
			| Puppilla   |

		And I visit the object details page for host "Puppilla"
		Then the object details field "LATENCY" should show "0.00sec"
		And the object details field "DURATION" should show "0.00sec"
		And the object details field "TYPE" should show "active"
		And the object details field "SOURCE" should show "N/A (N/A)"


	Scenario: Host object details information regarding plugin output

		Given I have these mocked hosts
			| name       | plugin_output    |
			| Babaruajan | This is my awesome plugin output for my awesome check of awesomeness  |

		And I visit the object details page for host "Babaruajan"
		Then I should see "This is my awesome plugin output for my awesome check of awesomeness"

	Scenario: Host object details information regarding services

		Given I have these mocked hosts
			| name       | num_services_ok   | num_services_crit | num_services_warn | num_services_unknown | num_services_pending |
			| Babaruajan | 1                 | 1                 | 1                 | 1                    | 1                    |

		And I visit the object details page for host "Babaruajan"
		Then I should see these strings
			|OK 1|
			|CRITICAL 1|
			|WARNING 1|
			|UNKNOWN 1|
			|PENDING 1|

	Scenario: Host object details commands available

		Given I have these mocked hosts
			| name       |
			| Babaruajan |

		And I visit the object details page for host "Babaruajan"
		When I hover "Links" from the "Options" menu
		Then I should see these menu items:
			| Locate host on map |
			| Notifications |
			| Graphs |

		When I hover "Actions" from the "Options" menu
		Then I should see these menu items:
			| Cancel all downtimes |
			| Add a new comment |
			| Check now |
			| Schedule downtime |
			| Send custom notification |

		When I hover "Report" from the "Options" menu
		Then I should see these menu items:
			| Event log |
			| Availability |
			| Alert history |
			| Histogram |

		When I hover "Service Operations" from the "Options" menu
		Then I should see these menu items:
			| Disable checks of all services |
			| Disable notifications for all services |
			| Enable notifications for all services |
			| Enable checks of all services |
			| Schedule a check of all services |

		When I hover "Configuration" from the "Options" menu
		Then I should see these menu items:
			| Configure |
			| Delete |

	Scenario: Host object details displays scheduled downtime banner

		Given I have these mocked hosts
			| name       | scheduled_downtime_depth |
			| Babaruajan | 1                        |

		And I visit the object details page for host "Babaruajan"
		Then I should see "IN SCHEDULED DOWNTIME"

	Scenario: Host object details displays flapping banner

		Given I have these mocked hosts
			| name       | is_flapping | flap_detection_enabled | percent_state_change |
			| Babaruajan | 1           | 1                      | 0.05                 |

		And I visit the object details page for host "Babaruajan"
		Then I should see "FLAPPING 0.05% state change"

	Scenario: Host object details displays acknowledge banner

		Given I have these mocked hosts
			| name       | state |
			| Babaruajan | 1     |

		And I visit the object details page for host "Babaruajan"
		Then I should see "ACKNOWLEDGE"

	Scenario: Host object details displays acknowledged banner

		Given I have these mocked hosts
			| name       | state | acknowledged |
			| Babaruajan | 1     | 1            |

		And I visit the object details page for host "Babaruajan"
		Then I should see "ACKNOWLEDGED"


