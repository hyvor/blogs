<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260131164856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial state before symfony migration';
    }

    public function up(Schema $schema): void
    {
        $sql = (string)file_get_contents(__DIR__ . '/Version20260131164856.sql');
        $this->connection->executeStatement($sql);
    }

    public function down(Schema $schema): void
    {
        //
    }
}
