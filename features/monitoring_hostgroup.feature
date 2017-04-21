@monitoring
Feature: Monitoring Hostgroup

	Background:
		Given I am logged in as administrator

	Scenario: I follow action link on list view

		Given I have these mocked hostgroups
			| name             | alias            |
			| Babaruajan-group | Babaruajan-group |

		And I'm on the list view for query "[hostgroups] all"
		And I click "Status information for Babaruajan-group"
		Then I should see "Hostgroup: Babaruajan-group(Babaruajan-group)"