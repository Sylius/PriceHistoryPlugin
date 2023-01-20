@managing_product_variants
Feature: Seeing the correct catalog price history after creating a product variant
    In order to be aware of historical product prices
    As an Administrator
    I want to see the catalog price history of the product I've created

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @todo
    Scenario: Seeing historical product variant prices after the product variant has been created without any promotion applied
        When I want to create a new simple product
        And I specify its code as "WYBOROWA_VODKA"
        And I name it "Wyborowa Vodka" in "English (United States)"
        And I set its slug to "wyborowa-vodka" in "English (United States)"
        And I set its price to "$20.00" for "United States" channel
        And I set its original price to "$25.00" for "United States" channel
        And I add it
        And I go to the product price history
        Then I should see a single log entry in the catalog price history for the "Wyborowa Vodka" variant
        And there should be a log entry with the "$20.00" selling price, "$25.00" original price and datetime of the price change

    @todo
    Scenario: Seeing historical product variant prices after the product variant has been created without original price and any promotion applied
        When I want to create a new simple product
        And I specify its code as "WYBOROWA_VODKA"
        And I name it "Wyborowa Vodka" in "English (United States)"
        And I set its slug to "wyborowa-vodka" in "English (United States)"
        And I set its price to "$20.00" for "United States" channel
        And I add it
        And I go to the product price history
        Then I should see a single log entry in the catalog price history for the "Wyborowa Vodka" variant
        And there should be a log entry with the "$20.00" selling price, no original price and datetime of the price change

    @todo
    Scenario: Seeing historical product variant prices after the product variant has been created with catalog promotions applied
        Given the store has "Alcohol" taxonomy
        And there is a catalog promotion "Christmas sale" that reduces price by "50%" and applies on "Alcohol" taxon
        When I want to create a new simple product
        And I choose main taxon "Alcohol"
        And I specify its code as "WYBOROWA_VODKA"
        And I name it "Wyborowa Vodka" in "English (United States)"
        And I set its slug to "wyborowa-vodka" in "English (United States)"
        And I set its price to "$20.00" for "United States" channel
        And I add it
        And I go to the product price history
        Then I should see 2 log entries in the catalog price history for the "Wyborowa Vodka" variant
        And there should be a log entry on the 1st position with the "$10.00" selling price, "$20.00" original price and datetime of the price change
        And there should be a log entry on the 2nd with the "$20.00" selling price, no original price and datetime of the price change
