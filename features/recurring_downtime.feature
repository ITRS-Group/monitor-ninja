Feature: Recurring downtime

	Background:
	Background:
		Given I am logged in as administrator
		And I am on the main page


	@editedhappypath
	Scenario: Add a weekly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "weekly recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "monitor" from the multiselect "objects_tmp"
		And I clear and enter "10:00" into "start_time"
		And I clear and enter "2030-05-10" into "start_date"
		And I clear and enter "12:00" into "end_time"
		And I clear and enter "2030-05-10" into "end_date"
		And I find the option with string "Weekly on" from "recurrence_select"
		And I clear and enter "weekly recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "weekly recurring downtime"
		And I should see "Repeat weekly on"

	@editedhappypath
	Scenario: Delete a weekly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "weekly recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule" and confirm popup
		Then I shouldn't see "weekly recurring downtime"

	@editedhappypath
	Scenario: Add a monthly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "monthly recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "monitor" from the multiselect "objects_tmp"
		And I clear and enter "10:00" into "start_time"
		And I clear and enter "2030-05-10" into "start_date"
		And I clear and enter "12:00" into "end_time"
		And I clear and enter "2030-05-10" into "end_date"
		And I find the option with string "Monthly on" from "recurrence_select"
		And I clear and enter "monthly recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "monthly recurring downtime"
		And I should see "Repeat monthly on"

	@editedhappypath
	Scenario: Delete a monthly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "monthly recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule" and confirm popup
		Then I shouldn't see "monthly recurring downtime"


	@gian_edited
	Scenario: Add a monthly last day recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "monthly last day recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "monitor" from the multiselect "objects_tmp"
		And I clear and enter "10:00" into "start_time"
		And I clear and enter "2030-05-31" into "start_date"
		And I clear and enter "12:00" into "end_time"
		And I clear and enter "2030-05-31" into "end_date"
		And I find the option with string "Monthly on" from "recurrence_select"
		Then I should see all elements in the UI
		#And I select radio button "rec-on-last-day-month"
		#And I clear and enter "monthly last day recurring downtime" into "comment"
		#And I click "Add Schedule"
		#Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		#And I should see "monthly last day recurring downtime"
		#And I should see "Repeat monthly on the last day"

	@editedhappypath
	Scenario: Delete a monthly last day recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "monthly last day recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule" and confirm popup
		Then I shouldn't see "monthly last day recurring downtime"


	@editedhappypath
	Scenario: Add a custom daily recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "custom daily recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "monitor" from the multiselect "objects_tmp"
		And I clear and enter "10:00" into "start_time"
		And I clear and enter "2030-05-31" into "start_date"
		And I clear and enter "12:00" into "end_time"
		And I clear and enter "2030-05-31" into "end_date"
		And I select "Custom recurrence" from "recurrence_select"
		And I select "Day" from "recurrence_text"
		And I clear and enter "2031-05-10" into "finite_ends_value"
		And I clear and enter "custom daily recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "custom daily recurring downtime"
		And I should see "Repeat daily"

	@editedhappypath
	Scenario: Delete a custom daily recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "custom daily recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule" and confirm popup
		Then I shouldn't see "custom daily recurring downtime"

	@editedhappypath
	Scenario: Add a custom yearly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "custom yearly recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "monitor" from the multiselect "objects_tmp"
		And I clear and enter "10:00" into "start_time"
		And I clear and enter "2030-05-31" into "start_date"
		And I clear and enter "12:00" into "end_time"
		And I clear and enter "2030-05-31" into "end_date"
		And I select "Custom recurrence" from "recurrence_select"
		And I select "Year" from "recurrence_text"
		And I clear and enter "2031-05-10" into "finite_ends_value"
		And I clear and enter "custom yearly recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "custom yearly recurring downtime"
		And I should see "Repeat yearly on the"

	@editedhappypath
	Scenario: Delete a custom yearly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "custom yearly recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule" and confirm popup
		Then I shouldn't see "custom yearly recurring downtime"

	@editedhappypath
	Scenario: Add a custom monthly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "custom monthly recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "monitor" from the multiselect "objects_tmp"
		And I clear and enter "10:00" into "start_time"
		And I clear and enter "2030-05-31" into "start_date"
		And I clear and enter "12:00" into "end_time"
		And I clear and enter "2030-05-31" into "end_date"
		And I select "Custom recurrence" from "recurrence_select"
		And I clear and enter "5" into "recurrence_no"
		And I select "Month" from "recurrence_text"
		And I clear and enter "2031-05-10" into "finite_ends_value"
		And I clear and enter "custom monthly recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "custom monthly recurring downtime"
		And I should see "Repeat every 5 months"

	@editedhappypath
	Scenario: Delete a custom yearly recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "custom monthly recurring downtime"

		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule" and confirm popup
		Then I shouldn't see "custom monthly recurring downtime"

	@editedhappypath
	Scenario: Add a no recurrence recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "no recurrence recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "monitor" from the multiselect "objects_tmp"
		And I clear and enter "10:00" into "start_time"
		And I clear and enter "2030-05-31" into "start_date"
		And I clear and enter "12:00" into "end_time"
		And I clear and enter "2030-05-31" into "end_date"
		And I clear and enter "no repeat recurrence recurring downtime" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "no repeat recurrence recurring downtime"
		And I should see "No recurrence"

	@editedhappypath
	Scenario: Delete a no recurrence recurring downtime
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "no repeat recurrence recurring downtime"
		# thank batman there's only a single row, don't know how to select
		# it otherwise
		When I click "Delete schedule" and confirm popup
		Then I shouldn't see "no repeat recurrence recurring downtime"

	@editedhappypath
	Scenario: Add a weekly recurring downtime for each monday starts at 2025-12-03
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I shouldn't see "weekly recurring downtime"
		When I click "New"
		And I select "Hosts" from "downtime_type"
		And I select "monitor" from the multiselect "objects_tmp"
		And I clear and enter "10:00" into "start_time"
		And I clear and enter "2030-12-03" into "start_date"
		And I clear and enter "12:00" into "end_time"
		And I clear and enter "2030-12-03" into "end_date"
		And I find the option with string "Weekly on" from "recurrence_select"
		And I clear and enter "weekly recurring downtime on monday starts at 2025-12-03" into "comment"
		And I click "Add Schedule"
		Then I should be on url "/index.php/listview?q=%5Brecurring_downtimes%5D%20all"
		And I should see "weekly recurring downtime on monday starts at 2030-12-03"
		And I click "Edit schedule"
		And "start_date" should contain "2030-12-03"
		And "end_date" should contain "2030-12-03"

	@editedhappypath
	Scenario: Delete a weekly recurring downtime for each monday starts at 2030-12-03
		When I hover over the "Monitor" menu
		And I hover over the "Downtimes" menu
		And I click "Recurring Downtimes"
		Then I should see "weekly recurring downtime on monday starts at 2030-12-03"
		When I click "Delete schedule" and confirm popup
		Then I shouldn't see "weekly recurring downtime on monday starts at 2030-12-03"
