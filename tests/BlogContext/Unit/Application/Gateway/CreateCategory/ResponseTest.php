<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\CreateCategory;

use App\BlogContext\Application\Gateway\CreateCategory\Response;
use App\Shared\Application\Gateway\GatewayResponse;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testItImplementsGatewayResponse(): void
    {
        $response = new Response(
            success: true,
            message: 'Category created successfully',
        );

        $this->assertInstanceOf(GatewayResponse::class, $response);
    }

    public function testItCanBeCreatedWithSuccessResponse(): void
    {
        $response = new Response(
            success: true,
            message: 'Category created successfully',
            categoryId: '123',
            slug: 'technology',
        );

        $this->assertTrue($response->success);
        $this->assertEquals('Category created successfully', $response->message);
        $this->assertEquals('123', $response->categoryId);
        $this->assertEquals('technology', $response->slug);
    }

    public function testItCanBeCreatedWithFailureResponse(): void
    {
        $response = new Response(
            success: false,
            message: 'Category already exists',
        );

        $this->assertFalse($response->success);
        $this->assertEquals('Category already exists', $response->message);
        $this->assertNull($response->categoryId);
        $this->assertNull($response->slug);
    }

    public function testItReturnsDataArrayForSuccessResponse(): void
    {
        $response = new Response(
            success: true,
            message: 'Category created successfully',
            categoryId: '123',
            slug: 'technology',
        );

        $data = $response->data();

        $this->assertEquals([
            'success' => true,
            'message' => 'Category created successfully',
            'categoryId' => '123',
            'slug' => 'technology',
        ], $data);
    }

    public function testItReturnsDataArrayForFailureResponse(): void
    {
        $response = new Response(
            success: false,
            message: 'Category already exists',
        );

        $data = $response->data();

        $this->assertEquals([
            'success' => false,
            'message' => 'Category already exists',
        ], $data);
    }
}
