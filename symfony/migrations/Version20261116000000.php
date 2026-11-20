<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Hosting is no longer an explicit setting (blogs.hosting_at). The URL of the blog is derived from:
 * 1. blogs.hosting_url, if set (where the blog is finally served, ex: a reverse proxy or a headless frontend)
 * 2. blogs.custom_domain_id, if a custom domain is connected
 * 3. the subdomain, otherwise
 *
 * Hosting changes are applied right away, so hosting_changes (and the hosting history) is removed.
 */
final class Version20261116000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'drop blogs.hosting_at and hosting_changes; migrate self hosting to blogs.hosting_url';
    }

    public function up(Schema $schema): void
    {
        // hosting_url was only meaningful for self hosting before
        $this->addSql("UPDATE blogs SET hosting_url = NULL WHERE hosting_at <> 'self' AND hosting_url IS NOT NULL");

        // self-hosted blogs keep their URLs via the hosting URL
        $this->addSql("UPDATE blogs SET hosting_url = NULLIF(RTRIM(TRIM(hosting_url), '/'), '') WHERE hosting_at = 'self'");

        // the custom domain relation now decides whether the blog is served at a custom domain,
        // so only domain-hosted blogs keep it (also, the legacy hosting_domain, used by hosting:migrate-caddy-tls-certificates)
        $this->addSql("UPDATE blogs SET custom_domain_id = NULL WHERE hosting_at <> 'domain' AND custom_domain_id IS NOT NULL");
        $this->addSql("UPDATE blogs SET hosting_domain = NULL WHERE hosting_at <> 'domain' AND hosting_domain IS NOT NULL");

        $this->addSql('DROP TABLE hosting_changes');
        $this->addSql('DROP TYPE hosting_change_status');

        $this->addSql('ALTER TABLE blogs DROP COLUMN hosting_at');
        $this->addSql('DROP TYPE blog_hosting_at');
    }

    public function down(Schema $schema): void
    {
    }
}
