@backup
Feature: Backup/Restore configuration

	Background:
		Given I am logged in as administrator

	@gian
	Scenario: Create a config backup and then restore to it
		When I hover "Backup/Restore" from the "Manage" menu
		And I click link "Backup/Restore"
		Then I should see "Save your current op5 Monitor configuration"
		And I click "verify_backup"
		Then I should see "The current configuration is valid. Do you really want to backup your current configuration?"
		And I click button "Yes"
		And I wait for 10 seconds
		Then I should see regex "backup-.*\.tar\.gz"
		And I click the element with class "delete_backup" and href containing "backup-"
		#And I click "Restore Backup"
		Then I should see "Do you really want to restore this backup?"
		And I click button "Yes"
		And I wait for 10 seconds
		Then I should see button "Restart now"
		And I should see button "Close"

	@gian
	Scenario: View backup
		When I hover "Backup/Restore" from the "Manage" menu
		And I click link "Backup/Restore"
		Then I should see "Save your current op5 Monitor configuration"
		And I click link "View Backup"
		Then I should see regex "backup-.*\.tar\.gz"
		And I should see "This backup contains the following files:"
	
	@gian
	Scenario: Delete backup
		When I hover "Backup/Restore" from the "Manage" menu
		And I click link "Backup/Restore"
		Then I should see "Save your current op5 Monitor configuration"
		And I click link "Delete Backup"
		And I click button "Yes"
		And I wait for 3 seconds
		Then I should see " has been deleted"
		And I hover "Backup/Restore" from the "Manage" menu
		And I click link "Backup/Restore"
		And I shouldn't see regex "backup-.*\.tar\.gz"