<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240510024357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add formatted address to place, remove county, administrative and zipcode from address as they are not useful';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place ADD address_formatted_address VARCHAR(255) NULL');
        $this->addSql('ALTER TABLE place DROP address_county');
        $this->addSql('ALTER TABLE place DROP address_administrative');
        $this->addSql('ALTER TABLE place DROP address_zipcode');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE place ADD address_county VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE place ADD address_administrative VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE place ADD address_zipcode VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE place DROP address_formatted_address');
    }
}
