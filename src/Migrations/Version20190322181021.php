<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190322181021 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE quater');
        $this->addSql('ALTER TABLE location CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_detail CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE date date DATETIME NOT NULL, CHANGE activation_date activation_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE role CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE material_quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE bill CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE account_validation CHANGE date date DATETIME NOT NULL, CHANGE last_date last_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evaluation CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE statut CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE intervention ADD location_id INT NOT NULL, CHANGE date date DATETIME NOT NULL, CHANGE start_date start_date DATETIME NOT NULL, CHANGE end_date end_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('CREATE INDEX IDX_D11814AB64D218E ON intervention (location_id)');
        $this->addSql('ALTER TABLE company CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE note CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE date date DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE quater (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE account_validation CHANGE date date DATETIME NOT NULL, CHANGE last_date last_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE bill CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE company CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evaluation CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB64D218E');
        $this->addSql('DROP INDEX IDX_D11814AB64D218E ON intervention');
        $this->addSql('ALTER TABLE intervention DROP location_id, CHANGE date date DATETIME NOT NULL, CHANGE start_date start_date DATETIME NOT NULL, CHANGE end_date end_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE location CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE material_quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE note CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE statut CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE date date DATETIME NOT NULL, CHANGE activation_date activation_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user_detail CHANGE date date DATETIME NOT NULL');
    }
}
