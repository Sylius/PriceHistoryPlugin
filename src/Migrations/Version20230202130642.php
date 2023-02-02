<?php

declare(strict_types=1);

namespace Sylius\PriceHistoryPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230202130642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add `visible` field to the ChannelPricingLogEntry';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_price_history_channel_pricing_log_entry ADD visible TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_price_history_channel_pricing_log_entry DROP visible');
    }
}
