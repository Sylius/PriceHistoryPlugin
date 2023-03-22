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
The legacy installation guide is available [here](docs/legacy-installation-guide.md).

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

5. Execute migrations:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

6. Rebuild the cache to display all new translations correctly:

    ```bash
    bin/console cache:clear
    bin/console cache:warmup
   ```
