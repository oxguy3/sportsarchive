<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230610180043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE team_league_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE team_league (id INT NOT NULL, team_id INT DEFAULT NULL, league_id INT DEFAULT NULL, first_season VARCHAR(255) DEFAULT NULL, last_season VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_48AF84C1296CD8AE ON team_league (team_id)');
        $this->addSql('CREATE INDEX IDX_48AF84C158AFC4DE ON team_league (league_id)');
        $this->addSql('ALTER TABLE team_league ADD CONSTRAINT FK_48AF84C1296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team_league ADD CONSTRAINT FK_48AF84C158AFC4DE FOREIGN KEY (league_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE team_league_id_seq CASCADE');
        $this->addSql('ALTER TABLE team_league DROP CONSTRAINT FK_48AF84C1296CD8AE');
        $this->addSql('ALTER TABLE team_league DROP CONSTRAINT FK_48AF84C158AFC4DE');
        $this->addSql('DROP TABLE team_league');
    }
}
