<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\ListArticles;

use App\BlogContext\Application\Gateway\ListArticles\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testRequestCreationWithDefaults(): void
    {
        $data = [];

        $request = Request::fromData($data);

        $this->assertEquals(1, $request->page);
        $this->assertEquals(20, $request->limit);
        $this->assertNull($request->status);
        $this->assertNull($request->search);
    }

    public function testRequestCreationWithCustomValues(): void
    {
        $data = [
            'page' => 2,
            'limit' => 10,
            'status' => 'published',
            'search' => 'test search',
        ];

        $request = Request::fromData($data);

        $this->assertEquals(2, $request->page);
        $this->assertEquals(10, $request->limit);
        $this->assertEquals('published', $request->status);
        $this->assertEquals('test search', $request->search);
    }

    public function testRequestDataSerialization(): void
    {
        $originalData = [
            'page' => 3,
            'limit' => 5,
            'status' => 'draft',
            'search' => null,
            'articleId' => null,
        ];

        $request = Request::fromData($originalData);

        $serializedData = $request->data();

        $this->assertEquals($originalData, $serializedData);
    }

    public function testRequestValidationWithInvalidPage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be greater than 0');

        Request::fromData([
            'page' => 0,
        ]);
    }

    public function testRequestValidationWithInvalidLimit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');

        Request::fromData([
            'limit' => 101,
        ]);
    }

    public function testRequestIsReadonly(): void
    {
        $request = Request::fromData([
            'page' => 1,
        ]);

        // Should be readonly - attempting to modify should fail
        $this->expectException(\Error::class);
        $request->page = 999;
    }
}
