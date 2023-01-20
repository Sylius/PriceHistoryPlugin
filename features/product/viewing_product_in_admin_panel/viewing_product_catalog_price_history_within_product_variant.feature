@viewing_products
Feature: Seeing catalog price history within a variant
    In order to be aware of historical variant prices
    As an Administrator
    I want to browse the catalog price history of a specific variant

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a "Wyborowa Vodka" configurable product
        And the product "Wyborowa Vodka" has a "Wyborowa Vodka Exquisite" variant priced at "$40.00"
        And the product "Wyborowa Vodka" has a "Wyborowa Vodka Lemon" variant priced at "$10.00"
        And there is disabled catalog promotion "Winter sale" with priority 1 that reduces price by "50%" and applies on "Wyborowa Vodka Exquisite" variant
        And there is disabled catalog promotion "Christmas sale" with priority 2 that reduces price by fixed "$5.00" in the "United States" channel and applies on "Wyborowa Vodka" product
        And the "Wyborowa Vodka Exquisite" variant is now priced at "$45.00" and originally priced at "$15.00"
        And I am logged in as an administrator

    @todo
    Scenario: Seeing the catalog price history of a variant with many catalog promotions
        When I enable "Winter sale" catalog promotion
        And I enable "Christmas sale" catalog promotion
        And I access "Wyborowa Vodka" product
        And I go to the "Wyborowa Vodka Exquisite" variant price history
        Then I should see 4 log entries in the catalog price history for the "Wyborowa Vodka" variant
        And there should be a log entry on the 1st position with the "$2.50" selling price, "$15.00" original price and datetime of the price change
        And there should be a log entry on the 2nd position with the "$7.50" selling price, "$15.00" original price and datetime of the price change
        And there should be a log entry on the 3rd position with the "$45.00" selling price, "$15.00" original price and datetime of the price change
        And there should be a log entry on the 4th position with the "$40.00" selling price, no original price and datetime of the price change

    @todo
    Scenario: Seeing the catalog price history of a variant with one catalog promotion
        When I enable "Winter sale" catalog promotion
        And I enable "Christmas sale" catalog promotion
        And I access "Wyborowa Vodka" product
        And I go to the "Wyborowa Vodka Lemon" variant price history
        Then I should see 2 log entries in the catalog price history for the "Wyborowa Vodka Lemon" variant
        And there should be a log entry on the 1st position with the "$5.00" selling price, "$10.00" original price and datetime of the price change
        And there should be a log entry on the 2nd position with the "$10.00" selling price, no original price and datetime of the price change
