<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'update url_data varchar(255) columns to text';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE url_data
                ALTER COLUMN url TYPE TEXT,
                ALTER COLUMN final_url TYPE TEXT,
                ALTER COLUMN title TYPE TEXT,
                ALTER COLUMN description TYPE TEXT,
                ALTER COLUMN thumbnail_url TYPE TEXT,
                ALTER COLUMN icon_url TYPE TEXT,
                ALTER COLUMN site TYPE TEXT
            SQL
        );
    }

    public function down(Schema $schema): void
    {
    }
}
