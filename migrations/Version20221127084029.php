<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221127084029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer CHANGE highest_bid_id highest_bid_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873EAEB93B9F FOREIGN KEY (highest_bid_id) REFERENCES bid (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29D6873EAEB93B9F ON offer (highest_bid_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EAEB93B9F');
        $this->addSql('DROP INDEX UNIQ_29D6873EAEB93B9F ON offer');
        $this->addSql('ALTER TABLE offer CHANGE highest_bid_id highest_bid_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
