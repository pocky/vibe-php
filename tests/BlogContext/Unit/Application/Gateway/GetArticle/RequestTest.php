<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\GetArticle;

use App\BlogContext\Application\Gateway\GetArticle\Request;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testRequestCreationFromData(): void
    {
        $data = [
            'id' => $this->generateArticleId()->getValue(),
        ];

        $request = Request::fromData($data);

        $this->assertEquals($data['id'], $request->id);
    }

    public function testRequestDataSerialization(): void
    {
        $originalData = [
            'id' => $this->generateArticleId()->getValue(),
        ];
        $request = Request::fromData($originalData);

        $serializedData = $request->data();

        $this->assertEquals($originalData, $serializedData);
    }

    public function testRequestValidationWithEmptyId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article ID is required');

        Request::fromData([
            'id' => '',
        ]);
    }

    public function testRequestValidationWithMissingId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article ID is required');

        Request::fromData([]);
    }

    public function testRequestIsReadonly(): void
    {
        $request = Request::fromData([
            'id' => $this->generateArticleId()->getValue(),
        ]);

        // Should be readonly - attempting to modify should fail
        $this->expectException(\Error::class);
        $request->id = 'different-id';
    }
}
