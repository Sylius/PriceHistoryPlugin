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

final class Version20230317123447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ChannelPriceHistoryConfig';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_channel_price_history_config (id INT AUTO_INCREMENT NOT NULL, channel_id INT DEFAULT NULL, lowest_price_for_discounted_products_checking_period INT DEFAULT 30 NOT NULL, lowest_price_for_discounted_products_visible TINYINT(1) DEFAULT true NOT NULL, UNIQUE INDEX UNIQ_D0F282FA72F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_channel_price_history_config ADD CONSTRAINT FK_D0F282FA72F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)');
        $this->addSql('ALTER TABLE sylius_channel ADD channel_price_history_config_id INT DEFAULT NULL, DROP lowest_price_for_discounted_products_visible, DROP lowest_price_for_discounted_products_checking_period');
        $this->addSql('ALTER TABLE sylius_channel ADD CONSTRAINT FK_16C8119E75F20EAE FOREIGN KEY (channel_price_history_config_id) REFERENCES sylius_channel_price_history_config (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16C8119E75F20EAE ON sylius_channel (channel_price_history_config_id)');
        $this->addSql('ALTER TABLE sylius_price_history_channel_excluded_taxons DROP FOREIGN KEY FK_C5BAE23572F5A1AA');
        $this->addSql('ALTER TABLE sylius_price_history_channel_excluded_taxons ADD CONSTRAINT FK_C5BAE23572F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel_price_history_config (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_channel DROP FOREIGN KEY FK_16C8119E75F20EAE');
        $this->addSql('ALTER TABLE sylius_price_history_channel_excluded_taxons DROP FOREIGN KEY FK_C5BAE23572F5A1AA');
        $this->addSql('ALTER TABLE sylius_channel_price_history_config DROP FOREIGN KEY FK_D0F282FA72F5A1AA');
        $this->addSql('DROP TABLE sylius_channel_price_history_config');
        $this->addSql('DROP INDEX UNIQ_16C8119E75F20EAE ON sylius_channel');
        $this->addSql('ALTER TABLE sylius_channel ADD lowest_price_for_discounted_products_visible TINYINT(1) DEFAULT 1 NOT NULL, ADD lowest_price_for_discounted_products_checking_period INT DEFAULT 30 NOT NULL, DROP channel_price_history_config_id');
        $this->addSql('ALTER TABLE sylius_price_history_channel_excluded_taxons DROP FOREIGN KEY FK_C5BAE23572F5A1AA');
        $this->addSql('ALTER TABLE sylius_price_history_channel_excluded_taxons ADD CONSTRAINT FK_C5BAE23572F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
