<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725173317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_authors (id UUID NOT NULL, name VARCHAR(200) NOT NULL, email VARCHAR(255) NOT NULL, bio TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_62FA97D2E7927C74 ON blog_authors (email)');
        $this->addSql('CREATE INDEX idx_authors_email ON blog_authors (email)');
        $this->addSql('CREATE INDEX idx_authors_name ON blog_authors (name)');
        $this->addSql('COMMENT ON COLUMN blog_authors.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog_authors.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN blog_authors.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX idx_categories_slug');
        $this->addSql('ALTER TABLE blog_categories ADD "order" INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE blog_authors');
        $this->addSql('ALTER TABLE blog_categories DROP "order"');
        $this->addSql('CREATE INDEX idx_categories_slug ON blog_categories (slug)');
    }
}
