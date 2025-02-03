Feature: Global search
		
	@gian
	Scenario: Create Hosts with specific names for test data
		Given I am real logged in as "monitor" with password "monitor"
		When I hover over the "Manage" menu
		And I click "Configure"
		And I click the span with text "Hosts"
		When I create a host with hostname "A  host   with    spaces" and host address "127.0.0.1"
		When I hover over the "Manage" menu
		And I click "Configure"
		And I click the span with text "Hosts"
		When I create a host with hostname "Rozłączniki_TD_Kraków" and host address "127.0.0.1"
		When I hover over the "Manage" menu
		And I click "Configure"
		And I click the span with text "Hosts"
		When I create a host with hostname "Räksmörgås" and host address "127.0.0.1"
		When I hover over the "Manage" menu
		And I click "Configure"
		And I click the span with text "Host groups"
		And I create a hostgroup "Übergruppe"
		When I hover over the "Manage" menu
		And I click "Configure"
		And I click the span with text "Service groups"
		And I create a servicegroup "Übergruppe"
		When I save the changes in OP5
		Then I should see "Monitor has successfully loaded the new waaaa configuration"

	@gian_edited
	Scenario: Search for host with spaces
		Given I am real logged in as "monitor" with password "monitor"
		And I search for "host   with"
		Then I should see the search result:
			| A  host   with    spaces |
		When I search for "    spa"
		Then I should see the search result:
			| A  host   with    spaces |
		When I click "A  host   with    spaces"
		Then I should be on list view with filter '[services] host.name="A  host   with    spaces"'

	@gian_edited
	Scenario: Search for non ascii characters
		Given I am real logged in as "monitor" with password "monitor"
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

	@gian_edited
	Scenario: Search for service
		Given I am real logged in as "monitor" with password "monitor"
		When I search for "s:PI"
		Then I should see the search result:
			| Räksmörgås;PING |
			| Rozłączniki_TD_Kraków;PING |

	@gian_edited
	Scenario: Search for hostgroup
		Given I am real logged in as "monitor" with password "monitor"
		And I search for "hg:Über"
		Then I should see the search result:
			| Übergruppe |

	@gian_edited
	Scenario: Search for servicegroup
		Given I am real logged in as "monitor" with password "monitor"
		When I search for "sg:Über"
		Then I should see the search result:
			| Übergruppe |

	@gian_edited
	Scenario: Search with limit
		Given I am real logged in as "monitor" with password "monitor"
		When I search for "h:O limit=1"
		# Need to submit explicitly, since autocomplete doesn't work with the limit= syntax...
		And I submit the search
		Then I should see "Rozłączniki_TD_Kraków"
		And I should see "Load 1 more rows"
		But I shouldn't see "Räksmörgås"