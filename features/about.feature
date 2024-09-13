@menu-about
Feature: Menu About

	Background:
		Given I am logged in
		And I am on the main page

	Scenario: 
		When I hover the branding
		Then I should see menuSee that the menu option displays properly on hover branding items:
			| About |

	Scenario: See that About page content rendered correct
		When I am on address "/index.php/menu/about"
		Then I should see "Version"
		And I should see "Release"
		And I should see "License"

	Scenario: See that Licensing Information page content rendered correct
		When I am on address "/index.php/menu/license_info"
		Then I should see "Software Licensing Information"

	@addedhappypath
	Scenario: See that Knowledge Base page content rendered correct
		When I hover the branding
		And I click "open-about-button"
		And I click link "Knowledge Base"
		Then I should see "Introduction"
		And I should see "Prerequisites"
		And I should see "Install"
		And I should see "Get started"
		And I should see "Troubleshoot"