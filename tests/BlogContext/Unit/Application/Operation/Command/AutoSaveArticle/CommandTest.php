<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\AutoSaveArticle;

use App\BlogContext\Application\Operation\Command\AutoSaveArticle\Command;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Content, Title};
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testCanCreateCommand(): void
    {
        $articleIdValue = $this->generateArticleId()->getValue();
        $articleId = new ArticleId($articleIdValue);
        $title = new Title('Auto-saved Title');
        $content = new Content('Auto-saved content');

        $command = new Command(
            articleId: $articleId,
            title: $title,
            content: $content
        );

        self::assertSame($articleId, $command->articleId);
        self::assertSame($title, $command->title);
        self::assertSame($content, $command->content);
        self::assertSame($articleIdValue, $command->articleId->getValue());
        self::assertSame('Auto-saved Title', $command->title->getValue());
        self::assertSame('Auto-saved content', $command->content->getValue());
    }

    public function testCommandIsReadonly(): void
    {
        $command = new Command(
            articleId: new ArticleId($this->generateArticleId()->getValue()),
            title: new Title('Test Title'),
            content: new Content('Test Content')
        );

        $reflection = new \ReflectionClass($command);
        self::assertTrue($reflection->isReadOnly(), 'Command should be readonly');
    }
}
