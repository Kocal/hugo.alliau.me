<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251020063916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add visible field to cv_project';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cv_project ADD visible BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cv_project DROP visible');
    }
}
