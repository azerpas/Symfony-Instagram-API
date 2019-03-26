<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190321151026 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_account ADD accounts INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_account ALTER username SET NOT NULL');
        $this->addSql('ALTER TABLE user_account ADD CONSTRAINT FK_253B48AECAC89EAC FOREIGN KEY (accounts) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_253B48AECAC89EAC ON user_account (accounts)');
        $this->addSql('ALTER TABLE account ADD user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE account ALTER email SET NOT NULL');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A49D86650F FOREIGN KEY (user_id_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7D3656A49D86650F ON account (user_id_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_account DROP CONSTRAINT FK_253B48AECAC89EAC');
        $this->addSql('DROP INDEX IDX_253B48AECAC89EAC');
        $this->addSql('ALTER TABLE user_account DROP accounts');
        $this->addSql('ALTER TABLE user_account ALTER username DROP NOT NULL');
        $this->addSql('ALTER TABLE account DROP CONSTRAINT FK_7D3656A49D86650F');
        $this->addSql('DROP INDEX IDX_7D3656A49D86650F');
        $this->addSql('ALTER TABLE account DROP user_id_id');
        $this->addSql('ALTER TABLE account ALTER email DROP NOT NULL');
    }
}
