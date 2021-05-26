<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526013354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE roster_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE roster (id INT NOT NULL, team_id INT DEFAULT NULL, year INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_60B9ADF9296CD8AE ON roster (team_id)');
        $this->addSql('ALTER TABLE roster ADD CONSTRAINT FK_60B9ADF9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE roster_entry ADD roster_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE roster_entry ADD CONSTRAINT FK_339EA82A75404483 FOREIGN KEY (roster_id) REFERENCES roster (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_339EA82A75404483 ON roster_entry (roster_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE roster_entry DROP CONSTRAINT FK_339EA82A75404483');
        $this->addSql('DROP SEQUENCE roster_id_seq CASCADE');
        $this->addSql('DROP TABLE roster');
        $this->addSql('DROP INDEX IDX_339EA82A75404483');
        $this->addSql('ALTER TABLE roster_entry DROP roster_id');
    }
}
