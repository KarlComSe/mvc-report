<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240608115252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE organization_journal');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE organization_journal (organization_id INTEGER NOT NULL, journal_id INTEGER NOT NULL, PRIMARY KEY(organization_id, journal_id), CONSTRAINT FK_C756F0E32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C756F0E478E8802 FOREIGN KEY (journal_id) REFERENCES journal (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C756F0E478E8802 ON organization_journal (journal_id)');
        $this->addSql('CREATE INDEX IDX_C756F0E32C8A3DE ON organization_journal (organization_id)');
    }
}
