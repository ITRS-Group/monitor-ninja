@menu-about
Feature: Menu About

	Background:
		Given I am logged in
		And I am on the main page

	Scenario: See that the menu option displays properly on hover branding
		When I hover the branding
		Then I should see menu items:
			| About |

	Scenario: See that About page content rendered correct
		When I am on address "/index.php/menu/about"
		Then I should see "Version"
		And I should see "Release"
		And I should see "License"