Feature: Mocked
  Background:
    Given I am logged in

	Scenario: Host details page links
		Ensure that all links on the host details
		page work, and verify the tables' content
		reflects the current configuration.
		Given I have these mocked hosts
			| name			|
			| Pavani Chand |
			| Moshe Feierman |

		And I have these mocked services
			| description     | host		  |
			| Nainsi Topelson | Pavani Chand |
			| Naoto Yamada    | Moshe Feierman |

		And I am on the Host details page
		When I click "Services total"
		Then I should see "Nainsi Topelson"
		And I should see "Naoto Yamada"


	Scenario: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place. Status detail link.

		Given I have these mocked hosts
			| name			|
			| Hanh Cao |

		And I have these mocked services
			| description   | host		  |
			| Nhean Saechao | Hanh Cao |

		And I am on the Host details page
		When I click "Hanh Cao"
		And I click "Status detail"
		Then I should see this status:
			| Host Name	    | Service |
			| Hanh Cao      | Nhean Saechao|

	Scenario Outline: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place.

		Given I have these mocked hosts
			| name			|
			| <name>		|

		And I am logged in
		And I am on the Host details page
		When I click "<name>"
		And I click "<link>"
		Then I should be on url "<url>"

		Examples:
			|name			|link			|url                                                                   |
			|Bao Jen		|Alert history	|/index.php/alert_history/generate?report_type=hosts&objects%5B%5D=Bao+Jen|
      |Henderson Gomez|Alert histogram|/index.php/histogram/generate?report_type=hosts&objects%5B%5D=Henderson+Gomez|
			|Raizy Olsen    |Availability report|/index.php/avail/generate?report_type=hosts&objects%5B%5D=Raizy+Olsen|

	Scenario: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place. Notifications link.

		Given I have these mocked hosts
			|name			|
			|Fermin Miller|
		And I am on the Host details page
		When I click "Fermin Miller"
		And I click "Notifications"
		Then I should see "Notifications"
		And I should see "Count:"

	Scenario: Service details filter
		Verify that filter links work as expected

		Given I have these mocked hosts
			|name			|
			|Ofra Heidenheim|

		And I have these mocked services
			|description    |host           |
			|Channary Kim   |Ofra Heidenheim|
			|Sariah Grayson |Ofra Heidenheim|
			|Manami Kawakami|Ofra Heidenheim|
			|Channary Sum	|Ofra Heidenheim|

		Given I am on the Service details page
		Then I should see the mocked services
		And I click link "Services total"
		Then I should see the mocked services

	Scenario: Service details extinfo page check links
		Verify that all links on the extinfo page for a given service
		point to the right place. Status detail link.

		Given I have these mocked hosts
			|name			|
			|Kwanita Page	|

		And I have these mocked services
			|description	 |host			|
			|Gabriela Obregon|Kwanita Page  |

		Given I am on the Service details page
		When I click "Gabriela Obregon"
		And I click "Status detail"
		Then I should see this status:
			| Host Name		| Service		  |
			| Kwanita Page| Gabriela Obregon|

	Scenario Outline:
		Service details extinfo page check links
		Verify that all links on the extinfo page for a given service
		point to the right place.

		Given I have these mocked hosts
			|name			|
			|Sincere Carroll|

		And I have these mocked services
			|description|host			|
			|<name>		|Sincere Carroll|
		And I am on the Service details page
		When I click "<name>"
		And I click "<link>"
		Then I should be on url "<url>"

		Examples:
			|name|link|url|
			|Sherwin Ventura|Alert history|/index.php/alert_history/generate?report_type=services&objects%5B%5D=Sincere+Carroll%3BSherwin+Ventura|
			|Munny Ma|Alert histogram|/index.php/histogram/generate?report_type=services&objects%5B%5D=Sincere+Carroll%3BMunny+Ma|

	Scenario: Service details extinfo page check links
		Verify that all links on the extinfo page for a given service
		point to the right place. Notifications link.

		Given I have these mocked hosts
			|name			|
			|Fermin Cristobal|

		And I have these mocked services
			|description|host			 |
			|Bhin Phan	|Fermin Cristobal|

		Given I am on the Service details page
		When I click "Bhin Phan"
		And I click "Notifications"
		Then I should see "Notifications"
		And I should see "Count:"


  Scenario: Service details extinfo page check links
		Verify that all links on the extinfo page for a given service
		point to the right place. Availability report link.

    Given I have these mocked hosts
      |name        |
      |Champey Hong|
    And I have these mocked services
      |description      |host|
      |Manami Kaneshiro |Champey Hong|
    And I am on the Service details page
		When I click "Manami Kaneshiro"
		And I click "Availability report"
		Then I should be on url "/index.php/avail/generate?report_type=services&objects%5B%5D=Champey+Hong%3BManami+Kaneshiro"
		And I should see "Service details for Manami Kaneshiro on host Champey Hong"
		And I should see "Reporting period: Last 7 days"
