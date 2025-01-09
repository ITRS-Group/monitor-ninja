Feature: Scheduled reports
	Test that reports can be scheduled, that scheduled can be deleted, that
	deleting schedules deletes reports...

	Background:
		Given I am logged in as administrator
		And I am on the main page

	@editedhappypath
	Scenario: Save availability report
		When I hover over the "Report" menu
		And I hover over the "Availability" menu
		And I click "Create Availability Report"
		And I select "Hosts" from "report_type"
		And I wait for 1 second
		And I select "monitor" from the multiselect "objects_tmp"
		Then "objects" should have option "monitor"
		When I click "Show report"
		Then I should see "Host details for monitor"
		And I should see "monitor"
		And I should see "SSH server"
		When I click "Save report"
		And I enter "saved test report" into "report_name"
		And I click "Save report" inside "#save_report_form"
		And I wait for 1 second
		Then I should see "Report was successfully saved"
	
	@editedhappypath
	Scenario: Schedule availability report
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see "New Schedule"
		And "Availability reports" should be selected from "Select report type"
		When I select "Week" from "every_text"
		And I select "saved test report" from "Select report"
		And I enter "dev@op5.com" into "Recipients"
		And I enter "This report comes from a cuke test. If the test worked, it would have been deleted, so if you're reading this, you've got work to do to fix tests. Chop, chop!" into "Description"
		And I select "Yes" from "Attach description"
		And I click "Save"
		And I wait for 1 second
		Then I shouldn't see "There are no scheduled availability reports"
		And I should see "saved_test_report_Weekly.pdf"
		And I should see "dev@op5.com"

	@editedhappypath
	Scenario: View scheduled availability report
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see "New Schedule"
		And "Availability reports" should be selected from "Select report type"
		And "Select report" should have option "saved test report"
		And I should see "saved_test_report"
		When I click "View report" on the row where "Report" is "saved test report"
		Then I should see "Host details for monitor"
		And I should see "monitor"
		And I should see "SSH server"

	@editedhappypath
	Scenario: Add second availability schedule
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see "New Schedule"
		And "Availability reports" should be selected from "Select report type"
		And "Day" should be selected from "every_text"
		When I select "saved test report" from "Select report"
		Then "Filename" should contain "saved_test_report_Daily.pdf"
		When I select "Month" from "every_text"
		Then "Filename" should contain "saved_test_report_Monthly.pdf"
		When I enter "dev@op5.com" into "Recipients"
		And I click "Save"
		And I wait for 1 second
		Then I should see "saved_test_report_Monthly.pdf"
		And I shouldn't see "&nbsp;"
		# Description comes before persistent path, so that's where we'll click
		When I doubleclick "Double click to edit" on the row where "Filename" is "saved_test_report_Monthly.pdf"
		And I enter "A description" into "newvalue" on the row where "Filename" is "saved_test_report_Monthly.pdf"
		And I click "OK" on the row where "Filename" is "saved_test_report_Monthly.pdf"
		Then the "Description" column should be "A description" on the row where "Filename" is "saved_test_report_Monthly.pdf"

	@editedhappypath
	Scenario: Delete previously created availability report
		When I hover over the "Report" menu
		And I hover over the "Availability" menu
		And I click "Create Availability Report"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report"
		When I select "saved test report"
		Then "objects" should have option "monitor"
		When I click "Delete" and confirm popup
		# Test available first, to force capybara to wait for page reload
		Then "objects_tmp" should have option "monitor"
		And "Saved reports" shouldn't have option "saved test report"
		And "objects" shouldn't have option "monitor"

	@editedhappypath
	Scenario: Ensure previously added availability schedule is gone
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see "New Schedule"
		And I shouldn't see "saved_test_report"
		And I shouldn't see "saved test report"
		And "Select report" shouldn't have option "saved test report"

	@editedhappypath
	Scenario: Save SLA report
		When I hover over the "Report" menu
		And I hover over the "SLA" menu
		And I click "Create SLA Report"
		And I select "monitor" from the multiselect "objects_tmp"
		Then "objects" should have option "monitor"
		When I enter "9" into "Jan"
		And I click "Show report"
		Then I should see "SLA breakdown for: monitor"
		And I should see "SSH server"
		When I click "Save report"
		And I enter "saved test report" into "report_name"
		And I click "Save report" inside "#save_report_form"
		And I wait for 1 second
		Then I should see "Report was successfully saved"

	@editedhappypath
	Scenario: Schedule SLA report on first day of every second month
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see "New Schedule"
		When I select "SLA report" from "Select report type"
		Then "Select report" should have option "saved test report" waiting patiently
		When I select "saved test report" from "Select report"
		And I select "Month" from "every_text"
		And I choose "sch-first-day-month"
		And I enter "2" into "every_no"
		And I enter "dev@op5.com" into "Recipients"
		And I enter "This report comes from a cuke test. If the test worked, it would have been deleted, so if you're reading this, you've got work to do to fix tests. Chop, chop!" into "Description"
		And I select "Yes" from "Attach description"
		And I click "Save"
		And I wait for 1 second
		Then I shouldn't see "There are no scheduled SLA reports"
		And I should see "saved_test_report_Monthly.pdf"
		And I should see "dev@op5.com"
		And I should see "Every 2 months on the first day of month at 12:00"

	@editedhappypath
	Scenario: View scheduled SLA report
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see "New Schedule"
		When I select "SLA report" from "Select report type"
		Then "Select report" should have option "saved test report"
		And I should see "saved_test_report"
		When I click "View report" on the row where "Report" is "saved test report"
		Then I should see "SLA breakdown for: monitor"
		And I should see "SSH server"
	
	@editedhappypath
	Scenario: Delete SLA schedule
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see "New Schedule"
		When I select "SLA report" from "Select report type"
		Then "Select report" should have option "saved test report"
		And I should see "saved_test_report"
		When I click "Delete scheduled report" and confirm popup on the row where "Report" is "saved test report"
		Then I should see "Schedule deleted"
		And I should see "There are no scheduled SLA reports"
		When I hover over the "Report" menu
		And I click "Schedule reports"
		And I select "SLA report" from "Select report type"
		Then I shouldn't see "saved test report" within "#scheduled_sla_reports"
		When I select "SLA report" from "Select report type"
		Then "Select report" should have option "saved test report"

	@editedhappypath
	Scenario: Delete previously created SLA report
		When I hover over the "Report" menu
		And I hover over the "SLA" menu
		And I click "Create SLA Report"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report"
		And I select "saved test report"
		Then "objects" should have option "monitor"
		When I click "Delete" and confirm popup
		# Test available first, to force capybara to wait for page reload
		Then "objects_tmp" should have option "monitor"
		And "Saved reports" shouldn't have option "saved test report"
		And "objects" shouldn't have option "monitor"
	
	@editedhappypath
	Scenario: Ensure previously added sla schedule is gone
		When I hover over the "Report" menu
		And I click "Schedule reports"
		And I select "SLA report" from "Select report type"
		Then I should see "New Schedule"
		And I shouldn't see "saved_test_report"
		And I shouldn't see "saved test report"
		And "Select report" shouldn't have option "saved test report"

	@editedhappypath
	Scenario: Save summary report
		When I hover over the "Report" menu
		And I hover over the "Summary" menu
		And I click "Create Summary Report"
		And I choose "Custom"
		And I select "monitor" from the multiselect "objects_tmp"
		Then "objects" should have option "monitor"
		When I click "Show report"
		Then I should see "Top alert producers"
		When I click "Save report"
		And I enter "saved test report" into "report_name"
		And I click "Save report" inside "#save_report_form"
		And I wait for 1 second
		Then I should see "Report was successfully saved"
	
	@editedhappypath
	Scenario: Schedule summary report
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see "New Schedule"
		When I select "Alert Summary Report" from "Select report type"
		Then "Select report" should have option "saved test report" waiting patiently
		When I select "saved test report" from "Select report"
		And I select "Week" from "every_text"
		And I enter "dev@op5.com" into "Recipients"
		And I enter "This report comes from a cuke test. If the test worked, it would have been deleted, so if you're reading this, you've got work to do to fix tests. Chop, chop!" into "Description"
		And I select "Yes" from "Attach description"
		And I click "Save"
		And I wait for 1 second
		Then I shouldn't see "There are no scheduled alert summary reports"
		And I should see "saved_test_report_Weekly.pdf"
		And I should see "dev@op5.com"

	@editedhappypath
	Scenario: View scheduled summary report
		When I hover over the "Report" menu
		And I click "Schedule reports"
		Then I should see "New Schedule"
		When I select "Alert Summary Report" from "Select report type"
		Then "Select report" should have option "saved test report"
		And I should see "saved_test_report"
		When I click "View report" on the row where "Report" is "saved test report"
		Then I should see "Top alert producers"

	@editedhappypath
	Scenario: Delete previously created summary report
		When I hover over the "Report" menu
		And I hover over the "Summary" menu
		When I click "Create Summary Report"
		Then I should see "Saved reports"
		And "Saved reports" should have option "saved test report"
		When I select "saved test report"
		Then "Custom" should be checked
		And "objects" should have option "monitor"
		When I click "Delete" and confirm popup
		Then "Saved reports" shouldn't have option "saved test report"
		And "objects" shouldn't have option "monitor"

	@editedhappypath
	Scenario: Ensure previously added summary schedule is gone
		Given I am on the Host details page
		And I hover over the "Report" menu
		When I click "Schedule reports"
		Then I should see "New Schedule"
		When I select "Alert Summary Report" from "Select report type"
		And I shouldn't see "saved_test_report"
		And I shouldn't see "saved test report"
		And "Select report" shouldn't have option "saved test report"