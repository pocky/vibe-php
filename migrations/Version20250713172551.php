<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250713172551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_articles (id UUID NOT NULL, title VARCHAR(200) NOT NULL, content TEXT NOT NULL, slug VARCHAR(250) NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB80154F989D9B62 ON blog_articles (slug)');
        $this->addSql('CREATE INDEX idx_articles_status ON blog_articles (status)');
        $this->addSql('CREATE INDEX idx_articles_slug ON blog_articles (slug)');
        $this->addSql('CREATE INDEX idx_articles_published_at ON blog_articles (published_at)');
        $this->addSql('CREATE INDEX idx_articles_created_at ON blog_articles (created_at)');
        $this->addSql('COMMENT ON COLUMN blog_articles.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_articles.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN blog_articles.published_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN blog_articles.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE blog_articles');
    }
}
