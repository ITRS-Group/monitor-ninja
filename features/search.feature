Feature: Global search
	Background:
		Given I am logged in
		And I am on the main page

	Scenario: Search for host with spaces
		Given I have these mocked hosts
			| name     |
			| A  host   with    spaces|
			| Rozłączniki_TD_Kraków |
			| Räksmörgås |
		And I search for "host   with"
		Then I should see the search result:
			| A host with spaces |
		When I search for "    spa"
		Then I should see the search result:
			| A host with spaces |
		When I click "A  host   with    spaces"
		Then I should be on list view with filter '[services] host.name="A  host   with    spaces"'

	@MON-8046
	Scenario: Search for non ascii characters
		Given I have these mocked hosts
			| name     |
			| A  host   with    spaces|
			| Rozłączniki_TD_Kraków |
			| Räksmörgås |
		And I search for "smör"
		Then I should see the search result:
			| Räksmörgås |
		When I search for "_TD_"
		Then I should see the search result:
			| Rozłączniki_TD_Kraków |
		When I search for "Kraków"
		Then I should see the search result:
			| Rozłączniki_TD_Kraków |
		When I search for "złączn"
		Then I should see the search result:
			| Rozłączniki_TD_Kraków |
		When I click "Rozłączniki_TD_Kraków"
		Then I should be on list view with filter '[services] host.name="Rozłączniki_TD_Kraków"'

	Scenario: Search for service
		Given I have these mocked hosts
			| name     |
			| A  host   with    spaces|
			| Rozłączniki_TD_Kraków |
			| Räksmörgås |
		And I have these mocked services
			| host     | description |
			| A  host   with    spaces| PING |
			| Rozłączniki_TD_Kraków | PING |
			| Räksmörgås | PING |
		When I search for "s:PI"
		Then I should see the search result:
			| Räksmörgås;PING |
			| Rozłączniki_TD_Kraków;PING |

	Scenario: Search for hostgroup
		Given I have these mocked hostgroups
			| name     |
			| Übergruppe |
		And I search for "hg:Über"
		Then I should see the search result:
			| Übergruppe |

	Scenario: Search for servicegroup
		Given I have these mocked servicegroups
			| name     |
			| Übergruppe |
		When I search for "sg:Über"
		Then I should see the search result:
			| Übergruppe |
