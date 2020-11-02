Feature: Report namespace are respected

	Background:
		Given I have an admins user group with all rights
		And I am logged in
		And I am on the main page

	Scenario: Performance information is accessable when access is not restricted
		When I select "Performance information" from the "Manage" menu
		Then I should see "Performance InformationProgram-wide"

	Scenario: Performance information is restricted without access and presents forbidden
		Given these actions are denied
			| action                                      | message                             |
			| monitor.monitoring.performance:read.extinfo | Not allowed performance information |
		When I select "Performance information" from the "Manage" menu
		Then I should see "I'm sorry, you don't have permission to view this page ... Not allowed performance information"
