@viewing_products
Feature: Seeing the corresponding lowest price before the discount when selecting different product variants
    In order to be aware of the lowest price before the discount for the chosen product variant
    As a Customer
    I want to see the corresponding lowest price before the discount for the chosen product variant

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a "Wyborowa Vodka" configurable product
        And the product "Wyborowa Vodka" has "Wyborowa Vodka 40%" variant priced at "$40.00"
        And the product "Wyborowa Vodka" has "Wyborowa Vodka 50%" variant priced at "$50.00"
        And the product "Wyborowa Vodka" has "Wyborowa Vodka 30%" variant priced at "$60.00"
        And there is a catalog promotion "Winter sale" with priority 1 that reduces price by "50%" and applies on "Wyborowa Vodka" product
        And the store has a "Bocian Vodka" configurable product
        And the product "Bocian Vodka" has "Bocian Vodka Caramel" variant priced at "$40.00"
        And the product "Bocian Vodka" has "Bocian Vodka Advocate" variant priced at "$20.00"
        And there is a catalog promotion "Summer sale" with priority 1 that reduces price by "50%" and applies on "Bocian Vodka Caramel" variant

    @no-api @ui @javascript
    Scenario: Seeing correct lowest price when selecting first variant from the list
        When I view product "Wyborowa Vodka"
        And I select "Wyborowa Vodka 40%" variant
        Then I should see "$40.00" as its lowest price before the discount

    @no-api @ui @javascript
    Scenario: Seeing correct lowest price when selecting another variant from the list
        When I view product "Wyborowa Vodka"
        And I select "Wyborowa Vodka 50%" variant
        Then I should see "$50.00" as its lowest price before the discount

    @no-api @ui @javascript
    Scenario: Seeing correct lowest price when selecting first variant from the list after selecting another variant
        When I view product "Wyborowa Vodka"
        And I select "Wyborowa Vodka 50%" variant
        And I select "Wyborowa Vodka 30%" variant
        And I select "Wyborowa Vodka 40%" variant
        Then I should see "$40.00" as its lowest price before the discount

    @no-api @ui @javascript
    Scenario: Seeing the correct lowest price when selecting the first discounted variant from the list
        When I view product "Bocian Vodka"
        And I select "Bocian Vodka Caramel" variant
        Then I should see "$40.00" as its lowest price before the discount

    @no-api @ui @javascript
    Scenario: Unable to see the lowest price when there is a discount on the first variant, but not on the selected variant
        When I view product "Bocian Vodka"
        And I select "Bocian Vodka Advocate" variant
        Then I should not see information about its lowest price
