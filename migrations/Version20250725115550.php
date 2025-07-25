<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725115550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create blog_categories table for category management';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_categories (id UUID NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, description TEXT DEFAULT NULL, parent_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_categories_slug ON blog_categories (slug)');
        $this->addSql('CREATE INDEX idx_categories_parent_id ON blog_categories (parent_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_categories_slug ON blog_categories (slug)');
        $this->addSql('CREATE UNIQUE INDEX uniq_categories_name ON blog_categories (name)');
        $this->addSql('COMMENT ON COLUMN blog_categories.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_categories.parent_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_categories.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN blog_categories.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE blog_categories');
    }
}
