<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250304162257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the queue_messages table (compatible with SQLite & MySQL).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
        CREATE TABLE queue_messages (
            id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
            tries INT DEFAULT 0 NOT NULL,
            last_try DATETIME NOT NULL,
            status INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            payload JSON NOT NULL -- (DC2Type:json) Native JSON type for MySQL
        )
    ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE queue_messages');
    }
}
