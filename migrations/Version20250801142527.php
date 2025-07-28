<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250801142527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_article_tags (article_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(article_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_8522FCA07294869C ON blog_article_tags (article_id)');
        $this->addSql('CREATE INDEX IDX_8522FCA0BAD26311 ON blog_article_tags (tag_id)');
        $this->addSql('COMMENT ON COLUMN blog_article_tags.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_article_tags.tag_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE blog_tags (id UUID NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, article_count INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BLOG_TAG_NAME ON blog_tags (name)');
        $this->addSql('CREATE INDEX IDX_BLOG_TAG_ARTICLE_COUNT ON blog_tags (article_count)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BLOG_TAG_SLUG ON blog_tags (slug)');
        $this->addSql('COMMENT ON COLUMN blog_tags.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_tags.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN blog_tags.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE blog_article_tags ADD CONSTRAINT FK_8522FCA07294869C FOREIGN KEY (article_id) REFERENCES blog_articles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE blog_article_tags ADD CONSTRAINT FK_8522FCA0BAD26311 FOREIGN KEY (tag_id) REFERENCES blog_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE blog_articles ADD excerpt TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE blog_articles ADD category_id VARCHAR(36) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE blog_article_tags DROP CONSTRAINT FK_8522FCA07294869C');
        $this->addSql('ALTER TABLE blog_article_tags DROP CONSTRAINT FK_8522FCA0BAD26311');
        $this->addSql('DROP TABLE blog_article_tags');
        $this->addSql('DROP TABLE blog_tags');
        $this->addSql('ALTER TABLE blog_articles DROP excerpt');
        $this->addSql('ALTER TABLE blog_articles DROP category_id');
    }
}
