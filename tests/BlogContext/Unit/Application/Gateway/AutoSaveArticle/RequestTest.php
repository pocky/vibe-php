<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\AutoSaveArticle;

use App\BlogContext\Application\Gateway\AutoSaveArticle\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testCanCreateAutoSaveRequest(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Auto-saved title',
            content: 'Auto-saved content'
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        self::assertSame('Auto-saved title', $request->title);
        self::assertSame('Auto-saved content', $request->content);
    }

    public function testCanCreateFromDataWithEmptyFields(): void
    {
        $data = [
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'title' => '',
            'content' => '',
        ];

        $request = Request::fromData($data);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        self::assertSame('', $request->title);
        self::assertSame('', $request->content);
    }

    public function testCanCreateFromDataWithMissingOptionalFields(): void
    {
        $data = [
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
        ];

        $request = Request::fromData($data);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        self::assertSame('', $request->title);
        self::assertSame('', $request->content);
    }
}
