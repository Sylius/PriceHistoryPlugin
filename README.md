<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Price History Plugin</h1>


<p align="center"><a href="https://sylius.com/plugins/" target="_blank"><img src="https://sylius.com/assets/badge-official-sylius-plugin.png" width="200"></a></p>

⚙️ Installation
===============

We encourage you to use the installation instructions based on Rector and Recipes as it is more convenient and faster.
Below you can also find an instruction using the traditional installation process.

Installation with Recipes and Rector
------------------------------------

Before you start, you need to have both SyliusRecipes and SyliusRector installed. You can find how to install them here:
- [Sylius/SyliusRecipes](https://github.com/Sylius/SyliusRecipes)
- [Sylius/SyliusRector](https://github.com/Sylius/SyliusRector)

1. Run:

    ```bash
    composer require sylius/price-history-plugin --no-scripts
    ```

2. Update `<project_root>/rector.php`

    ```diff
    + use Sylius\SyliusRector\Set\SyliusPriceHistory;
   
    return static function (RectorConfig $rectorConfig): void {
        // ...
    +    $rectorConfig->sets([SyliusPriceHistory::PRICE_HISTORY_PLUGIN]);
    };

3. Run:

    ```bash
    vendor/bin/rector
    ```

4. Ensure you have modified resource configured in `config/packages/_sylius.yaml`:

    ```yaml
    sylius_customer:
        resources:
            customer:
                classes:
                    model: App\Entity\Customer\Customer
    sylius_core:
        resources:
            channel_pricing:
                classes:
                    model: App\Entity\Channel\ChannelPricing
    ```

5. Execute migrations:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

6. Rebuild the cache to display all new translations correctly:

    ```bash
    bin/console cache:clear
    bin/console cache:warmup
   ```

Traditional installation
------------------------

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
    sylius_customer:
        resources:
            customer:
                classes:
                    model: App\Entity\Customer\Customer
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
