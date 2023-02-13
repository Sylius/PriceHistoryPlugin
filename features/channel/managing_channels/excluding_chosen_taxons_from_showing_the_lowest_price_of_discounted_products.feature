@managing_channels
Feature: Excluding chosen taxons from displaying the lowest price of discounted products
    In order not to show the lowest price of discounted products on some taxons
    As an Administrator
    I want to be able to configure taxons for which the lowest price of discounted products is not displayed

    Background:
        Given the store operates on a single channel in "Poland"
        And the store classifies its products as "T-Shirts" and "Caps"
        And the store has a product "T-Shirt Banana" available in "Poland" channel
        And this product belongs to "T-Shirts"
        And the store also has product "Anniversary cap" available in "Poland" channel
        And this product belongs to "Caps"
        And I am logged in as an administrator

    @todo
    Scenario: Excluding chosen taxons from displaying the lowest price of discounted products
        When I want to modify a channel "Poland"
        And I enable showing the lowest price of discounted products
        And I exclude the "T-Shirts" taxon from showing the lowest price of discounted products
        And I save my changes
        Then I should be notified that it has been successfully edited
        And the "Poland" channel should have the lowest price of discounted products prior to the current discount enabled
        And the "Poland" channel should have "T-Shirt Banana" product excluded from displaying the lowest price of discounted products
