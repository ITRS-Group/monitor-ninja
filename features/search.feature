@monitoring @integration
Feature: Global search
        Background:
                Given I have these hosts:
                        | host_name     |
                        | A  host   with    spaces|
                        | Rozłączniki_TD_Kraków |
                        | Räksmörgås |
                And I have activated the configuration
                And I am logged in as administrator

	Scenario: Search for host with spaces
		When I search for "host   with"
		Then I should see the search result:
			| A  host   with    spaces |

	Scenario: Search for host with ÅÄÖ
		When I search for "Räk"
		Then I should see the search result:
			| Räksmörgås |
		When I search for "smör"
		Then I should see the search result:
			| Räksmörgås |

	@MON-8046
	Scenario: Search for non ascii characters
		When I search for "_TD_"
		Then I should see the search result:
			| Rozłączniki_TD_Kraków |
		When I search for "Kraków"
		Then I should see the search result:
			| Rozłączniki_TD_Kraków |
		When I search for "Rozłączniki"
		Then I should see the search result:
			| Rozłączniki_TD_Kraków |

