<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190311202620 extends AbstractMigration
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
        $this->addSql('ALTER TABLE bill CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D19EB6921');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DCD53EDB6');
        $this->addSql('DROP INDEX IDX_6D28840D19EB6921 ON payment');
        $this->addSql('DROP INDEX IDX_6D28840DCD53EDB6 ON payment');
        $this->addSql('ALTER TABLE payment ADD client VARCHAR(100) NOT NULL, ADD receiver VARCHAR(100) NOT NULL, DROP client_id, DROP receiver_id, CHANGE date date DATETIME NOT NULL');
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

        $this->addSql('ALTER TABLE bill CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE company CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evaluation CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE intervention CHANGE date date DATETIME NOT NULL, CHANGE start_date start_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE location CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE material_quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE note CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment ADD client_id INT NOT NULL, ADD receiver_id INT NOT NULL, DROP client, DROP receiver, CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6D28840D19EB6921 ON payment (client_id)');
        $this->addSql('CREATE INDEX IDX_6D28840DCD53EDB6 ON payment (receiver_id)');
        $this->addSql('ALTER TABLE quote CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE statut CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_detail CHANGE date date DATETIME NOT NULL');
    }
}
