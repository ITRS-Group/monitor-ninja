@monitoring
Feature: Monitoring Service

	Background:
		Given I am logged in as administrator

	Scenario Outline: Service object details displays state properly

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | state   | host     |
			| Babaruajan  | <state> | Rosalind |

		Given I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then I should see "Soft <text> after 0 out of 0 check attempts"

		# States that are invalid for a host result in unknown
		Examples:
			| state | text        |
			| 0     | OK          |
			| 1     | WARNING     |
			| 2     | CRITICAL    |
			| 3     | UNKNOWN     |
			| 4     | UNKNOWN     |
			| 5     | UNKNOWN     |
			| -1    | UNKNOWN     |

	Scenario Outline: Service object detail displays toggles properly

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     | <property> |
			| Babaruajan  | Rosalind | 1          |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
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

	Scenario Outline: Service object details displays timestamps properly

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     | <property> | active_checks_enabled |
			| Babaruajan  | Rosalind | <value>    | 1                     |

		Given I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then the timestamp "<label>" should show the datetime for "<value>"

		Examples:
			| property          | value      | label             |
			| last_check        | 1456789043 | LAST CHECK        |
			| next_check        | 1356789046 | NEXT CHECK        |
			| last_state_change | 1254723013 | LAST CHANGE       |
			| last_notification | 1341356135 | LAST NOTIFICATION |

	Scenario: Service object details information regarding last check local

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     | latency   | execution_time | check_type | check_source    |
			| Babaruajan  | Rosalind | 0.0335    | 0.12345        | 0          | Core Worker 123 |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then the object details field "LATENCY" should show "0.03sec"
		And the object details field "DURATION" should show "0.12sec"
		And the object details field "TYPE" should show "active"
		And the object details field "SOURCE" should match "local"

	Scenario: Service object details information regarding last check remote

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     | latency   | execution_time | check_type | check_source    |
			| Babaruajan  | Rosalind | 0.035    | 0.12345        | 0          | Merlin remote 123 |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then the object details field "LATENCY" should show "0.04sec"
		And the object details field "DURATION" should show "0.12sec"
		And the object details field "TYPE" should show "active"
		And the object details field "SOURCE" should show "123 (remote)"

	Scenario: Service object details information regarding last check with defaults

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     |
			| Babaruajan  | Rosalind |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then the object details field "LATENCY" should show "0.00sec"
		And the object details field "DURATION" should show "0.00sec"
		And the object details field "TYPE" should show "active"
		And the object details field "SOURCE" should show "N/A (N/A)"


	Scenario: Service object details information regarding plugin output

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     | plugin_output |
			| Babaruajan  | Rosalind | This is my awesome plugin output for my awesome check of awesomeness  |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then I should see "This is my awesome plugin output for my awesome check of awesomeness"

	Scenario: Service object details commands available

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     |
			| Babaruajan  | Rosalind |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		When I hover "Links" from the "Options" menu
		Then I should see these menu items:
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

		When I hover "Configuration" from the "Options" menu
		Then I should see these menu items:
			| Configure |
			| Delete |

	Scenario: Service object details displays scheduled downtime banner

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     | scheduled_downtime_depth |
			| Babaruajan  | Rosalind | 1                        |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then I should see "IN SCHEDULED DOWNTIME"

	Scenario: Service object details displays scheduled downtime banner

		Given I have these mocked hosts
			| name     | scheduled_downtime_depth |
			| Rosalind | 1                        |
		Given I have these mocked services
			| description | host     |
			| Babaruajan  | Rosalind |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then I should see "SCHEDULED DOWNTIME"

	Scenario: Service object details displays flapping banner

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     | is_flapping | flap_detection_enabled | percent_state_change |
			| Babaruajan  | Rosalind | 1           | 1                      | 0.05                 |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then I should see "FLAPPING 0.05% state change"

	Scenario: Service object details displays acknowledge banner

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     | state |
			| Babaruajan  | Rosalind | 1     |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then I should see "ACKNOWLEDGE"

	Scenario: Service object details displays acknowledged banner

		Given I have these mocked hosts
			| name     |
			| Rosalind |
		Given I have these mocked services
			| description | host     | acknowledged | state |
			| Babaruajan  | Rosalind | 1            | 1     |

		And I visit the object details page for service "Babaruajan" on host "Rosalind"
		Then I should see "ACKNOWLEDGED"


