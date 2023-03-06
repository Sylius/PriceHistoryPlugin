@viewing_products
Feature: Not seeing the product's lowest price in taxons that have been excluded on the channel
    In order to see the product's lowest price only where it is necessary
    As a Guest
    I don't want to see the product's lowest price in taxons that have been excluded on the channel

    Background:
        Given the store operates on a single channel in "United States"
        And the store classifies its products as "T-Shirts"
        And the store has a product "T-Shirt Banana" priced at "$21.00"
        And it belongs to "T-Shirts"
        And this product's price changed to "$10.00" and original price changed to "$21.00"
        And the store also has a product "T-Shirt Watermelon" priced at "$22.00"
        And it belongs to "T-Shirts"
        And the "T-Shirts" taxon is excluded from showing the lowest price of discounted products in the "United States" channel

    @todo
    Scenario: Not seeing the product's lowest price in taxon which was excluded
        When I browse products from taxon "T-Shirts"
        And I view product "T-Shirt Banana"
        Then I should not see information about its lowest price

    @todo
    Scenario: Not seeing the product's lowest price in taxon which was excluded and not discounted
        When I browse products from taxon "T-Shirts"
        And I view product "T-Shirt Watermelon"
        Then I should not see information about its lowest price
