<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240509225502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove origin from place table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place DROP origin');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place ADD origin VARCHAR(255) NOT NULL');
    }
}
