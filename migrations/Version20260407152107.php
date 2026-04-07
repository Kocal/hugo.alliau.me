<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260407152107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable('user2')) {
            $this->addSql(<<<'SQL'
            CREATE TABLE "user2" (
              id UUID NOT NULL,
              username VARCHAR(30) NOT NULL,
              roles JSONB NOT NULL,
              password VARCHAR(255) NOT NULL,
              api_token VARCHAR(255) NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        }
        if ($schema->hasTable('user2')) {
            $table = $schema->getTable('user2');
            if (!$table->hasIndex('UNIQ_IDENTIFIER_USERNAME')) {
                $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON "user2" (username)
        SQL);
            }
        }
        if ($schema->hasTable('user')) {
            $table = $schema->getTable('user');
            if ($table->hasColumn('password')) {
                $this->addSql(<<<'SQL'
            ALTER TABLE "user" RENAME COLUMN password TO api_token
        SQL);
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable('user2')) {
            $this->addSql(<<<'SQL'
            DROP TABLE "user2"
        SQL);
        }
        if ($schema->hasTable('user')) {
            $table = $schema->getTable('user');
            if ($table->hasColumn('api_token')) {
                $this->addSql(<<<'SQL'
            ALTER TABLE "user" RENAME COLUMN api_token TO password
        SQL);
            }
        }
    }
}
