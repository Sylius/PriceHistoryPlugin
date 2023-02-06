@viewing_products
Feature: Seeing the product's lowest price from 30 days before the discount
    In order to know whether the current discount is attractive
    As a Guest
    I want to see a product's lowest price within 30 days before the discount

    Background:
        Given the store operates on a single channel in "United States"

    @todo
    Scenario: Not seeing the lowest price information on a product with no discount
        Given the store has a product "Wyborowa Vodka" priced at "$21.00"
        When I check this product's details
        Then I should not see information about its lowest price

    @todo
    Scenario: Seeing the lowest price information on a product that has a single discount
        Given the store has a product "Wyborowa Vodka" priced at "$37.00"
        And this product's price was discounted to "$21.00"
        When I check this product's details
        Then I should see "$37.00" as this product's lowest price from 30 days before the discount

    @todo
    Scenario: Seeing the lowest price information on a product that has a discount, and then seeing the discount change
        Given the store has a product "Wyborowa Vodka" priced at "$37.00"
        And this product's price was discounted to "$21.00"
        And this product's discounted price changed to "$30.00"
        When I check this product's details
        Then I should see "$21.00" as this product's lowest price from 30 days before the discount

    @todo
    Scenario: Seeing the lowest price information on a product that has a discount, over a month passes, and then seeing the discount change
        Given it is "2023-03-14" now
        And the store has a product "Wyborowa Vodka" priced at "$42.00"
        And this product's price was discounted to "$21.00"
        And it is "2023-04-15" now
        And this product's discounted price changed to "$33.00"
        When I check this product's details
        Then I should see "$21.00" as this product's lowest price from 30 days before the discount

    @todo
    Scenario: Seeing the lowest price information on a product that has a discount and the one of the previous promotions ended over 30 days ago
        Given it is "2022-12-01" now
        And the store has a product "Wyborowa Vodka" priced at "$42.00"
        And this product's price was discounted to "$21.00"
        And it is "2023-01-01" now
        And this product's price was changed to "$39.00"
        And it is "2023-02-15" now
        And this product's discounted price changed to "$33.00"
        When I check this product's details
        Then I should see "$39.00" as this product's lowest price from 30 days before the discount

    @todo
    Scenario: Not seeing the lowest price information on a product that has a discount and the previous promotion ended over 30 days ago
        Given it is "2022-12-01" now
        And the store has a product "Wyborowa Vodka" priced at "$42.00"
        And this product's price was discounted to "$21.00"
        And it is "2023-01-01" now
        And this product's discount was removed
        And it is "2023-02-15" now
        And this product's price was discounted to "$33.00"
        When I check this product's details
        Then I should not see information about its lowest price

    @todo
    Scenario: Not seeing the lowest price information on a product that had a discount and the discount was removed
        Given the store has a product "Wyborowa Vodka" priced at "$37.00"
        And this product's price was discounted to "$21.00"
        And this product's discount was removed
        When I check this product's details
        Then I should not see information about its lowest price

    @todo
    Scenario: Seeing the lowest price information on a product that had a discount, the discount was removed, the price changed below the discount price, the price changed back to the start value and then the less attractive discount was added
        Given the store has a product "Wyborowa Vodka" priced at "$37.00"
        And this product's price was discounted to "$21.00"
        And this product's discount was removed
        And this product's price was changed to "$10.00"
        And this product's price was changed back to "$21.00"
        And this product's price was discounted to "$20.00"
        When I check this product's details
        Then I should see "$10.00" as this product's lowest price from 30 days before the discount
