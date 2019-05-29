<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190311202258 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE material_quote (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(40) NOT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_3D9667BA12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE material_quote ADD CONSTRAINT FK_3D9667BA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE location CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_detail CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE bill CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evaluation CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE statut CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE intervention CHANGE date date DATETIME NOT NULL, CHANGE start_date start_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE company CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE note CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE date date DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE material_quote');
        $this->addSql('ALTER TABLE bill CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE company CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evaluation CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE intervention CHANGE date date DATETIME NOT NULL, CHANGE start_date start_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE location CHANGE date date DATETIME NOT NULL');
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
