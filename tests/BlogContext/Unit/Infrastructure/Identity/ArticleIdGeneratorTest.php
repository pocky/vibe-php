<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Infrastructure\Identity;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Infrastructure\Identity\ArticleIdGenerator;
use App\Shared\Infrastructure\Generator\UuidGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ArticleIdGeneratorTest extends TestCase
{
    private ArticleIdGenerator $articleIdGenerator;

    protected function setUp(): void
    {
        // Use the real UuidGenerator since it's a simple wrapper
        $this->articleIdGenerator = new ArticleIdGenerator(new UuidGenerator());
    }

    public function testNextIdentityGeneratesArticleId(): void
    {
        $articleId = $this->articleIdGenerator->nextIdentity();

        $this->assertInstanceOf(ArticleId::class, $articleId);
        // Verify it's a valid UUID
        $this->assertTrue(Uuid::isValid($articleId->getValue()));
    }

    public function testNextIdentityGeneratesUniqueIds(): void
    {
        $articleId1 = $this->articleIdGenerator->nextIdentity();
        $articleId2 = $this->articleIdGenerator->nextIdentity();

        $this->assertNotEquals($articleId1->getValue(), $articleId2->getValue());
        // Both should be valid UUIDs
        $this->assertTrue(Uuid::isValid($articleId1->getValue()));
        $this->assertTrue(Uuid::isValid($articleId2->getValue()));
    }

    public function testNextIdentityUsesGeneratorInterface(): void
    {
        // This test verifies the constructor accepts GeneratorInterface
        // and the implementation works correctly
        $articleId = $this->articleIdGenerator->nextIdentity();

        $this->assertInstanceOf(ArticleId::class, $articleId);
        $this->assertTrue(Uuid::isValid($articleId->getValue()));
    }
}
