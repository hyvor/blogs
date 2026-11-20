<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261116000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'drop hosting_changes from_hosting_url/to_hosting_url (self hosting is replaced by blogs.hosting_url)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE hosting_changes
                DROP COLUMN from_hosting_url,
                DROP COLUMN to_hosting_url
            SQL
        );
    }

    public function down(Schema $schema): void
    {
    }
}
