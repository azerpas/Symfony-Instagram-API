<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190318203014 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE user_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_account (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles TEXT NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, back_up VARCHAR(255) DEFAULT NULL, accounts TEXT DEFAULT NULL, account INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_253B48AEE7927C74 ON user_account (email)');
        $this->addSql('COMMENT ON COLUMN user_account.roles IS \'(DC2Type:json)\'');
        $this->addSql('COMMENT ON COLUMN user_account.accounts IS \'(DC2Type:array)\'');
        $this->addSql('DROP TABLE "user"');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE user_account_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles TEXT NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, back_up VARCHAR(255) DEFAULT NULL, accounts TEXT DEFAULT NULL, account INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649e7927c74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".roles IS \'(DC2Type:json)\'');
        $this->addSql('COMMENT ON COLUMN "user".accounts IS \'(DC2Type:array)\'');
        $this->addSql('DROP TABLE user_account');
    }
}
