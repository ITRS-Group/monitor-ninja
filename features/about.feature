@menu-about
Feature: Menu About

	Background:
		Given I am logged in
		And I am on the main page

	Scenario: See that the menu option displays properly on hover branding
		When I hover the branding
		Then I should see menu items:
			| About |

	@editedhappypath
	Scenario: See that About page content rendered correct
		When I hover the branding
		And I click "open-about-button"
		And I should see "Release"
		And I should see "License"

	@editedhappypath
	Scenario: See that Licensing Information page content rendered correct
		When I hover the branding
		And I click "open-about-button"
		And I click name "License Information"
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
	
	@addedhappypath
	Scenario: See that ITRS OP5 Monitor page content rendered correct
		When I hover the branding
		And I click "open-about-button"
		And I click link "ITRS OP5 Monitor"
		Then I should see "ITRS OP5 Monitor"
		And I should see "Exceptional infrastructure monitoring"
		And I should see "Stay updated with ITRS OP5 Monitor"
	
	@addedhappypath
	Scenario: See that Support page content rendered correct
		When I hover the branding
		And I click "open-about-button"
		And I click link "Support"
		Then I should see "How can we help?"
		And I should see "Do you have questions?"
		And I should see "Product specific help"