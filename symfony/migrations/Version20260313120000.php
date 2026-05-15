<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create custom_domain_setups table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
                CREATE TYPE custom_domain_setup_status AS ENUM ('pending', 'active', 'failed');
            SQL
        );

        $this->addSql(
            <<<SQL
            CREATE TABLE custom_domain_setups (
                id serial PRIMARY KEY,
                created_at timestamptz NOT NULL,
                updated_at timestamptz NOT NULL,
                blog_id BIGINT NOT NULL,
                status custom_domain_setup_status NOT NULL DEFAULT 'pending',
                domain TEXT NOT NULL,
                private_key_encrypted TEXT,
                certificate TEXT,
                valid_from timestamptz,
                valid_to timestamptz
            );
            SQL
        );

        // 1 active setup per blog
        $this->addSql(
            <<<SQL
            CREATE UNIQUE INDEX unique_active_custom_domain_setups ON custom_domain_setups(blog_id, domain) WHERE status = 'active';
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE custom_domain_setups');
        $this->addSql('DROP TYPE custom_domain_setup_status');
        $this->addSql('DROP INDEX unique_active_custom_domain_setups');
    }
}
