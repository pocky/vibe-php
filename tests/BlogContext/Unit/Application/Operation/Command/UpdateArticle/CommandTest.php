<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\UpdateArticle;

use App\BlogContext\Application\Operation\Command\UpdateArticle\Command;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    public function testCanCreateCommand(): void
    {
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Updated Article Title',
            content: 'This is the updated content of the article with sufficient length.'
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $command->articleId);
        self::assertSame('Updated Article Title', $command->title);
        self::assertSame('This is the updated content of the article with sufficient length.', $command->content);
    }

    public function testCommandIsReadonly(): void
    {
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test Title',
            content: 'Test content with sufficient length.'
        );

        // Verify properties are readonly by checking reflection
        $reflection = new \ReflectionClass($command);

        foreach (['articleId', 'title', 'content'] as $property) {
            $prop = $reflection->getProperty($property);
            self::assertTrue($prop->isReadOnly(), "Property {$property} should be readonly");
        }
    }
}
