<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260516095559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projects (id UUID NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5C93B3A47E3C61F9 ON projects (owner_id)');
        $this->addSql('CREATE TABLE project_members (project_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (project_id, user_id))');
        $this->addSql('CREATE INDEX IDX_D3BEDE9A166D1F9C ON project_members (project_id)');
        $this->addSql('CREATE INDEX IDX_D3BEDE9AA76ED395 ON project_members (user_id)');
        $this->addSql('CREATE TABLE tasks (id UUID NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, project_id UUID NOT NULL, assignee_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_50586597166D1F9C ON tasks (project_id)');
        $this->addSql('CREATE INDEX IDX_5058659759EC7D60 ON tasks (assignee_id)');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(100) NOT NULL, roles JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A47E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE project_members ADD CONSTRAINT FK_D3BEDE9A166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_members ADD CONSTRAINT FK_D3BEDE9AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_5058659759EC7D60 FOREIGN KEY (assignee_id) REFERENCES users (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projects DROP CONSTRAINT FK_5C93B3A47E3C61F9');
        $this->addSql('ALTER TABLE project_members DROP CONSTRAINT FK_D3BEDE9A166D1F9C');
        $this->addSql('ALTER TABLE project_members DROP CONSTRAINT FK_D3BEDE9AA76ED395');
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT FK_50586597166D1F9C');
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT FK_5058659759EC7D60');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE project_members');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('DROP TABLE users');
    }
}
