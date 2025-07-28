<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Gateway\Category\CreateCategory;

use App\Blog\Application\Gateway\Category\CreateCategory\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    #[Test]
    public function validRequest_withAllFields_constructsSuccessfully(): void
    {
        $request = new Request(
            name: 'Technology',
            description: 'Technology related articles',
            slug: 'technology',
            parentId: 'parent-123',
            order: 10
        );

        $this->assertSame('Technology', $request->name);
        $this->assertSame('technology', $request->slug);
        $this->assertSame('Technology related articles', $request->description);
        $this->assertSame('parent-123', $request->parentId);
        $this->assertSame(10, $request->order);
    }

    #[Test]
    public function validRequest_withOptionalFields_constructsSuccessfully(): void
    {
        $request = new Request(
            name: 'Technology',
            description: 'Technology related articles'
        );

        $this->assertSame('Technology', $request->name);
        $this->assertNull($request->slug);
        $this->assertSame('Technology related articles', $request->description);
        $this->assertNull($request->parentId);
        $this->assertNull($request->order);
    }

    #[Test]
    public function fromData_withValidData_constructsRequest(): void
    {
        $data = [
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Technology related articles',
            'parentId' => 'parent-123',
            'order' => 10,
        ];

        $request = Request::fromData($data);

        $this->assertSame('Technology', $request->name);
        $this->assertSame('technology', $request->slug);
        $this->assertSame('Technology related articles', $request->description);
        $this->assertSame('parent-123', $request->parentId);
        $this->assertSame(10, $request->order);
    }

    #[Test]
    public function fromData_withMissingOptionalFields_constructsRequest(): void
    {
        $data = [
            'name' => 'Technology',
            'description' => 'Technology related articles',
        ];

        $request = Request::fromData($data);

        $this->assertSame('Technology', $request->name);
        $this->assertNull($request->slug);
        $this->assertSame('Technology related articles', $request->description);
        $this->assertNull($request->parentId);
        $this->assertNull($request->order);
    }

    #[Test]
    public function data_returnsCorrectArray(): void
    {
        $request = new Request(
            name: 'Technology',
            description: 'Technology related articles',
            slug: 'technology',
            parentId: 'parent-123',
            order: 10
        );

        $expectedData = [
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Technology related articles',
            'parentId' => 'parent-123',
            'order' => 10,
        ];

        $this->assertSame($expectedData, $request->data());
    }
}
