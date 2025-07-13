<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\PublishArticle;

use App\BlogContext\Application\Operation\Command\PublishArticle\Command;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testCanCreateCommand(): void
    {
        $articleIdValue = $this->generateArticleId()->getValue();
        $articleId = new ArticleId($articleIdValue);

        $command = new Command(
            articleId: $articleId
        );

        self::assertSame($articleId, $command->articleId);
        self::assertSame($articleIdValue, $command->articleId->getValue());
    }

    public function testCommandIsReadonly(): void
    {
        $command = new Command(
            articleId: new ArticleId($this->generateArticleId()->getValue())
        );

        $reflection = new \ReflectionClass($command);
        self::assertTrue($reflection->isReadOnly(), 'Command should be readonly');
    }
}
