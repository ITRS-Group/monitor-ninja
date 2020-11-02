@monitoring
Feature: Monitoring Servicegroup

	Background:
		Given I am logged in as administrator

	Scenario: I follow action link on list view

		Given I have these mocked servicegroups
			| name             | alias            |
			| Babaruajan-group | Babaruajan-group |

		And I am on a servicegroups list view
		And I click "Status information for Babaruajan-group"
		Then I should see "Servicegroup: Babaruajan-group(Babaruajan-group)"