<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715194303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add review functionality: editorial comments table, review fields to articles, and indexes for review workflow';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_editorial_comments (id UUID NOT NULL, article_id UUID NOT NULL, reviewer_id UUID NOT NULL, comment TEXT NOT NULL, selected_text TEXT DEFAULT NULL, position_start INT DEFAULT NULL, position_end INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_editorial_comments_article_id ON blog_editorial_comments (article_id)');
        $this->addSql('CREATE INDEX idx_editorial_comments_reviewer_id ON blog_editorial_comments (reviewer_id)');
        $this->addSql('CREATE INDEX idx_editorial_comments_created_at ON blog_editorial_comments (created_at)');
        $this->addSql('COMMENT ON COLUMN blog_editorial_comments.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_editorial_comments.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_editorial_comments.reviewer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_editorial_comments.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE blog_articles ADD author_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE blog_articles ADD submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE blog_articles ADD reviewed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE blog_articles ADD reviewer_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE blog_articles ADD approval_reason TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE blog_articles ADD rejection_reason TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE blog_articles ALTER updated_at DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN blog_articles.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_articles.submitted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN blog_articles.reviewed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN blog_articles.reviewer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX idx_articles_author_id ON blog_articles (author_id)');
        $this->addSql('CREATE INDEX idx_articles_submitted_at ON blog_articles (submitted_at)');
        $this->addSql('CREATE INDEX idx_articles_reviewed_at ON blog_articles (reviewed_at)');
        $this->addSql('CREATE INDEX idx_articles_reviewer_id ON blog_articles (reviewer_id)');
        $this->addSql('CREATE INDEX idx_articles_review_queue ON blog_articles (status, submitted_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE blog_editorial_comments');
        $this->addSql('DROP INDEX idx_articles_author_id');
        $this->addSql('DROP INDEX idx_articles_submitted_at');
        $this->addSql('DROP INDEX idx_articles_reviewed_at');
        $this->addSql('DROP INDEX idx_articles_reviewer_id');
        $this->addSql('DROP INDEX idx_articles_review_queue');
        $this->addSql('ALTER TABLE blog_articles DROP author_id');
        $this->addSql('ALTER TABLE blog_articles DROP submitted_at');
        $this->addSql('ALTER TABLE blog_articles DROP reviewed_at');
        $this->addSql('ALTER TABLE blog_articles DROP reviewer_id');
        $this->addSql('ALTER TABLE blog_articles DROP approval_reason');
        $this->addSql('ALTER TABLE blog_articles DROP rejection_reason');
        $this->addSql('ALTER TABLE blog_articles ALTER updated_at SET NOT NULL');
    }
}
