<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240330082641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CV entities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE cv_professional_experience_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cv_project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cv_professional_experience (id INT NOT NULL, company VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, job_name VARCHAR(255) NOT NULL, description TEXT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, badges JSONB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN cv_professional_experience.start_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN cv_professional_experience.end_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE cv_project (id INT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, description TEXT NOT NULL, date DATE NOT NULL, tech_stack JSONB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN cv_project.date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE cv_professional_experience_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cv_project_id_seq CASCADE');
        $this->addSql('DROP TABLE cv_professional_experience');
        $this->addSql('DROP TABLE cv_project');
    }
}
