<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Gateway\Category\CreateCategory;

use App\Blog\Application\Gateway\Category\CreateCategory\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    #[Test]
    public function validResponse_constructsSuccessfully(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-01');

        $response = new Response(
            id: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology related articles',
            parentId: 'parent-456',
            order: 10,
            createdAt: $createdAt
        );

        $this->assertSame('category-123', $response->id);
        $this->assertSame('Technology', $response->name);
        $this->assertSame('technology', $response->slug);
        $this->assertSame('Technology related articles', $response->description);
        $this->assertSame('parent-456', $response->parentId);
        $this->assertSame(10, $response->order);
        $this->assertSame($createdAt, $response->createdAt);
    }

    #[Test]
    public function data_returnsCorrectArray(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00+00:00');

        $response = new Response(
            id: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology related articles',
            parentId: 'parent-456',
            order: 10,
            createdAt: $createdAt
        );

        $expectedData = [
            'id' => 'category-123',
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Technology related articles',
            'parentId' => 'parent-456',
            'order' => 10,
            'createdAt' => '2024-01-01T10:00:00+00:00',
        ];

        $this->assertSame($expectedData, $response->data());
    }

    #[Test]
    public function validResponse_withNullOptionalFields_constructsSuccessfully(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-01');

        $response = new Response(
            id: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology related articles',
            parentId: null,
            order: null,
            createdAt: $createdAt
        );

        $this->assertSame('category-123', $response->id);
        $this->assertSame('Technology', $response->name);
        $this->assertSame('technology', $response->slug);
        $this->assertSame('Technology related articles', $response->description);
        $this->assertNull($response->parentId);
        $this->assertNull($response->order);
        $this->assertSame($createdAt, $response->createdAt);
    }
}
