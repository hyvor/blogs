<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Self-hosting changes: OIDC tables, webhook_deliveries, user role changes, custom_domains, blogs.custom_domain_id, and API keys scopes';
    }

    public function up(Schema $schema): void
    {

        // OIDC ====
        $this->addSql(
            <<<SQL
            CREATE TABLE oidc_users
            (
              id          SERIAL PRIMARY KEY,
              created_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
              updated_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
              iss         text NOT NULL,
              sub         text NOT NULL,
              email       text NOT NULL,
              name        text NOT NULL,
              picture_url text,
              website_url text,
              UNIQUE (iss, sub)
            )
        SQL
        );
        $this->addSql('CREATE INDEX idx_oidc_users_email ON oidc_users (email)');
        $this->addSql(
            <<<SQL
            CREATE TABLE oidc_sessions
            (
                sess_id       VARCHAR(128) NOT NULL PRIMARY KEY,
                sess_data     BYTEA        NOT NULL,
                sess_lifetime INTEGER      NOT NULL,
                sess_time     INTEGER      NOT NULL
            )
        SQL
        );
        $this->addSql('CREATE INDEX idx_oidc_sessions_sess_lifetime ON oidc_sessions (sess_lifetime)');

        // Webhooks ====
        $this->addSql('ALTER TABLE webhook_deliveries ADD COLUMN try_count INTEGER NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE webhook_deliveries ADD COLUMN last_try_at TIMESTAMPTZ');

        // API keys scopes ====
        $this->addSql("ALTER TABLE api_keys ADD scopes JSON NOT NULL DEFAULT '[]'");

        // User role ====
        // change 'owner' to 'admin'
        $this->addSql("UPDATE users SET role = 'admin' WHERE role = 'owner'");


        // Custom Domain ====
        $this->addSql(
            <<<SQL
                CREATE TYPE custom_domain_status AS ENUM ('pending', 'active');
            SQL
        );
        $this->addSql(
            <<<SQL
            CREATE TABLE custom_domains (
                id serial PRIMARY KEY,
                created_at timestamptz NOT NULL,
                updated_at timestamptz NOT NULL,
                blog_id BIGINT NOT NULL REFERENCES blogs(id) ON DELETE CASCADE UNIQUE,
                status custom_domain_status NOT NULL DEFAULT 'pending',
                domain TEXT NOT NULL UNIQUE,
                private_key_encrypted TEXT,
                certificate TEXT,
                valid_from timestamptz,
                valid_to timestamptz
            );
            SQL
        );
        $this->addSql("CREATE INDEX idx_custom_domains_blog_id ON custom_domains(blog_id)");

        // Blogs: custom_domain_id ====
        $this->addSql('ALTER TABLE blogs ADD COLUMN custom_domain_id BIGINT REFERENCES custom_domains(id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void {}
}
