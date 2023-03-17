<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\PriceHistoryPlugin\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230126131430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ChannelPricingLogEntry';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_channel_pricing_log_entry (id INT AUTO_INCREMENT NOT NULL, channel_pricing_id INT NOT NULL, price INT NOT NULL, original_price INT DEFAULT NULL, logged_at DATETIME NOT NULL, INDEX IDX_B3F5AAC23EADFFE5 (channel_pricing_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_channel_pricing_log_entry ADD CONSTRAINT FK_B3F5AAC23EADFFE5 FOREIGN KEY (channel_pricing_id) REFERENCES sylius_channel_pricing (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO `sylius_channel_pricing_log_entry` (`channel_pricing_id`, `price`, `original_price`, `logged_at`) SELECT `id`, `price`, `original_price`, NOW() FROM `sylius_channel_pricing`');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_channel_pricing_log_entry DROP FOREIGN KEY FK_B3F5AAC23EADFFE5');
        $this->addSql('DROP TABLE sylius_channel_pricing_log_entry');
    }
}
