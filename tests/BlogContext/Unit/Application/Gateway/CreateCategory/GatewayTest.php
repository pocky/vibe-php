<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\CreateCategory;

use App\BlogContext\Application\Gateway\CreateCategory\Request;
use App\BlogContext\Application\Gateway\CreateCategory\Response;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use PHPUnit\Framework\TestCase;

final class GatewayTest extends TestCase
{
    public function testGatewayProcessesValidRequest(): void
    {
        // Cette partie sera implémentée une fois que nous aurons les middlewares configurés
        $this->markTestSkipped('Gateway test will be implemented after middleware setup');
    }

    public function testRequestValidation(): void
    {
        // Given
        $validData = [
            'name' => 'Technology',
            'slug' => 'technology',
            'parentId' => null,
            'createdAt' => '2024-01-01T12:00:00+00:00',
        ];

        // When
        $request = Request::fromData($validData);

        // Then
        $this->assertInstanceOf(GatewayRequest::class, $request);
        $this->assertEquals('Technology', $request->name);
        $this->assertEquals('technology', $request->slug);
        $this->assertNull($request->parentId);
    }

    public function testRequestRequiredFields(): void
    {
        // Given
        $invalidData = []; // Missing required fields

        // When & Then
        $this->expectException(\InvalidArgumentException::class);
        Request::fromData($invalidData);
    }

    public function testResponseStructure(): void
    {
        // Given
        $categoryId = '550e8400-e29b-41d4-a716-446655440000';
        $name = 'Technology';
        $slug = 'technology';
        $path = 'technology';
        $createdAt = new \DateTimeImmutable('2024-01-01 12:00:00');

        // When
        $response = new Response(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            path: $path,
            parentId: null,
            createdAt: $createdAt
        );

        // Then
        $this->assertInstanceOf(GatewayResponse::class, $response);
        $this->assertEquals($categoryId, $response->categoryId);
        $this->assertEquals($name, $response->name);
        $this->assertEquals($slug, $response->slug);
        $this->assertEquals($path, $response->path);
        $this->assertNull($response->parentId);

        $data = $response->data();
        $this->assertIsArray($data);
        $this->assertEquals($categoryId, $data['categoryId']);
        $this->assertEquals($name, $data['name']);
        $this->assertEquals($slug, $data['slug']);
        $this->assertEquals($path, $data['path']);
        $this->assertNull($data['parentId']);
    }
}
