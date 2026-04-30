<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240829185809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE payment_info_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE payment_info (id INT NOT NULL, cost DOUBLE PRECISION DEFAULT NULL, payment_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN payment_info.cost IS \'Стоимость поддержки сайта\'');
        $this->addSql('COMMENT ON COLUMN payment_info.payment_date IS \'Дата оплаты поддержки\'');
        $this->addSql('ALTER TABLE site ADD payment_info_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E444C2CF12 FOREIGN KEY (payment_info_id) REFERENCES payment_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_694309E444C2CF12 ON site (payment_info_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE site DROP CONSTRAINT FK_694309E444C2CF12');
        $this->addSql('DROP SEQUENCE payment_info_id_seq CASCADE');
        $this->addSql('DROP TABLE payment_info');
        $this->addSql('DROP INDEX UNIQ_694309E444C2CF12');
        $this->addSql('ALTER TABLE site DROP payment_info_id');
    }
}
