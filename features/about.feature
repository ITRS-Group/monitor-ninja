@menu-about
Feature: Menu About

	Background:
		Given I am on the main page

	@asmonitor
	Scenario: See that the about menu option is rendered
		When I hover the branding
		And I click "About"
		Then I should see "Version"
		And I should see "License"
		And I should see "Release"