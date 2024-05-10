<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240509225403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make address_zipcode nullable in place entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place ALTER address_zipcode DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place ALTER address_zipcode SET NOT NULL');
    }
}
