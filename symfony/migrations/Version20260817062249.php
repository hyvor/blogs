<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260817062249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Collaborative editing: post_variants.document_version, post_variant_steps table, post_variants.seo_score';
    }

    public function up(Schema $schema): void
    {

        // post_variant updates
        $this->addSql(
            <<<SQL
            ALTER TABLE post_variants
                ADD COLUMN document_version INTEGER NOT NULL DEFAULT 0,
                ADD COLUMN content_unsaved_version INTEGER NOT NULL DEFAULT 0,
                ADD COLUMN seo_score INTEGER NULL
            SQL
        );

        // content_unsaved is now the sole editable document - backfill it from the published
        // content for any variant that predates this column (or was never edited since)
        $this->addSql(
            <<<SQL
            UPDATE post_variants SET content_unsaved = content WHERE content_unsaved IS NULL
            SQL
        );

        $this->addSql(
            <<<SQL
            CREATE TABLE post_variant_steps
            (
                id              SERIAL PRIMARY KEY,
                post_variant_id INTEGER NOT NULL REFERENCES post_variants (id) ON DELETE CASCADE,
                version         INTEGER      NOT NULL,
                client_id       VARCHAR(64)  NOT NULL,
                step            JSONB        NOT NULL,
                created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
                UNIQUE (post_variant_id, version)
            )
            SQL
        );
        $this->addSql('CREATE INDEX idx_post_variant_steps_lookup ON post_variant_steps (post_variant_id, version)');

        // user color
        $this->addSql('ALTER TABLE users ADD COLUMN cursor_color VARCHAR(30) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
    }
}
