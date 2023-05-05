<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503021343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE team_name_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE team_name (id INT NOT NULL, team_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, language VARCHAR(255) DEFAULT NULL, start_year INT DEFAULT NULL, end_year INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8FC28A7D296CD8AE ON team_name (team_id)');
        $this->addSql('ALTER TABLE team_name ADD CONSTRAINT FK_8FC28A7D296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE team_name_id_seq CASCADE');
        $this->addSql('ALTER TABLE team_name DROP CONSTRAINT FK_8FC28A7D296CD8AE');
        $this->addSql('DROP TABLE team_name');
    }
}
