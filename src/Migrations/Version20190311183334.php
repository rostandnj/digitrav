<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190311183334 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, city VARCHAR(25) NOT NULL, quater VARCHAR(30) NOT NULL, longitude VARCHAR(20) DEFAULT NULL, latitude VARCHAR(20) DEFAULT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, detail VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_detail (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, criminal_record_id INT NOT NULL, cni VARCHAR(20) NOT NULL, is_valid TINYINT(1) NOT NULL, is_company TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, date DATETIME NOT NULL, note INT NOT NULL, INDEX IDX_4B5464AE979B1AD6 (company_id), INDEX IDX_4B5464AE5C980E05 (criminal_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, picture_id INT NOT NULL, location_id INT NOT NULL, user_detail_id INT DEFAULT NULL, name VARCHAR(35) NOT NULL, surname VARCHAR(35) DEFAULT NULL, phone VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, gender TINYINT(1) NOT NULL, is_valid TINYINT(1) NOT NULL, is_close TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, token VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_8D93D649D60322AC (role_id), INDEX IDX_8D93D649EE45BDBF (picture_id), INDEX IDX_8D93D64964D218E (location_id), INDEX IDX_8D93D649D8308E5F (user_detail_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, domain_id INT NOT NULL, name_fr VARCHAR(35) NOT NULL, name_en VARCHAR(35) NOT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_64C19C1115F0EE5 (domain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, intervention_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(20) NOT NULL, extension VARCHAR(5) NOT NULL, size INT NOT NULL, is_profile TINYINT(1) NOT NULL, path VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, date DATETIME NOT NULL, INDEX IDX_8C9F36108EAE3863 (intervention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE domain (id INT AUTO_INCREMENT NOT NULL, name_fr VARCHAR(30) NOT NULL, name_en VARCHAR(30) NOT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quote (id INT AUTO_INCREMENT NOT NULL, technician_id INT NOT NULL, intervention_id INT NOT NULL, bill_id INT DEFAULT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, statut TINYINT(1) NOT NULL, INDEX IDX_6B71CBF4E6C5D496 (technician_id), INDEX IDX_6B71CBF48EAE3863 (intervention_id), INDEX IDX_6B71CBF41A8C12F5 (bill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bill (id INT AUTO_INCREMENT NOT NULL, operator_id INT DEFAULT NULL, payment_id INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, date DATETIME NOT NULL, amount VARCHAR(10) NOT NULL, statut INT NOT NULL, INDEX IDX_7A2119E3584598A3 (operator_id), INDEX IDX_7A2119E34C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, receiver_id INT NOT NULL, reference VARCHAR(100) NOT NULL, type INT NOT NULL, amount VARCHAR(10) NOT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_6D28840D19EB6921 (client_id), INDEX IDX_6D28840DCD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, intervention_id INT NOT NULL, technician_id INT NOT NULL, bill_id INT DEFAULT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, statut INT NOT NULL, message LONGTEXT NOT NULL, INDEX IDX_1323A5758EAE3863 (intervention_id), INDEX IDX_1323A575E6C5D496 (technician_id), INDEX IDX_1323A5751A8C12F5 (bill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statut (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, notification_id INT NOT NULL, is_active TINYINT(1) NOT NULL, date DATETIME NOT NULL, INDEX IDX_E564F0BFA76ED395 (user_id), INDEX IDX_E564F0BFEF1A9D84 (notification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, operator_id INT DEFAULT NULL, category_id INT NOT NULL, domain_id INT NOT NULL, client_id INT DEFAULT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, statut INT NOT NULL, reference VARCHAR(20) NOT NULL, client_name VARCHAR(40) DEFAULT NULL, start_date DATETIME NOT NULL, nb_file INT NOT NULL, is_main TINYINT(1) NOT NULL, INDEX IDX_D11814AB584598A3 (operator_id), INDEX IDX_D11814AB12469DE2 (category_id), INDEX IDX_D11814AB115F0EE5 (domain_id), INDEX IDX_D11814AB19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, logo_id INT DEFAULT NULL, location_id INT NOT NULL, trade_register_id INT NOT NULL, tax_card_id INT NOT NULL, manager_id INT NOT NULL, name VARCHAR(60) NOT NULL, director_cni VARCHAR(20) DEFAULT NULL, director_name VARCHAR(50) NOT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_4FBF094FF98F144A (logo_id), INDEX IDX_4FBF094F64D218E (location_id), INDEX IDX_4FBF094F97E3C6E0 (trade_register_id), INDEX IDX_4FBF094FB843A2A (tax_card_id), INDEX IDX_4FBF094F783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, technician_id INT NOT NULL, intervention_id INT NOT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, note INT NOT NULL, comment LONGTEXT DEFAULT NULL, has_comment TINYINT(1) NOT NULL, INDEX IDX_CFBDFA14E6C5D496 (technician_id), INDEX IDX_CFBDFA148EAE3863 (intervention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, intervention_id INT DEFAULT NULL, quote_id INT DEFAULT NULL, evaluation_id INT DEFAULT NULL, note_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, code INT NOT NULL, INDEX IDX_BF5476CA8EAE3863 (intervention_id), INDEX IDX_BF5476CADB805178 (quote_id), INDEX IDX_BF5476CA456C5646 (evaluation_id), INDEX IDX_BF5476CA26ED0855 (note_id), INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_detail ADD CONSTRAINT FK_4B5464AE979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE user_detail ADD CONSTRAINT FK_4B5464AE5C980E05 FOREIGN KEY (criminal_record_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649EE45BDBF FOREIGN KEY (picture_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64964D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D8308E5F FOREIGN KEY (user_detail_id) REFERENCES user_detail (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36108EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF4E6C5D496 FOREIGN KEY (technician_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF48EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF41A8C12F5 FOREIGN KEY (bill_id) REFERENCES bill (id)');
        $this->addSql('ALTER TABLE bill ADD CONSTRAINT FK_7A2119E3584598A3 FOREIGN KEY (operator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE bill ADD CONSTRAINT FK_7A2119E34C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5758EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575E6C5D496 FOREIGN KEY (technician_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5751A8C12F5 FOREIGN KEY (bill_id) REFERENCES bill (id)');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BFEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB584598A3 FOREIGN KEY (operator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FF98F144A FOREIGN KEY (logo_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F97E3C6E0 FOREIGN KEY (trade_register_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FB843A2A FOREIGN KEY (tax_card_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14E6C5D496 FOREIGN KEY (technician_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA148EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CADB805178 FOREIGN KEY (quote_id) REFERENCES quote (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluation (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA26ED0855 FOREIGN KEY (note_id) REFERENCES note (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64964D218E');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F64D218E');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D8308E5F');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF4E6C5D496');
        $this->addSql('ALTER TABLE bill DROP FOREIGN KEY FK_7A2119E3584598A3');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D19EB6921');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DCD53EDB6');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575E6C5D496');
        $this->addSql('ALTER TABLE statut DROP FOREIGN KEY FK_E564F0BFA76ED395');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB584598A3');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB19EB6921');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F783E3463');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14E6C5D496');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D60322AC');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB12469DE2');
        $this->addSql('ALTER TABLE user_detail DROP FOREIGN KEY FK_4B5464AE5C980E05');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649EE45BDBF');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FF98F144A');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F97E3C6E0');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FB843A2A');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1115F0EE5');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB115F0EE5');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CADB805178');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF41A8C12F5');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A5751A8C12F5');
        $this->addSql('ALTER TABLE bill DROP FOREIGN KEY FK_7A2119E34C3A3BB');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA456C5646');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36108EAE3863');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF48EAE3863');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A5758EAE3863');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA148EAE3863');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA8EAE3863');
        $this->addSql('ALTER TABLE user_detail DROP FOREIGN KEY FK_4B5464AE979B1AD6');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA26ED0855');
        $this->addSql('ALTER TABLE statut DROP FOREIGN KEY FK_E564F0BFEF1A9D84');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE user_detail');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE domain');
        $this->addSql('DROP TABLE quote');
        $this->addSql('DROP TABLE bill');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE statut');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE notification');
    }
}
