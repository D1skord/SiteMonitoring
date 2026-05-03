<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add response time to status logs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE status_log ADD response_time_ms INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE status_log DROP response_time_ms');
    }
}
