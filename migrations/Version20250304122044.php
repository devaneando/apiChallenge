<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250304122044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the stock_request_history table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE stock_request_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                provider VARCHAR(60) NOT NULL,
                user_id INTEGER NOT NULL,
                symbol VARCHAR(60) NOT NULL,
                name VARCHAR(120) DEFAULT NULL,
                open DOUBLE PRECISION DEFAULT NULL,
                high DOUBLE PRECISION DEFAULT NULL,
                low DOUBLE PRECISION DEFAULT NULL,
                close DOUBLE PRECISION DEFAULT NULL,
                date DATETIME NOT NULL,
                CONSTRAINT FK_EC3779BDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ');
        $this->addSql('CREATE INDEX IDX_EC3779BDA76ED395 ON stock_request_history (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE stock_request_history');
    }
}
