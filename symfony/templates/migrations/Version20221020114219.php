<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221020114219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE facility_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_facility_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE facility (id INT NOT NULL, name VARCHAR(1024) NOT NULL, phone VARCHAR(16) DEFAULT NULL, address TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_facility (id INT NOT NULL, user_id INT DEFAULT NULL, facility_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F0D470B2A76ED395 ON user_facility (user_id)');
        $this->addSql('CREATE INDEX IDX_F0D470B2A7014910 ON user_facility (facility_id)');
        $this->addSql('ALTER TABLE user_facility ADD CONSTRAINT FK_F0D470B2A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_facility ADD CONSTRAINT FK_F0D470B2A7014910 FOREIGN KEY (facility_id) REFERENCES facility (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE facility_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_facility_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_facility DROP CONSTRAINT FK_F0D470B2A76ED395');
        $this->addSql('ALTER TABLE user_facility DROP CONSTRAINT FK_F0D470B2A7014910');
        $this->addSql('DROP TABLE facility');
        $this->addSql('DROP TABLE user_facility');
    }
}
