<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tls_certificates table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            CREATE TABLE tls_certificates (
                id serial PRIMARY KEY,
                created_at timestamptz NOT NULL,
                updated_at timestamptz NOT NULL,
                blog_id BIGINT NOT NULL,
                status VARCHAR(255) NOT NULL DEFAULT 'pending',
                private_key TEXT,
                certificate TEXT,
                valid_from timestamptz,
                valid_to timestamptz
            );
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tls_certificates');
    }
}
