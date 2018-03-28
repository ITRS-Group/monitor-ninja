@exportsave
Feature: Export and save configuration messages in nachos

        Scenario: See that message banner is displayed in different states
                Given I am logged in
                And I am on "https://localhost/ninja/index.php/exportsave/banner"
                Then the banner should have text "Saving changes (step 2 of 4)"