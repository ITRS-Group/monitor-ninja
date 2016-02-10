Feature: Report namespace are respected

	Background:
		Given I am logged in
		And I am on the main page

	Scenario Outline: HTML reports are restricted
		Given these actions are denied
			|action            | message   |
			|<denied namespace>       | <message> |
		When I hover over the "Report" menu
		And I hover over the "<report type>" menu
		And I click "Create <report type> Report"
		Then I should see "<message>"

		Examples:
			|denied namespace                  |message                         |report type |
			|:read.report.avail.html    |Can't let you do that, Starfox! |Availability|
			|:read.report.sla.html      |Andross' enemy is MY enemy.     |SLA         |
			|:read.report.histogram.html|Hey, Einstein! I'm on your side!|Histogram   |
			|:read.report.summary.html  |Do a barrel roll!               |Summary     |

	Scenario: PDF Availability reports are restricted
		Given these actions are denied
			|action                | message                       |
			|:read.report.avail.pdf|Hold still and lemme shoot you.|
		And I have these mocked hosts
			|name                 |
			|Falco Lombardi       |

		When I hover over the "Report" menu
		And I hover over the "Availability" menu
		And I click "Create Availability Report"
		And I select "Hosts" from "Report type"
		And I select "Falco Lombardi" from the multiselect "objects_tmp"
		And I click "Show report"
		When I click "As PDF"
		Then I should see "Hold still and lemme shoot you."

	Scenario: PDF SLA reports are restricted
		Given these actions are denied
			|action                  |message                               |
			|:read.report.sla.pdf    |We'll just see about that, Star Wolf. |
		And I have these mocked hosts
			|name       |
			|Fox McCloud|
		When I hover over the "Report" menu
		And I hover over the "SLA" menu
		And I click "Create SLA Report"
		And I select "Hosts" from "Report type"
		And I select "Fox McCloud" from the multiselect "objects_tmp"
		And I enter "99" into "Jan"
		And I click "Show report"
		When I click "As PDF"
		Then I should see "We'll just see about that, Star Wolf."

	Scenario: PDF summary reports are restricted
		Given these actions are denied
			|action                  | message                     |
			|:read.report.summary.pdf| Escaping? I don't think so! |
		When I hover over the "Report" menu
		And I hover over the "Summary" menu
		And I click "Create Summary Report"
		And I click "Show report"
		When I click "As PDF"
		Then I should see "Escaping? I don't think so!"

	Scenario: PDF histogram reports are not implemented
		Given I have these mocked hosts
			|name       |
			|Slippy Toad|
		When I hover over the "Report" menu
		And I hover over the "Histogram" menu
		And I click "Create Histogram Report"
		And I click "Show report"
		# If this step fails, you've implemented PDF support for
		# histogram reports, and should make sure to add a test
		# for that namespace akin to the scenarios above this one
		Then I shouldn't see "As PDF"

