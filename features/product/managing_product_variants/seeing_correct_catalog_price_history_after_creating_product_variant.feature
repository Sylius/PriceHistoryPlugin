@managing_product_variants
Feature: Seeing the correct catalog price history after creating a product variant
    In order to be aware of historical product prices
    As an Administrator
    I want to see the catalog price history of the product I've created

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @api
    Scenario: Seeing historical product variant prices after the product variant has been created without any promotion applied
        Given the store has a "Wyborowa Vodka" configurable product
        When I want to create a new variant of this product
        And I specify its code as "WYBOROWA_VODKA"
        And I set its price to "$20.00" for "United States" channel
        And I set its original price to "$25.00" for "United States" channel
        And I add it
        And I go to the price history of a variant with code "WYBOROWA_VODKA"
        Then I should see a single log entry in the catalog price history
        And there should be a log entry with the "$20.00" selling price, "$25.00" original price and datetime of the price change

    @api
    Scenario: Seeing historical product variant prices after the product variant has been created without original price and any promotion applied
        Given the store has a "Wyborowa Vodka" configurable product
        When I want to create a new variant of this product
        And I specify its code as "WYBOROWA_VODKA"
        And I set its price to "$20.00" for "United States" channel
        And I add it
        And I go to the price history of a variant with code "WYBOROWA_VODKA"
        Then I should see a single log entry in the catalog price history
        And there should be a log entry with the "$20.00" selling price, no original price and datetime of the price change

    @api
    Scenario: Seeing historical product variant prices after the product variant has been created with catalog promotions applied
        Given the store has a "Wyborowa Vodka" configurable product
        And there is a catalog promotion "Christmas sale" that reduces price by "50%" and applies on "Wyborowa Vodka" product
        When I want to create a new variant of this product
        And I specify its code as "WYBOROWA_VODKA"
        And I set its price to "$20.00" for "United States" channel
        And I add it
        And I go to the price history of a variant with code "WYBOROWA_VODKA"
        Then I should see 2 log entries in the catalog price history
        And there should be a log entry on the 1st position with the "$10.00" selling price, "$20.00" original price and datetime of the price change
        And there should be a log entry on the 2nd position with the "$20.00" selling price, no original price and datetime of the price change

    @todo
    Scenario: Seeing historical product variant prices visible in the catalog only, after the product variant has been created but disabled
        Given the store has a "Wyborowa Vodka" configurable product
        When I want to create a new variant of this product
        And I specify its code as "WYBOROWA_VODKA"
        And I set its price to "$20.00" for "United States" channel
        And I set its original price to "$25.00" for "United States" channel
        #And I disable it # It's not possible to disable a product variant in the current API state
        And I add it
        And I go to the "Wyborowa Vodka" product variant price history
        Then I should not see any log entries in the catalog price history

    @api
    Scenario: Seeing historical product variant prices visible in the catalog only, after the product variant has been created within the disabled product
        Given the store has a "Wyborowa Vodka" configurable product
        And this product has been disabled
        When I want to create a new variant of this product
        And I specify its code as "WYBOROWA_VODKA"
        And I set its price to "$20.00" for "United States" channel
        And I set its original price to "$25.00" for "United States" channel
        And I add it
        And I go to the "Wyborowa Vodka" product variant price history
        Then I should not see any log entries in the catalog price history

    @api
    Scenario: Seeing historical product variant prices visible in the catalog only, after the product variant has been created with no channel enabled
        Given the store has a "Wyborowa Vodka" configurable product with no channel enabled
        When I want to create a new variant of this product
        And I specify its code as "WYBOROWA_VODKA"
        And I set its price to "$20.00" for "United States" channel
        And I set its original price to "$25.00" for "United States" channel
        And I add it
        And I go to the "Wyborowa Vodka" product variant price history
        Then I should not see any log entries in the catalog price history
