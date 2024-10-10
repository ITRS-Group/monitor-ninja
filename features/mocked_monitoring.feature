Feature: Mocked
	Background:
		Given I am logged in

	Scenario: Host details page links
		Ensure that all links on the host details
		page work, and verify the tables' content
		reflects the current configuration.

		Given I have these mocked hosts
			| name          |
			| Pavani Chand  |
			| Moshe Feierman|

		And I have these mocked services
			| description     | host           |
			| Nainsi Topelson | Pavani Chand   |
			| Naoto Yamada    | Moshe Feierman |

		And I am on the Host details page
		When I click "Services total"
		Then I should see "Nainsi Topelson"
		And I should see "Naoto Yamada"


	Scenario: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place. Service total link.

		Given I have these mocked hosts
			| name    |
			| Hanh Cao|

		And I have these mocked services
			| description   | host     |
			| Nhean Saechao | Hanh Cao |

		And I am on the Host details page
		When I click "Hanh Cao"
		And I click "Go to list of all services on this host"
		Then I should see this status:
			| Host Name | Service      |
			| Hanh Cao  | Nhean Saechao|


	Scenario: Host details extinfo page displays notes
		Verify that notes on the extinfo page for a given host
		are displayed.

		Given I have these mocked hosts
			| name    |	notes |
			| Royal cheese| Your bones don't break, mine do. That's clear. Your cells react to bacteria and viruses differently than mine. You don't get sick, I do. That's also clear. But for some reason, you and I react the exact same way to water. We swallow it too fast, we choke. |

		And I visit the object details page for host "Royal cheese"
		Then I should see "Your bones"


	Scenario Outline: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place.

		Given I have these mocked hosts
			| name	 |
			| <name> |

		And I am logged in
		And I am on the Host details page
		When I click "<name>"
		And I select "<link>" from the "OPTIONS" menu
		Then I should be on url "<url>"

		Examples:
			|name           |link               |url                                                                          |
			|Bao Jen        |Report > Alert history      |/index.php/alert_history/generate?report_type=hosts&objects%5B%5D=Bao+Jen    |
			|Henderson Gomez|Report > Histogram    |/index.php/histogram/generate?report_type=hosts&objects%5B%5D=Henderson+Gomez|
			|Raizy Olsen    |Report > Availability|/index.php/avail/generate?report_type=hosts&objects%5B%5D=Raizy+Olsen        |

	Scenario: Host details extinfo page check links
		Verify that all links on the extinfo page for a given host
		point to the right place. Notifications link.

		Given I have these mocked hosts
			|name         |
			|Fermin Miller|
		And I am on the Host details page
		When I click "Fermin Miller"
		And I select "Links > Notifications" from the "OPTIONS" menu
		Then I should see "Notifications"
		And I should see "Count:"

	Scenario: Service details filter
		Verify that filter links work as expected

		Given I have these mocked hosts
			|name           |
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
			|name        |
			|Kwanita Page|

		And I have these mocked services
			|description     |host        |
			|Gabriela Obregon|Kwanita Page|

		Given I am on the Service details page
		When I click "Gabriela Obregon"
		And I click "Go to the host of this service"
		# Object details upper-case transforms names
		Then I should see "Kwanita Page"
		And I should see "Service states"


	Scenario: Service details extinfo page displays notes
		Verify that notes on the extinfo page for a given service
		are displayed.

		Given I have these mocked hosts
			| name    |
			| Quarter pounder |

		And I have these mocked services
			|description     |host        |notes |
			|Gabriela Obregon|Quarter pounder| Now that we know who you are, I know who I am. I'm not a mistake! It all makes sense! In a comic, you know how you can tell who the arch-villain's going to be? He's the exact opposite of the hero. And most times they're friends, like you and me! I should've known way back when. |

		And I visit the object details page for service "Gabriela Obregon" on host "Quarter pounder"
 		Then I should see "Now that we know"


	Scenario Outline:
		Service details extinfo page check links
		Verify that all links on the extinfo page for a given service
		point to the right place.

		Given I have these mocked hosts
			|name           |
			|Sincere Carroll|

		And I have these mocked services
			|description|host           |
			|<name>     |Sincere Carroll|
		And I am on the Service details page
		When I click "<name>"
		And I select "<link>" from the "OPTIONS" menu
		Then I should be on url "<url>"

		Examples:
			| name | link | url |
			| Sherwin Ventura | Report > Alert history| /index.php/alert_history/generate?report_type=services&objects%5B%5D=Sincere+Carroll%3BSherwin+Ventura|
			| Munny Ma | Report > Histogram | /index.php/histogram/generate?report_type=services&objects%5B%5D=Sincere+Carroll%3BMunny+Ma|

	Scenario: Service details extinfo page check links
		Verify that all links on the extinfo page for a given service
		point to the right place. Notifications link.

		Given I have these mocked hosts
			|name            |
			|Fermin Cristobal|

		And I have these mocked services
			|description|host            |
			|Bhin Phan  |Fermin Cristobal|

		Given I am on the Service details page
		When I click "Bhin Phan"
		And I select "Links > Notifications" from the "OPTIONS" menu
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
		And I select "Report > Availability" from the "OPTIONS" menu
		Then I should be on url "/index.php/avail/generate?report_type=services&objects%5B%5D=Champey+Hong%3BManami+Kaneshiro"
		And I should see "Service details for Manami Kaneshiro on host Champey Hong"
		And I should see "Reporting period: Last 7 days"

	@MON-9647
	Scenario: Contact tables should have pager and email columns, at least
		Given I have these mocked contacts
			| name          | alias  | email                     | pager |
			| Handsome Jack | Jackie | buttstallion@hyperion.com | 911   |

		When I am on the contacts list
		Then I should see "Name"
		And I should see "Handsome Jack"

		And I should see "Email"
		And I should see "buttstallion@hyperion.com"

		And I should see "Alias"
		And I should see "Jackie"

		And I should see "Pager"
		And I should see "911"

	@MON-11243
	Scenario: Host with contacts renders contacts widget in extinfo page
		Given I have these mocked contactgroups
			| name            | members         |
			| support-group   | support-contact |
		And I have these mocked contacts
			| name            |
			| support-contact |
		And I have these mocked hosts
			| name       | contactgroups | contacts        |
			| test-host  | support-group | support-contact |

		And I am on the Host details page
		When I click "test-host"
		Then I should see "Contacts"
		And I should see "support-contact"
		And I shouldn't see "Loading..."

	@gian
	Scenario: Host Actions > Check Now
	Given I have these mocked hosts
		|name         |
		|Fermin Miller|
	And I am on the Host details page
	When I click "Fermin Miller"
	And I select "Actions > Check now" from the "OPTIONS" menu
	Then I should see "Your commands were successfully submitted to ITRS OP5 Monitor."