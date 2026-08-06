<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Self-hosting changes: OIDC tables, webhook_deliveries, user role changes, custom_domains, blogs.custom_domain_id, API keys scopes, and blog deletion policy (deleted_at + cascading FKs)';
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

        // Hosting changes ====
        $this->addSql(
            <<<SQL
                CREATE TYPE hosting_change_status AS ENUM ('changing', 'success', 'failed');
            SQL
        );
        $this->addSql(
            <<<SQL
            CREATE TABLE hosting_changes (
                id serial PRIMARY KEY,
                created_at timestamptz NOT NULL,
                updated_at timestamptz NOT NULL,
                blog_id BIGINT NOT NULL REFERENCES blogs(id) ON DELETE CASCADE,
                from_at blog_hosting_at NOT NULL,
                from_subdomain TEXT,
                from_domain TEXT,
                from_url TEXT,
                to_at blog_hosting_at NOT NULL,
                to_subdomain TEXT,
                to_domain TEXT,
                to_url TEXT,
                status hosting_change_status NOT NULL DEFAULT 'changing',
                error_message TEXT,
                retry_count INTEGER NOT NULL DEFAULT 0
            );
            SQL
        );
        $this->addSql("CREATE INDEX idx_hosting_changes_blog_id ON hosting_changes(blog_id)");
        // only one change can be in progress for a blog at a time
        $this->addSql(
            "CREATE UNIQUE INDEX idx_hosting_changes_blog_pending ON hosting_changes(blog_id) WHERE status = 'changing'"
        );

        // Blog deletion policy (soft delete now, hard delete after 30 days) ====
        // see https://github.com/hyvor/core/issues/561
        $this->addSql('ALTER TABLE blogs ADD COLUMN deleted_at TIMESTAMPTZ');
        $this->addSql('CREATE INDEX idx_blogs_deleted_at ON blogs(deleted_at) WHERE deleted_at IS NOT NULL');

        // direct blog_id foreign keys that were missing DB-level cascading delete
        $this->addSql('ALTER TABLE media ADD CONSTRAINT media_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE navigations ADD CONSTRAINT navigations_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE api_keys ADD CONSTRAINT api_keys_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_variants ADD CONSTRAINT blog_variants_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT posts_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE redirects ADD CONSTRAINT redirects_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE auto_translations ADD CONSTRAINT auto_translations_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gpt_prompts ADD CONSTRAINT gpt_prompts_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inter_hyvor_talk_websites ADD CONSTRAINT inter_hyvor_talk_websites_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE link_analyzer_checks ADD CONSTRAINT link_analyzer_checks_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exports ADD CONSTRAINT exports_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE languages ADD CONSTRAINT languages_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE imports ADD CONSTRAINT imports_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE routes ADD CONSTRAINT routes_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE link_analyzer_links ADD CONSTRAINT link_analyzer_links_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tags ADD CONSTRAINT tags_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE theme_files ADD CONSTRAINT theme_files_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT users_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webhooks ADD CONSTRAINT webhooks_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE');

        // second-level foreign keys so that the full blog subtree cascades (e.g. blog -> post -> post_author)
        $this->addSql('ALTER TABLE post_tag ADD CONSTRAINT post_tag_post_id_foreign FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_tag ADD CONSTRAINT post_tag_tag_id_foreign FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_author ADD CONSTRAINT post_author_post_id_foreign FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_author ADD CONSTRAINT post_author_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_variants ADD CONSTRAINT post_variants_post_id_foreign FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_variants ADD CONSTRAINT post_variants_language_id_foreign FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_variant_histories ADD CONSTRAINT post_variant_histories_post_variant_id_foreign FOREIGN KEY (post_variant_id) REFERENCES post_variants(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT media_post_id_foreign FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tag_variants ADD CONSTRAINT tag_variants_tag_id_foreign FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_variants ADD CONSTRAINT tag_variants_language_id_foreign FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE navigation_variants ADD CONSTRAINT navigation_variants_navigation_id_foreign FOREIGN KEY (navigation_id) REFERENCES navigations(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE navigation_variants ADD CONSTRAINT navigation_variants_language_id_foreign FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_variants ADD CONSTRAINT user_variants_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_variants ADD CONSTRAINT user_variants_language_id_foreign FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webhook_deliveries ADD CONSTRAINT webhook_deliveries_webhook_id_foreign FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE link_analyzer_links ADD CONSTRAINT link_analyzer_links_post_variant_id_foreign FOREIGN KEY (post_variant_id) REFERENCES post_variants(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_variants ADD CONSTRAINT blog_variants_language_id_foreign FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE');

        // cleanup =============
        $this->addSql('ALTER TABLE blogs DROP COLUMN trial_ends_at');

        // cache ==============
        // https://supun.io/symfony-database-cache
        $this->addSql(<<<SQL
        CREATE TABLE cache_items (
            item_id varchar(255) NOT NULL PRIMARY KEY,
            item_data bytea NOT NULL,
            item_lifetime int4,
            item_time int4 NOT NULL
        );
        SQL);

        // for zenstruct/messenger-monitor-bundle
        $this->addSql(
            <<<SQL
            CREATE TABLE messenger_processed_messages (
                id SERIAL PRIMARY KEY,
                run_id INT NOT NULL,
                attempt INT NOT NULL DEFAULT 1,
                message_type VARCHAR(255) NOT NULL,
                description TEXT,
                dispatched_at TIMESTAMP WITH TIME ZONE NOT NULL,
                received_at TIMESTAMP WITH TIME ZONE NOT NULL,
                finished_at TIMESTAMP WITH TIME ZONE NOT NULL,
                memory_usage INT NOT NULL,
                transport VARCHAR(100) NOT NULL,
                tags TEXT,
                wait_time INT NOT NULL,
                handle_time INT NOT NULL,
                failure_type VARCHAR(255),
                failure_message TEXT,
                results JSONB
            );
            SQL
        );
    }

    public function down(Schema $schema): void {}
}
