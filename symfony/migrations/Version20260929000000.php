<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260929000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move manually added Hyvor Talk/Post embed codes from blogs.meta into the integration embed_code';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            UPDATE inter_hyvor_talk_websites
            SET embed_code = blogs.meta->>'comments_code', updated_at = NOW()
            FROM blogs
            WHERE inter_hyvor_talk_websites.blog_id = blogs.id
                AND blogs.meta->>'comments_code' IS NOT NULL
            SQL
        );

        $this->addSql(
            <<<SQL
            UPDATE integrations_hyvor_post
            SET embed_code = blogs.meta->>'newsletter_code', updated_at = NOW()
            FROM blogs
            WHERE integrations_hyvor_post.blog_id = blogs.id
                AND blogs.meta->>'newsletter_code' IS NOT NULL
            SQL
        );

        $this->addSql(
            <<<SQL
            UPDATE blogs
            SET meta = jsonb_set(meta, '{comments_code}', 'null'::jsonb, false)
            FROM inter_hyvor_talk_websites
            WHERE inter_hyvor_talk_websites.blog_id = blogs.id
                AND blogs.meta->>'comments_code' IS NOT NULL
            SQL
        );

        $this->addSql(
            <<<SQL
            UPDATE blogs
            SET meta = jsonb_set(meta, '{newsletter_code}', 'null'::jsonb, false)
            FROM integrations_hyvor_post
            WHERE integrations_hyvor_post.blog_id = blogs.id
                AND blogs.meta->>'newsletter_code' IS NOT NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
    }
}
