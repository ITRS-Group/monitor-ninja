@backup
Feature: Backup/Restore configuration

	Background:
		Given I am logged in as administrator

	@editedhappypath
	Scenario: Create a config backup and then restore to it
		#When I hover "Backup/Restore" from the "Manage" menu
		#And I click link "Backup/Restore"
		When I am on address "/index.php/backup"
		Then I should see "Save your current op5 Monitor configuration"
		And I click "verify_backup"
		Then I should see "The current configuration is valid. Do you really want to backup your current configuration?"
		And I click button "Yes"
		And I wait for 10 seconds
		Then I should see regex "backup-.*\.tar\.gz"
		And I wait for 10 seconds
		And I click the element with class "restore_backup" and href containing "backup-"
		Then I should see "has been restored successfully"

	@addedhappypath
	Scenario: View backup
		When I hover "Backup/Restore" from the "Manage" menu
		And I click link "Backup/Restore"
		Then I should see "Save your current op5 Monitor configuration"
		And I click the element with class "view_backup" and href containing "backup-"
		Then I should see regex "backup-.*\.tar\.gz"
		And I should see "This backup contains the following files:"
	
	@addedhappypath
	Scenario: Delete backup
		When I hover "Backup/Restore" from the "Manage" menu
		And I click name "Backup/Restore"
		Then I should see "Save your current op5 Monitor configuration"
		And I click the element with class "delete_backup" and href containing "backup-"
		And I click button "Yes"
		And I wait for 3 seconds
		Then I should see " has been deleted"
		And I wait for 10 seconds
		And I shouldn't see regex "backup-.*\.tar\.gz"