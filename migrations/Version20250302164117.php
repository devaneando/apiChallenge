<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250302164117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the users table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
            email VARCHAR(180) NOT NULL UNIQUE,
            roles JSON NOT NULL, -- (DC2Type:json)
            password VARCHAR(255) NOT NULL
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
