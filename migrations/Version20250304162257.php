<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250304162257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the queue_message table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE queue_message (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                tries INTEGER DEFAULT 0 NOT NULL,
                last_try DATETIME NOT NULL,
                status INTEGER NOT NULL,
                email VARCHAR(255) NOT NULL,
                payload CLOB NOT NULL --(DC2Type:json)
            )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE queue_message');
    }
}
