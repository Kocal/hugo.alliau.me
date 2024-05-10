<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240509230800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename tags to types in place table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place RENAME COLUMN tags TO types');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place RENAME COLUMN types TO tags');
    }
}
