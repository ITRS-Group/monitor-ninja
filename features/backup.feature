@backup
Feature: Backup/Restore configuration

	Background:
		Given I am logged in as administrator

	Scenario: Create a config backup and then restore to it
		When I am on address "/index.php/backup"
		Then I should see "Save your current op5 Monitor configuration"
		And I click "verify_backup"
		Then I should see "The current configuration is valid. Do you really want to backup your current configuration?"
		And I click button "Yes"
		And I wait for 3 seconds
		Then I should see regex "backup-.*\.tar\.gz"
		And I click "Restore Backup"
		Then I should see "Do you really want to restore this backup?"
		And I click button "Yes"
		And I wait for 3 seconds
		Then I should see regex "The configuration .* has been restored successfully"
