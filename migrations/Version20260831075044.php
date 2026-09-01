<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260831075044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Recipes: add nullable source_label/source_url columns for source attribution';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recipe ADD source_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE recipe ADD source_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recipe DROP source_label');
        $this->addSql('ALTER TABLE recipe DROP source_url');
    }
}
