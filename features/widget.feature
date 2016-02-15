@widgets
Feature: Widgets

	@unreliable
	Scenario: External widget listview
		Given I have these mocked ninja_widgets
			|id|username  |page     |name         |friendly_name     |setting|instance_id|
			|0 |mockeduser|tac/index|listview     |An external widget|a:0:{} |1234567    |
		And I have these mocked hosts
			| name			|
			| Kira Powers   |
		When I expose the widget "listview"
		And I am on address "/index.php/external_widget/listview"
		Then I should see "Kira Powers"
		When I am on the main page
		Then I should see "Password"

	Scenario: Requesting non-existing widget
		When I expose the widget "tac_problems"
		And I am on address "/index.php/external_widget/pippilottarullgardina"
		Then I should see "Widget not found"
		When I am on the main page
		Then I should see "Password"

	# This test is likely to fail when run against a ninja
	# not in /opt/monitor since nagvis is hardcoded to look
	# for external_widget settings in that particular directory
	@unreliable
	Scenario: External widget nagvis
		Given I expose the widget "nagvis" with settings
			| height | map |
			| 600    | automap |
		When I am on address "/index.php/external_widget/nagvis"
		# Default root of automap is monitor
		Then I should see "monitor" within frame "nagvis"
		When I am on the main page
		Then I should see "Password"

	Scenario: Listview widgets are rendered on TAC
		Given I have these mocked ninja_widgets
			|id|username  |page     |name         |friendly_name     |setting|instance_id|
			|0 |mockeduser|tac/index|listview     |A friendly widget!|a:0:{} |1234567    |
		And I am logged in
		When I am on the main page
		Then I should see "A friendly widget"

	Scenario: Non-installed widgets are reported as such
		Given I have these mocked ninja_widgets
			|id|username  |page     |name                     |friendly_name       |setting|instance_id|
			|0 |mockeduser|tac/index|not-an-actual-widget-type|An imaginary widget!|a:0:{} |1234567    |
		And I am logged in
		When I am on the main page
		Then I should see "An imaginary widget"
		And I should see "Widget type 'not-an-actual-widget-type' does not seem to be installed"

	Scenario: Widgets that fails to render are rendered with error message
		Given I have a widget that fails to render with error message "Widget failed to render"
		And I am logged in
		And I have these mocked ninja_widgets
			|id|username  |page     |name        |friendly_name    |setting|instance_id|
			|0 |mockeduser|tac/index|unrenderable|Unrendered Widget|a:0:{} |1234567    |

		And I am on the main page
		Then I should see "Widget failed to render"
		And I should see "Unrendered Widget"
		But I shouldn't see "Stack Trace"

	Scenario: Widgets that fail to instantiate are rendered with error message
		Given I have a broken widget with error message "This is a dead widget"
		And I am logged in
		And I have these mocked ninja_widgets
			|id|username  |page     |name  |friendly_name|setting|instance_id|
			|0 |mockeduser|tac/index|broken|Broken Widget|a:0:{} |1234567    |

		And I am on the main page
		Then I should see "This is a dead widget"
		And I should see "Broken Widget"
		But I shouldn't see "Stack Trace"
