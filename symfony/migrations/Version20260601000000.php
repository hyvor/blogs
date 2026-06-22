<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add try_count and last_try_at to webhook_deliveries';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE webhook_deliveries ADD COLUMN try_count INTEGER NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE webhook_deliveries ADD COLUMN last_try_at TIMESTAMPTZ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE webhook_deliveries DROP COLUMN try_count');
        $this->addSql('ALTER TABLE webhook_deliveries DROP COLUMN last_try_at');
    }
}
