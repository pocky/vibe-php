<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718233024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create blog_categories table with nested set support and add category_id to blog_articles';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_categories (id UUID NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(250) NOT NULL, description TEXT NOT NULL, parent_id UUID DEFAULT NULL, path VARCHAR(500) NOT NULL, level INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, article_count INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DC356481989D9B62 ON blog_categories (slug)');
        $this->addSql('CREATE INDEX idx_categories_slug ON blog_categories (slug)');
        $this->addSql('CREATE INDEX idx_categories_parent_id ON blog_categories (parent_id)');
        $this->addSql('CREATE INDEX idx_categories_level ON blog_categories (level)');
        $this->addSql('CREATE INDEX idx_categories_nested_set ON blog_categories (lft, rgt)');
        $this->addSql('CREATE INDEX idx_categories_path ON blog_categories (path)');
        $this->addSql('COMMENT ON COLUMN blog_categories.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_categories.parent_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_categories.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN blog_categories.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE blog_articles ADD category_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN blog_articles.category_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX idx_articles_category_id ON blog_articles (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE blog_categories');
        $this->addSql('DROP INDEX idx_articles_category_id');
        $this->addSql('ALTER TABLE blog_articles DROP category_id');
    }
}
