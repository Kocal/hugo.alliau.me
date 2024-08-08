<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240808054836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        foreach ($this->connection->fetchAllAssociative('SELECT id, address_coordinates FROM place') as $place) {
            $this->connection->update(
                'place', 
                [
                    'address_coordinates' => json_encode(
                        array_map(
                            floatval(...), 
                            json_decode($place['address_coordinates'], flags: JSON_THROW_ON_ERROR)
                        ),
                        flags: JSON_THROW_ON_ERROR
                    )
                ],
                [
                    'id' => $place['id']
                ]
            );
        }
    }
}
