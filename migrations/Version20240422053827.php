<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240422053827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add status column to blog_post table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_post ADD status VARCHAR(255) DEFAULT \'draft\' NOT NULL');
        $this->addSql('UPDATE blog_post SET status = \'published\' WHERE published_at IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_post DROP status');
    }
}
