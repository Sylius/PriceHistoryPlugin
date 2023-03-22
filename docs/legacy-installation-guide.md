Legacy installation
-------------------

1. Run:

    ```bash
    composer require sylius/price-history-plugin --no-scripts
    ```

2. Add the following line to the `packages/bundles.php`

    ```php
    Sylius\PriceHistoryPlugin\SyliusPriceHistoryPlugin::class => ['all' => true]
    ```

3. The `Channel` entity should implement the following interface:

    ```php
    Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface
    ```

   and use trait:

    ```php
    Sylius\PriceHistoryPlugin\Domain\Model\LowestPriceForDiscountedProductsAwareTrait
    ```

   Final result:

    ```php
    <?php
    
    declare(strict_types=1);
    
    namespace App\Entity\Channel;
    
    use Doctrine\ORM\Mapping as ORM;
    use Sylius\Component\Core\Model\Channel as BaseChannel;
    use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
    use Sylius\PriceHistoryPlugin\Domain\Model\LowestPriceForDiscountedProductsAwareTrait;
    
   /**
    * @ORM\Entity
    * @ORM\Table(name="sylius_channel")
    */
    #[ORM\Entity]
    #[ORM\Table(name: 'sylius_channel')]
    class Channel extends BaseChannel implements ChannelInterface
    {
        use LowestPriceForDiscountedProductsAwareTrait;
    }
    ```

4. The `ChannelPricing` entity should implement the following interface:

    ```php
    Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface
    ```

   and use trait:

    ```php
    Sylius\PriceHistoryPlugin\Domain\Model\LowestPriceBeforeDiscountAwareTrait
    ```

   Final result:

    ```php
    <?php
    
    declare(strict_types=1);
    
    namespace App\Entity\Channel;
    
    use Doctrine\ORM\Mapping as ORM;
    use Sylius\Component\Core\Model\ChannelPricing as BaseChannelPricing;
    use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
    use Sylius\PriceHistoryPlugin\Domain\Model\LowestPriceBeforeDiscountAwareTrait;
    
   /**
    * @ORM\Entity
    * @ORM\Table(name="sylius_channel_pricing")
    */
    #[ORM\Entity]
    #[ORM\Table(name: 'sylius_channel_pricing')]
    class ChannelPricing extends BaseChannelPricing implements ChannelPricingInterface
    {
    use LowestPriceBeforeDiscountAwareTrait;
    }
    ```

5. Ensure you have modified resource configured in `config/packages/_sylius.yaml`:

    ```yaml
    sylius_channel:
        resources:
            channel:
                classes:
                    model: App\Entity\Channel\Channel
    sylius_core:
        resources:
            channel_pricing:
                classes:
                    model: App\Entity\Channel\ChannelPricing
    ```

6. Import configuration to `config/packages/_sylius.yaml`:

    ```yaml
    imports:
        - { resource: "@SyliusPriceHistoryPlugin/config/config.yaml" }
    ```

7. Configure routing in `config/routes/sylius_admin.yaml`:

    ```yaml
    sylius_price_history_admin:
        resource: "@SyliusPriceHistoryPlugin/config/admin_routing.yaml"
        prefix: '/%sylius_admin.path_name%'
    ```

8. Execute migrations:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

9. Rebuild the cache to display all new translations correctly:

    ```bash
    bin/console cache:clear
    bin/console cache:warmup
    ```
