<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240529084153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE test_org (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, org_name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE test_org_test_user (test_org_id INTEGER NOT NULL, test_user_id INTEGER NOT NULL, PRIMARY KEY(test_org_id, test_user_id), CONSTRAINT FK_97605698635A7B35 FOREIGN KEY (test_org_id) REFERENCES test_org (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_976056987B2F075D FOREIGN KEY (test_user_id) REFERENCES test_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_97605698635A7B35 ON test_org_test_user (test_org_id)');
        $this->addSql('CREATE INDEX IDX_976056987B2F075D ON test_org_test_user (test_user_id)');
        $this->addSql('CREATE TABLE test_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE test_org');
        $this->addSql('DROP TABLE test_org_test_user');
        $this->addSql('DROP TABLE test_user');
    }
}
