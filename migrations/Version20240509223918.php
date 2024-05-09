<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240509223918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add icon mask uri to place entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place ADD icon_mask_uri VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place DROP icon_mask_uri');
    }
}
