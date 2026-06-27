<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Self-hosting changes: OIDC tables, webhook_deliveries, user role changes, and API keys scopes';
    }

    public function up(Schema $schema): void
    {

        // OIDC
        $this->addSql(<<<SQL
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
        $this->addSql(<<<SQL
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

        // Webhooks
        $this->addSql('ALTER TABLE webhook_deliveries ADD COLUMN try_count INTEGER NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE webhook_deliveries ADD COLUMN last_try_at TIMESTAMPTZ');

        // API keys scopes
        $this->addSql("ALTER TABLE api_keys ADD scopes JSON NOT NULL DEFAULT '[]'");

        // User role
        // change 'owner' to 'admin'
        $this->addSql("UPDATE users SET role = 'admin' WHERE role = 'owner'");
    }

    public function down(Schema $schema): void
    {}
}
