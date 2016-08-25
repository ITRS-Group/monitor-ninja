Feature: Settings

	Background:
		Given I am logged in as administrator
		And I am on the main page

	Scenario: Command defaults text input are respected
		Given I have these mocked settings
			|username     |page|type              |setting        |
			|administrator|*   |nagdefault.comment|This is for fun|
		And I have these mocked hosts
			|name      |
			|Barbarella|
		When I go to the listview for [hosts] all
		And I click "Barbarella"
		And I select "Actions > Add a new comment" from the "Options" menu
		Then "comment" should contain "This is for fun"

	# using two scenarios so that we can trust the mocked fixture, even if
	# the system default ever changes
	Scenario: Command defaults checkboxes are respected when checked
		Given I have these mocked settings
			|username     |page|type                 |setting|
			|administrator|*   |nagdefault.persistent|1      |
		And I have these mocked hosts
			|name     |state|
			|Batmanina|1    |
		When I go to the listview for [hosts] all
		And I click "Batmanina"
		And I click "acknowledge"
		Then "persistent" should be checked

	# using two scenarios so that we can trust the mocked fixture, even if
	# the system default ever changes
	Scenario: Command defaults checkboxes are respected when not checked
		Given I have these mocked settings
			|username     |page|type                 |setting|
			|administrator|*   |nagdefault.persistent|0      |
		And I have these mocked hosts
			|name      |state|
			|Whackaboom|1    |
		When I go to the listview for [hosts] all
		And I click "Whackaboom"
		And I click "acknowledge"
		Then "persistent" should be unchecked
