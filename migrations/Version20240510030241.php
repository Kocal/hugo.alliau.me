<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240510030241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove to_try from place as it is not useful';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place DROP to_try');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place ADD to_try BOOLEAN NOT NULL');
    }
}
