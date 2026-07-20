<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260719120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Hyvor Post integration: integrations_hyvor_post table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            CREATE TABLE integrations_hyvor_post (
                id serial PRIMARY KEY,
                created_at timestamptz NOT NULL DEFAULT NOW(),
                updated_at timestamptz NOT NULL DEFAULT NOW(),
                blog_id BIGINT NOT NULL REFERENCES blogs(id) ON DELETE CASCADE UNIQUE,
                newsletter_id BIGINT NOT NULL,
                embed_code TEXT,
                created_by_blogs BOOLEAN NOT NULL DEFAULT true
            );
            SQL
        );
        $this->addSql('CREATE UNIQUE INDEX idx_integrations_hyvor_post_newsletter_id ON integrations_hyvor_post(newsletter_id)');
    }

    public function down(Schema $schema): void {}
}
