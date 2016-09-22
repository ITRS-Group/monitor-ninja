@monitoring
Feature: Monitoring process info

	Background:
		Given I have these mocked status
			| process_performance_data | enable_flap_detection | enable_notifications | enable_event_handlers | execute_service_checks | execute_host_checks | accept_passive_service_checks | accept_passive_host_checks | obsess_over_hosts | obsess_over_services  |
			| 1                        | 1                     | 1                    | 1                     | 1                      | 1                   | 1                             | 1                          | 1                     | 1                    |

		Given I am logged in as administrator

	Scenario: Process commands are available

		Given I visit the process information page
		When I hover "Process" from the "Options" menu
		Then I should see these menu items:
			| Restart the Naemon process |
			| Shut down the Naemon process |

		When I hover "Operations" from the "Options" menu
		Then I should see these menu items:
			| Disable notifications |
			| Disable event handlers |
			| Disable flap detection |
			| Disable performance data processing |

		When I hover "Service operations" from the "Options" menu
		Then I should see these menu items:
			| Stop executing service checks |
			| Stop accepting passive service checks |
			| Stop obsessing over services |

		When I hover "Host operations" from the "Options" menu
		Then I should see these menu items:
			| Stop executing host checks |
			| Stop accepting passive host checks |
			| Stop obsessing over hosts |
