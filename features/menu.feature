Feature: Menu

	Background:
		Given I am logged in
		And I am on the main page

	@gian_edited
	Scenario: See that the about menu displays properly on hover
		When I hover the branding
		Then I should see the element with data-menu-id "about"
		And I should see the element with data-menu-id "op5_manual"
		And I should see the element with data-menu-id "op5_support"
		And I should see the element with data-menu-id "http_api"

	@gian
	Scenario: See that the dashboard menu displays properly on hover
		When I hover over the "Dashboards" menu
		Then I should see the element with data-menu-id "1"
		And I should see the element with data-menu-id "all_dashboards"
		And I should see the element with data-menu-id "menuitem_dashboard_option"

	@gian_edited
	Scenario: See that the monitor menu displays properly on hover
		When I hover over the "Monitor" menu
		Then I should see the element with data-menu-id "trapper"
		And I should see the element with data-menu-id "business_services"
		And I should see the element with data-menu-id "network_outages"
		And I should see the element with data-menu-id "hosts"
		And I should see the element with data-menu-id "services"
		And I should see the element with data-menu-id "hostgroups"
		And I should see the element with data-menu-id "servicegroups"
		And I should see the element with data-menu-id "downtimes"
		And I should see the element with data-menu-id "nagvis"
		And I should see the element with data-menu-id "geomap"

	@gian
	Scenario: See that the graphs menu displays properly on hover
		When I hover over the "Report" menu
		Then I should see the element with data-menu-id "availability"
		And I should see the element with data-menu-id "sla"
		And I should see the element with data-menu-id "histogram"
		And I should see the element with data-menu-id "summary"
		And I should see the element with data-menu-id "graphs"
		And I should see the element with data-menu-id "saved_reports"
		And I should see the element with data-menu-id "alert_history"
		And I should see the element with data-menu-id "schedule_reports"
		And I should see the element with data-menu-id "event_log"
		And I should see the element with data-menu-id "notifications"

	@gian_edited
	Scenario: See that the config menu displays properly on hover
		When I hover over the "Manage" menu
		Then I should see the element with data-menu-id "configure"
		And I should see the element with data-menu-id "view_active_config"
		And I should see the element with data-menu-id "backup_restore"
		And I should see the element with data-menu-id "manage_filters"
		And I should see the element with data-menu-id "scheduling_queue"
		And I should see the element with data-menu-id "performance_information"
		And I should see the element with data-menu-id "process_information"
		And I should see the element with data-menu-id "host_wizard"
		And I should see the element with data-menu-id "autodiscovery"

	Scenario: Add quicklink
		When I click "Manage quickbar"
		# The dialog will fade in, and if it's not done, it won't fade out properly
		And I wait for 2 seconds
		Then I should see css "#dojo-icon-container .x16-enable"
		When I enter "google.com" into "URI"
		And I enter "Make my day" into "Title"
		And I click css "#dojo-icon-container .x16-enable"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I should see css "a[href='google.com'][title='Make my day']" within "#header"
		And I shouldn't see "Add new quicklink"

	Scenario: Remove quicklink
		Then I wait for 2 seconds
		When I click "Manage quickbar"
		# The dialog will fade in, and if it's not done, it won't fade out properly
		And I wait for 1 second
		Then I should see css "#dojo-icon-container .x16-enable"
		When I check "Make my day"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I shouldn't see "Add new quicklink" waiting patiently
		And I shouldn't see css "a[href='google.com'][title='Make my day']" within "#header"

	@unreliable_el7 @unreliable
	Scenario: Verify that the Manual link goes to the KB
		When I hover the branding
		Then I should see css "a[href='https://docs.itrsgroup.com/docs/op5-monitor/current/']"

	Scenario: Validate quicklink absolute URL
		When I click "Manage quickbar"
		And I wait for 1 second
		Then I should see css "#dojo-icon-container .x16-notification"
		When I enter "https://monitor01/index.php/configuration/configure" into "URI"
		And I enter "absolute URL" into "Title"
		And I click css "#dojo-icon-container .x16-notification"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I should see css "a[href='https://monitor01/index.php/configuration/configure'][title='absolute URL']" within "#header"

	Scenario: Validate quicklink internal URL
		When I click "Manage quickbar"
		And I wait for 1 second
		Then I should see css "#dojo-icon-container .x16-monitoring"
		When I enter "/monitor/index.php/configuration/configure" into "URI"
		And I enter "internal URL" into "Title"
		And I click css "#dojo-icon-container .x16-monitoring"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I should see css "a[href='/monitor/index.php/configuration/configure'][title='internal URL']" within "#header"

	Scenario: Validate quicklink
		When I click "Manage quickbar"
		And I wait for 1 second
		Then I should see css "#dojo-icon-container .x16-cli"
		When I enter "javascript:alert(1);" into "URI"
		And I enter "XSS test" into "Title"
		And I click css "#dojo-icon-container .x16-cli"
		And I click "Save" waiting patiently
		And I wait for ajax
		Then I should see css "a[title='XSS test']" within "#header"
		And I shouldn't see "Add new quicklink"
		When I click css ".x16-cli" within "#header"
		Then I shouldn't see "Not Found"
