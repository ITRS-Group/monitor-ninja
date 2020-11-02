@custom_commands
Feature: Custom commands

	Scenario: Custom commands are available
		Given I have these mocked contactgroups
			| name            | members       |
			| mycontactgroup  | administrator |
		Given I have these mocked contacts
			| name           |
			| administrator  |
		Given I have these mocked hosts
			| name           | custom_variables |
			| Gösta Hyginus  | OP5X__ACTION__SENAPSKORV_KEK=echo "$HOSTNAME$",OP5X__ACCESS__SENAPSKORV_KEK=mycontactgroup |
		And I am logged in as administrator
		And I visit the object details page for host "Gösta Hyginus"
		When I hover over the "Options" menu
		And I hover over the "Custom commands" menu

		Then I should see these menu items:
			| Senapskorv kek |

		When I click "Senapskorv kek"
		# Then I should see a dialog with title "Command result - Senapskorv kek"

