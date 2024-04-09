<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240409184636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make created_at and updated_at columns in cv_professional_experience and cv_project tables not nullable and set default values to NOW().';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE cv_professional_experience SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('UPDATE cv_project SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('ALTER TABLE cv_professional_experience ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE cv_professional_experience ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE cv_project ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE cv_project ALTER updated_at SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cv_professional_experience ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE cv_professional_experience ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE cv_project ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE cv_project ALTER updated_at DROP NOT NULL');
    }
}
