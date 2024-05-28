<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240528174453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, journal_line_item_id INTEGER DEFAULT NULL, account_number VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_7D3656A474076076 FOREIGN KEY (journal_line_item_id) REFERENCES journal_line_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7D3656A474076076 ON account (journal_line_item_id)');
        $this->addSql('CREATE TABLE journal (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_day DATE NOT NULL, last_day DATE NOT NULL, chart_of_account VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE journal_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, journal_id INTEGER NOT NULL, journal_line_item_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, CONSTRAINT FK_C8FAAE5A478E8802 FOREIGN KEY (journal_id) REFERENCES journal (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C8FAAE5A74076076 FOREIGN KEY (journal_line_item_id) REFERENCES journal_line_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C8FAAE5A478E8802 ON journal_entry (journal_id)');
        $this->addSql('CREATE INDEX IDX_C8FAAE5A74076076 ON journal_entry (journal_line_item_id)');
        $this->addSql('CREATE TABLE journal_line_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, debit_amount DOUBLE PRECISION NOT NULL, credit_amount DOUBLE PRECISION NOT NULL)');
        $this->addSql('CREATE TABLE organization (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE organization_user (organization_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(organization_id, user_id), CONSTRAINT FK_B49AE8D432C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B49AE8D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B49AE8D432C8A3DE ON organization_user (organization_id)');
        $this->addSql('CREATE INDEX IDX_B49AE8D4A76ED395 ON organization_user (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE journal');
        $this->addSql('DROP TABLE journal_entry');
        $this->addSql('DROP TABLE journal_line_item');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE organization_user');
    }
}
