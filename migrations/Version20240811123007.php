<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240811123007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE expire_date_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE expire_date (id INT NOT NULL, domain TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ssl TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE site ADD expire_date_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E47B548712 FOREIGN KEY (expire_date_id) REFERENCES expire_date (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_694309E47B548712 ON site (expire_date_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE site DROP CONSTRAINT FK_694309E47B548712');
        $this->addSql('DROP SEQUENCE expire_date_id_seq CASCADE');
        $this->addSql('DROP TABLE expire_date');
        $this->addSql('DROP INDEX UNIQ_694309E47B548712');
        $this->addSql('ALTER TABLE site DROP expire_date_id');
    }
}
