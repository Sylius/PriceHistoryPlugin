@managing_channels
Feature: Excluding chosen taxons from displaying the lowest price of discounted products
    In order not to show the lowest price of discounted products on some taxons
    As an Administrator
    I want to be able to configure taxons for which the lowest price of discounted products is not displayed

    Background:
        Given the store operates on a channel named "Poland"
        And the store classifies its products as "T-Shirts", "Caps" and "Sweaters"
        And I am logged in as an administrator

    @todo
    Scenario: Excluding a singular taxon from displaying the lowest price of discounted products
        When I want to modify a channel "Poland"
        And I exclude the "T-Shirts" taxon from showing the lowest price of discounted products
        And I save my changes
        Then I should be notified that it has been successfully edited
        And the "Poland" channel should have "T-Shirts" taxon excluded from displaying the lowest price of discounted products

    @todo
    Scenario: Excluding multiple taxons from displaying the lowest price of discounted products
        When I want to modify a channel "Poland"
        And I exclude the "T-Shirts" and "Caps" taxons from showing the lowest price of discounted products
        And I save my changes
        Then I should be notified that it has been successfully edited
        And the "Poland" channel should have "T-Shirts" taxon excluded from displaying the lowest price of discounted products
        And the "Poland" channel should have "Caps" taxon excluded from displaying the lowest price of discounted products

    @todo
    Scenario: Removing a taxon from the from displaying the lowest price of discounted products exclusion
        Given the "Poland" channel has "T-Shirts" taxon excluded from displaying the lowest price of discounted products
        When I want to modify a channel "Poland"
        And I remove the "T-Shirts" taxon from the list of taxons excluded from showing the lowest price of discounted products
        And I save my changes
        Then I should be notified that it has been successfully edited
        And the "Poland" channel should not have "T-Shirts" taxon excluded from displaying the lowest price of discounted products
