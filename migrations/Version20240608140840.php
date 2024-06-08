<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240608140840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__journal AS SELECT id, first_day, last_day, chart_of_account FROM journal');
        $this->addSql('DROP TABLE journal');
        $this->addSql('CREATE TABLE journal (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, organization_id INTEGER DEFAULT NULL, first_day DATE NOT NULL, last_day DATE NOT NULL, chart_of_account VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_C1A7E74D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO journal (id, first_day, last_day, chart_of_account) SELECT id, first_day, last_day, chart_of_account FROM __temp__journal');
        $this->addSql('DROP TABLE __temp__journal');
        $this->addSql('CREATE INDEX IDX_C1A7E74D32C8A3DE ON journal (organization_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__journal AS SELECT id, first_day, last_day, chart_of_account FROM journal');
        $this->addSql('DROP TABLE journal');
        $this->addSql('CREATE TABLE journal (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_day DATE NOT NULL, last_day DATE NOT NULL, chart_of_account VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO journal (id, first_day, last_day, chart_of_account) SELECT id, first_day, last_day, chart_of_account FROM __temp__journal');
        $this->addSql('DROP TABLE __temp__journal');
    }
}
