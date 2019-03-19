<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190319091907 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_account ADD account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_account DROP accounts');
        $this->addSql('ALTER TABLE user_account ADD CONSTRAINT FK_253B48AE9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_253B48AE9B6B5FBA ON user_account (account_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_account DROP CONSTRAINT FK_253B48AE9B6B5FBA');
        $this->addSql('DROP INDEX IDX_253B48AE9B6B5FBA');
        $this->addSql('ALTER TABLE user_account ADD accounts TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_account DROP account_id');
        $this->addSql('COMMENT ON COLUMN user_account.accounts IS \'(DC2Type:array)\'');
    }
}
