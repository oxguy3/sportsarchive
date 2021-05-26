<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526213525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE roster_entry_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE headshot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE headshot (id INT NOT NULL, roster_id INT DEFAULT NULL, person_name VARCHAR(255) NOT NULL, jersey_number VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9AFB5DF075404483 ON headshot (roster_id)');
        $this->addSql('ALTER TABLE headshot ADD CONSTRAINT FK_9AFB5DF075404483 FOREIGN KEY (roster_id) REFERENCES roster (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE roster_entry');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE headshot_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE roster_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE roster_entry (id INT NOT NULL, roster_id INT DEFAULT NULL, person_name VARCHAR(255) NOT NULL, jersey_number VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_339ea82a75404483 ON roster_entry (roster_id)');
        $this->addSql('ALTER TABLE roster_entry ADD CONSTRAINT fk_339ea82a75404483 FOREIGN KEY (roster_id) REFERENCES roster (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE headshot');
    }
}
