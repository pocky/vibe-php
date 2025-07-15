<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\UpdateArticle;

use App\BlogContext\Application\Operation\Command\UpdateArticle\Command;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testCanCreateCommand(): void
    {
        $articleIdValue = $this->generateArticleId()->getValue();
        $articleId = new ArticleId($articleIdValue);
        $title = new Title('Updated Article Title');
        $content = new Content('This is the updated content of the article with sufficient length.');
        $slug = new Slug('updated-article-title');
        $status = ArticleStatus::PUBLISHED;

        $command = new Command(
            articleId: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: $status
        );

        self::assertSame($articleId, $command->articleId);
        self::assertSame($title, $command->title);
        self::assertSame($content, $command->content);
        self::assertSame($slug, $command->slug);
        self::assertSame($status, $command->status);
        self::assertSame($articleIdValue, $command->articleId->getValue());
        self::assertSame('Updated Article Title', $command->title->getValue());
        self::assertSame('This is the updated content of the article with sufficient length.', $command->content->getValue());
        self::assertSame('updated-article-title', $command->slug->getValue());
        self::assertSame('published', $command->status->toString());
    }

    public function testCommandIsReadonly(): void
    {
        $command = new Command(
            articleId: new ArticleId($this->generateArticleId()->getValue()),
            title: new Title('Test Title'),
            content: new Content('Test content with sufficient length.'),
            slug: new Slug('test-title'),
            status: ArticleStatus::DRAFT
        );

        // Verify properties are readonly by checking reflection
        $reflection = new \ReflectionClass($command);

        foreach (['articleId', 'title', 'content', 'slug', 'status'] as $property) {
            $prop = $reflection->getProperty($property);
            self::assertTrue($prop->isReadOnly(), "Property {$property} should be readonly");
        }
    }
}
