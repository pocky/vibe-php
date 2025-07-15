<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class ArticleIdTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testCreateValidArticleId(): void
    {
        $uuid = $this->generateArticleId()->getValue();
        $articleId = new ArticleId($uuid);

        $this->assertSame($uuid, $articleId->getValue());
    }

    public function testArticleIdEquality(): void
    {
        $uuid = $this->generateArticleId()->getValue();
        $articleId1 = new ArticleId($uuid);
        $articleId2 = new ArticleId($uuid);
        $articleId3 = new ArticleId('123e4567-e89b-12d3-a456-426614174000');

        $this->assertTrue($articleId1->equals($articleId2));
        $this->assertFalse($articleId1->equals($articleId3));
    }

    public function testRejectInvalidUuid(): void
    {
        $this->expectException(ValidationException::class);

        try {
            new ArticleId('invalid-uuid');
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.id.invalid_uuid', $e->getTranslationKey());
            throw $e;
        }
    }

    public function testRejectEmptyString(): void
    {
        $this->expectException(ValidationException::class);

        try {
            new ArticleId('');
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.id.invalid_uuid', $e->getTranslationKey());
            throw $e;
        }
    }

    public function testRejectIncompleteUuid(): void
    {
        $this->expectException(ValidationException::class);

        try {
            new ArticleId('550e8400-e29b-41d4-a716');
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.id.invalid_uuid', $e->getTranslationKey());
            throw $e;
        }
    }

    public function testAcceptDifferentUuidVersions(): void
    {
        // UUID v4
        $uuidV4 = '123e4567-e89b-12d3-a456-426614174000';
        $articleId = new ArticleId($uuidV4);
        $this->assertSame($uuidV4, $articleId->getValue());

        // UUID v7 (as used in the project)
        $uuidV7 = '01234567-89ab-7cde-89ab-123456789012';
        $articleId2 = new ArticleId($uuidV7);
        $this->assertSame($uuidV7, $articleId2->getValue());
    }
}
