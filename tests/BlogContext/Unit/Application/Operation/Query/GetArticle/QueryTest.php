<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Query\GetArticle;

use App\BlogContext\Application\Operation\Query\GetArticle\Query;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testQueryCreationWithValidId(): void
    {
        $id = $this->generateArticleId()->getValue();

        $query = new Query($id);

        $this->assertEquals($id, $query->id);
    }

    public function testQueryIsReadonly(): void
    {
        $id = $this->generateArticleId()->getValue();

        $query = new Query($id);

        // Should be readonly - attempting to modify should fail
        $this->expectException(\Error::class);
        $query->id = 'different-id';
    }
}
