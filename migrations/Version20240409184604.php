<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240409184604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at and updated_at columns to cv_professional_experience and cv_project tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cv_professional_experience ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE cv_professional_experience ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN cv_professional_experience.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN cv_professional_experience.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE cv_project ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE cv_project ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN cv_project.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN cv_project.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cv_project DROP created_at');
        $this->addSql('ALTER TABLE cv_project DROP updated_at');
        $this->addSql('ALTER TABLE cv_professional_experience DROP created_at');
        $this->addSql('ALTER TABLE cv_professional_experience DROP updated_at');
    }
}
