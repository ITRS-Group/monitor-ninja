@unreliable_el7
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
		And I check for cookie bar

	@configuration
	Scenario: Add a weekly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "weekly recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "switch32" from the multiselect "objects_tmp"
		And I enter "10:00" into "start_time"
		And I enter "2018-05-10" into "start_date"
		And I enter "12:00" into "end_time"
		And I enter "2018-05-10" into "end_date"
		And I select "Weekly on Thursday" from "recurrence_select"
		And I enter "weekly recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "weekly recurring downtime"

	@configuration
	Scenario: Delete a weekly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "weekly recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule"
		Then I shouldn't see "weekly recurring downtime"

	@configuration
	Scenario: Add a monthly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "monthly recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "switch32" from the multiselect "objects_tmp"
		And I enter "10:00" into "start_time"
		And I enter "2018-05-10" into "start_date"
		And I enter "12:00" into "end_time"
		And I enter "2018-05-10" into "end_date"
		And I select "Monthly on the 2nd Thursday" from "recurrence_select"
		And I enter "monthly recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "monthly recurring downtime"

	@configuration
	Scenario: Delete a monthly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "monthly recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule"
		Then I shouldn't see "monthly recurring downtime"


	@configuration
	Scenario: Add a monthly last day recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "monthly last day recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "switch32" from the multiselect "objects_tmp"
		And I enter "10:00" into "start_time"
		And I enter "2018-05-31" into "start_date"
		And I enter "12:00" into "end_time"
		And I enter "2018-05-31" into "end_date"
		And I select "Monthly on the last day" from "recurrence_select"
		And I enter "monthly last day recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "monthly last day recurring downtime"

	@configuration
	Scenario: Delete a monthly last day recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "monthly last day recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule"
		Then I shouldn't see "monthly last day recurring downtime"


	@configuration
	Scenario: Add a custom daily recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "custom daily recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "switch32" from the multiselect "objects_tmp"
		And I enter "10:00" into "start_time"
		And I enter "2018-05-31" into "start_date"
		And I enter "12:00" into "end_time"
		And I enter "2018-05-31" into "end_date"
		And I select "Custom recurrence" from "recurrence_select"
		And I select "Day" from "recurrence_text"
		And I enter "2019-05-10" into "finite_ends_value"
		And I enter "custom daily recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "custom daily recurring downtime"

	@configuration
	Scenario: Delete a custom daily recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "custom daily recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule"
		Then I shouldn't see "custom daily recurring downtime"

	@configuration
	Scenario: Add a custom yearly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "custom yearly recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "switch32" from the multiselect "objects_tmp"
		And I enter "10:00" into "start_time"
		And I enter "2018-05-31" into "start_date"
		And I enter "12:00" into "end_time"
		And I enter "2018-05-31" into "end_date"
		And I select "Custom recurrence" from "recurrence_select"
		And I select "Year" from "recurrence_text"
		And I enter "2019-05-10" into "finite_ends_value"
		And I enter "custom yearly recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "custom yearly recurring downtime"

	@configuration
	Scenario: Delete a custom yearly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "custom yearly recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule"
		Then I shouldn't see "custom yearly recurring downtime"

	@configuration
	Scenario: Add a custom monthly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "custom monthly recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "switch32" from the multiselect "objects_tmp"
		And I enter "10:00" into "start_time"
		And I enter "2018-05-31" into "start_date"
		And I enter "12:00" into "end_time"
		And I enter "2018-05-31" into "end_date"
		And I select "Custom recurrence" from "recurrence_select"
		And I enter "5" into "recurrence_no"
		And I select "Month" from "recurrence_text"
		And I enter "2019-05-10" into "finite_ends_value"
		And I enter "custom monthly recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "custom monthly recurring downtime"

	@configuration
	Scenario: Delete a custom yearly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "custom monthly recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule"
		Then I shouldn't see "custom monthly recurring downtime"

	@configuration
	Scenario: Add a no recurrence recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "no recurrence recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "switch32" from the multiselect "objects_tmp"
		And I enter "10:00" into "start_time"
		And I enter "2018-05-31" into "start_date"
		And I enter "12:00" into "end_time"
		And I enter "2018-05-31" into "end_date"
		And I enter "no recurrence recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "no recurrence recurring downtime"

	@configuration
	Scenario: Delete a custom yearly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "no recurrence recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule"
		Then I shouldn't see "no recurrence recurring downtime"

	

