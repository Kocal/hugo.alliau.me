<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240509182039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create place table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE place_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE place (id INT NOT NULL, tags JSONB NOT NULL, to_try BOOLEAN NOT NULL, google_maps_url VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, address_name VARCHAR(100) NOT NULL, address_city VARCHAR(100) NOT NULL, address_county VARCHAR(100) NOT NULL, address_administrative VARCHAR(100) NOT NULL, address_country VARCHAR(40) NOT NULL, address_zipcode VARCHAR(15) NOT NULL, address_coordinates JSONB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN place.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN place.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE place_id_seq CASCADE');
        $this->addSql('DROP TABLE place');
    }
}
