<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\AutoSaveArticle;

use App\BlogContext\Application\Operation\Command\AutoSaveArticle\Command;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    public function testCanCreateCommand(): void
    {
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Auto-saved Title',
            content: 'Auto-saved content'
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $command->articleId);
        self::assertSame('Auto-saved Title', $command->title);
        self::assertSame('Auto-saved content', $command->content);
    }

    public function testCommandIsReadonly(): void
    {
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            content: 'Content'
        );

        $reflection = new \ReflectionClass($command);
        self::assertTrue($reflection->isReadOnly(), 'Command should be readonly');
    }
}
