@configuration
Feature: Recurring downtime

	Background:
		Given I have these hosts:
			| host_name |
			| switch32  |
		And I have these services:
			| service_description | host_name | check_command |
			| PING                | switch32  | check_ping    |

		And I have activated the configuration
		And I am logged in as administrator
		And I am on the main page

	Scenario: Add a recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "Kroppkakor is a thing"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "switch32" from the multiselect "objects_tmp"
		And I enter "Kroppkakor is a thing" into "comment"
		And I click css "#select-all-days"
		And I click css "#select-all-months"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "Kroppkakor is a thing"

	Scenario: Edit a recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "Kroppkakor is a thing"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Edit schedule"
		And I enter "Whipped cream" into "comment"
		And I click "Update schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I shouldn't see "Kroppkakor is a thing"
		And I should see "Whipped cream"

	Scenario: Delete a recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "Whipped cream"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule"
		Then I shouldn't see "Whipped cream"
