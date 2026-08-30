<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260830143757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Blog: add locale column (default en, backfill known FR posts) and unique index on slug';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_post ADD locale VARCHAR(5) DEFAULT \'en\' NOT NULL');
        $this->addSql("UPDATE blog_post SET locale = 'fr' WHERE slug IN ('2021-04-26-migration-de-notre-stack-de-developpement-vers-docker', 'une-meilleure-architecture-pour-vous-twig-components-de-symfony-ux')");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BA5AE01D989D9B62 ON blog_post (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_BA5AE01D989D9B62');
        $this->addSql('ALTER TABLE blog_post DROP locale');
    }
}
