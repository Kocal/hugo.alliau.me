<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240422054729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on blog_post status and published_at columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_BA5AE01D7B00651CE0D4FDE1 ON blog_post (status, published_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_BA5AE01D7B00651CE0D4FDE1');
    }
}
