<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\CreateCategory;

use App\BlogContext\Application\Gateway\CreateCategory\Request;
use App\Shared\Application\Gateway\GatewayRequest;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testItImplementsGatewayRequest(): void
    {
        $request = new Request(
            name: 'Technology',
            description: 'Tech articles',
        );

        $this->assertInstanceOf(GatewayRequest::class, $request);
    }

    public function testItCanBeCreatedWithValidData(): void
    {
        $request = new Request(
            name: 'Technology',
            description: 'Tech articles',
            parentCategoryId: '123',
            order: 1,
        );

        $this->assertEquals('Technology', $request->name);
        $this->assertEquals('Tech articles', $request->description);
        $this->assertEquals('123', $request->parentCategoryId);
        $this->assertEquals(1, $request->order);
    }

    public function testItCanBeCreatedWithMinimalData(): void
    {
        $request = new Request(
            name: 'Technology',
            description: 'Tech articles',
        );

        $this->assertEquals('Technology', $request->name);
        $this->assertEquals('Tech articles', $request->description);
        $this->assertNull($request->parentCategoryId);
        $this->assertEquals(0, $request->order);
    }

    public function testItThrowsExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name is required');

        new Request(
            name: '',
            description: 'Description',
        );
    }

    public function testItThrowsExceptionWhenDescriptionIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Description is required');

        new Request(
            name: 'Category',
            description: '',
        );
    }

    public function testItCanBeCreatedFromData(): void
    {
        $data = [
            'name' => 'Technology',
            'description' => 'Tech articles',
            'parentCategoryId' => '123',
            'order' => 5,
        ];

        $request = Request::fromData($data);

        $this->assertEquals('Technology', $request->name);
        $this->assertEquals('Tech articles', $request->description);
        $this->assertEquals('123', $request->parentCategoryId);
        $this->assertEquals(5, $request->order);
    }

    public function testItCanBeCreatedFromDataWithDefaults(): void
    {
        $data = [
            'name' => 'Technology',
            'description' => 'Tech articles',
        ];

        $request = Request::fromData($data);

        $this->assertEquals('Technology', $request->name);
        $this->assertEquals('Tech articles', $request->description);
        $this->assertNull($request->parentCategoryId);
        $this->assertEquals(0, $request->order);
    }

    public function testItCanReturnData(): void
    {
        $request = new Request(
            name: 'Technology',
            description: 'Tech articles',
            parentCategoryId: '123',
            order: 5,
        );

        $data = $request->data();

        $this->assertEquals([
            'name' => 'Technology',
            'description' => 'Tech articles',
            'parentCategoryId' => '123',
            'order' => 5,
        ], $data);
    }
}
