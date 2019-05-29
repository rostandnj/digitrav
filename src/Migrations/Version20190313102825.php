<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190313102825 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE location CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_detail CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE material_quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE bill ADD reference VARCHAR(25) NOT NULL, CHANGE date date DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7A2119E3AEA34913 ON bill (reference)');
        $this->addSql('ALTER TABLE payment CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evaluation ADD reference VARCHAR(25) NOT NULL, CHANGE date date DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1323A575AEA34913 ON evaluation (reference)');
        $this->addSql('ALTER TABLE statut CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE intervention CHANGE date date DATETIME NOT NULL, CHANGE reference reference VARCHAR(25) NOT NULL, CHANGE start_date start_date DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D11814ABAEA34913 ON intervention (reference)');
        $this->addSql('ALTER TABLE company CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE note CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE date date DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_7A2119E3AEA34913 ON bill');
        $this->addSql('ALTER TABLE bill DROP reference, CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE company CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE date date DATETIME NOT NULL');
        $this->addSql('DROP INDEX UNIQ_1323A575AEA34913 ON evaluation');
        $this->addSql('ALTER TABLE evaluation DROP reference, CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE date date DATETIME NOT NULL');
        $this->addSql('DROP INDEX UNIQ_D11814ABAEA34913 ON intervention');
        $this->addSql('ALTER TABLE intervention CHANGE date date DATETIME NOT NULL, CHANGE reference reference VARCHAR(20) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE start_date start_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE location CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE material_quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE note CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE statut CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_detail CHANGE date date DATETIME NOT NULL');
    }
}
