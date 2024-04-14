<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240414040858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE headshot ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE headshot ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE roster ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE roster ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE team_league ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE team_league ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE team_name ADD type VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE team_name ADD first_season VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE team_name ADD last_season VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE team_name ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE team_name ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE team_name DROP start_year');
        $this->addSql('ALTER TABLE team_name DROP end_year');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE document DROP created_at');
        $this->addSql('ALTER TABLE document DROP updated_at');
        $this->addSql('ALTER TABLE roster DROP created_at');
        $this->addSql('ALTER TABLE roster DROP updated_at');
        $this->addSql('ALTER TABLE team_name ADD start_year INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team_name ADD end_year INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team_name DROP type');
        $this->addSql('ALTER TABLE team_name DROP first_season');
        $this->addSql('ALTER TABLE team_name DROP last_season');
        $this->addSql('ALTER TABLE team_name DROP created_at');
        $this->addSql('ALTER TABLE team_name DROP updated_at');
        $this->addSql('ALTER TABLE team DROP created_at');
        $this->addSql('ALTER TABLE team DROP updated_at');
        $this->addSql('ALTER TABLE team_league DROP created_at');
        $this->addSql('ALTER TABLE team_league DROP updated_at');
        $this->addSql('ALTER TABLE headshot DROP created_at');
        $this->addSql('ALTER TABLE headshot DROP updated_at');
    }
}
