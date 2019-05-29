<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190319140956 extends AbstractMigration
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
        $this->addSql('ALTER TABLE user_detail ADD cni_file_id INT DEFAULT NULL, CHANGE cni cni VARCHAR(20) DEFAULT NULL, CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_detail ADD CONSTRAINT FK_4B5464AE1FFE0591 FOREIGN KEY (cni_file_id) REFERENCES file (id)');
        $this->addSql('CREATE INDEX IDX_4B5464AE1FFE0591 ON user_detail (cni_file_id)');
        $this->addSql('ALTER TABLE user CHANGE date date DATETIME NOT NULL, CHANGE activation_date activation_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE role CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE material_quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE bill CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evaluation CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE statut CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE intervention CHANGE date date DATETIME NOT NULL, CHANGE start_date start_date DATETIME NOT NULL, CHANGE end_date end_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE company CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE note CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE date date DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE quater (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE bill CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE company CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evaluation CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE intervention CHANGE date date DATETIME NOT NULL, CHANGE start_date start_date DATETIME NOT NULL, CHANGE end_date end_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE location CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE material_quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE note CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE statut CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE date date DATETIME NOT NULL, CHANGE activation_date activation_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user_detail DROP FOREIGN KEY FK_4B5464AE1FFE0591');
        $this->addSql('DROP INDEX IDX_4B5464AE1FFE0591 ON user_detail');
        $this->addSql('ALTER TABLE user_detail DROP cni_file_id, CHANGE cni cni VARCHAR(20) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE date date DATETIME NOT NULL');
    }
}
