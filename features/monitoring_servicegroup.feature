@monitoring
Feature: Monitoring Servicegroup

	Background:
		Given I am logged in as administrator

	Scenario: I follow action link on list view

		Given I have these mocked servicegroups
			| name             | alias            |
			| Babaruajan-group | Babaruajan-group |

		And I'm on the list view for query "[servicegroups] all"
		And I click "Status information for Babaruajan-group"
		Then I should see "Servicegroup: Babaruajan-group(Babaruajan-group)"