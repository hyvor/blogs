<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260817062249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Collaborative editing: post_variants.content_version / content_unsaved_version, post_variant_steps table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE post_variants
                ADD COLUMN content_version INTEGER NOT NULL DEFAULT 0,
                ADD COLUMN content_unsaved_version INTEGER NOT NULL DEFAULT 0
            SQL
        );

        $this->addSql(
            <<<SQL
            CREATE TABLE post_variant_steps
            (
                id              SERIAL PRIMARY KEY,
                post_variant_id INTEGER NOT NULL REFERENCES post_variants (id) ON DELETE CASCADE,
                type            VARCHAR(20)  NOT NULL,
                version         INTEGER      NOT NULL,
                client_id       VARCHAR(64)  NOT NULL,
                step            JSONB        NOT NULL,
                created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
                UNIQUE (post_variant_id, type, version)
            )
            SQL
        );
        $this->addSql('CREATE INDEX idx_post_variant_steps_lookup ON post_variant_steps (post_variant_id, type, version)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE post_variant_steps');
        $this->addSql(
            <<<SQL
            ALTER TABLE post_variants
                DROP COLUMN content_version,
                DROP COLUMN content_unsaved_version
            SQL
        );
    }
}
