@viewing_products
Feature: Not seeing the product's lowest price on taxon which was excluded
    In order to see the product's lowest price only on taxon where it is necessary
    As a Guest
    I don't want to see the product's lowest price on taxon which was excluded

    Background:
        Given the store operates on a single channel in "United States"
        And the store classifies its products as "T-Shirts" and "Caps"
        And the store has a product "T-Shirt Banana" priced at "$21.00"
        And this product's price was discounted to "$10.00"
        And the store also has a product "T-Shirt Watermelon" priced at "$22.00"
        And the "T-Shirt Banana" and "T-Shirt Watermelon" products belong to "T-Shirts" taxon
        And the store has a product "Sylius cap" priced at "$10.00"
        And this product's price was discounted to "$2.00"
        And the store also has a product "Anniversary cap" priced at "$20.00"
        And this "Sylius cap" and "Anniversary cap" products belong to "Caps" taxon
        And The "T-Shirts" taxon is excluded from showing the lowest price in channel "United States"

    @todo
    Scenario: Seeing the product's lowest price on taxon which was not excluded
        When I browse products from taxon "Caps"
        And I view product "Sylius Cap"
        Then I should see "$10.00" as this product's lowest price from 30 days before the discount

    @todo
    Scenario: Not seeing the product's lowest price on taxon which was not excluded and not discounted
        When I browse products from taxon "Caps"
        And I view product "Anniversary Cap"
        Then I should not see information about its lowest price

    @todo
    Scenario: Not seeing the product's lowest price on taxon which was excluded
        When I browse products from taxon "T-Shirts"
        And I view product "T-Shirt Banana"
        Then I should not see information about its lowest price

    @todo
    Scenario: Not seeing the product's lowest price on taxon which was excluded and not discounted
        When I browse products from taxon "T-Shirts"
        And I view product "T-Shirt Watermelon"
        Then I should not see information about its lowest price
