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

final class Version20230222143342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Channel::taxonsExcludedFromShowingLowestPrice';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_channel_excluded_taxons (channel_id INT NOT NULL, taxon_id INT NOT NULL, INDEX IDX_C5BAE23572F5A1AA (channel_id), INDEX IDX_C5BAE235DE13F470 (taxon_id), PRIMARY KEY(channel_id, taxon_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_channel_excluded_taxons ADD CONSTRAINT FK_C5BAE23572F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_channel_excluded_taxons ADD CONSTRAINT FK_C5BAE235DE13F470 FOREIGN KEY (taxon_id) REFERENCES sylius_taxon (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_channel_excluded_taxons DROP FOREIGN KEY FK_C5BAE23572F5A1AA');
        $this->addSql('ALTER TABLE sylius_channel_excluded_taxons DROP FOREIGN KEY FK_C5BAE235DE13F470');
        $this->addSql('DROP TABLE sylius_channel_excluded_taxons');
    }
}
