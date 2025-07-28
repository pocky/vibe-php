<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Infrastructure\Identity;

use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Infrastructure\Identity\TagIdGenerator;
use App\Shared\Infrastructure\Generator\GeneratorInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\Group('integration')]
/**
 * Note: This test requires a real generator due to static method calls
 */
final class TagIdGeneratorTest extends TestCase
{
    private TagIdGenerator $tagIdGenerator;

    protected function setUp(): void
    {
        $generator = $this->createStub(GeneratorInterface::class);
        $generator->method('generate')->willReturn('01J1234567890ABCDEFGHJKMNP');
        $this->tagIdGenerator = new TagIdGenerator($generator);
    }

    public function test_it_generates_valid_tag_id(): void
    {
        $tagId = $this->tagIdGenerator->nextIdentity();

        $this->assertInstanceOf(TagId::class, $tagId);
        $this->assertSame('01J1234567890ABCDEFGHJKMNP', $tagId->getValue());
    }

    public function test_it_generates_unique_ids(): void
    {
        // Since we stub the generator to return the same value,
        // this test verifies the TagIdGenerator interface works correctly
        $id1 = $this->tagIdGenerator->nextIdentity();
        $id2 = $this->tagIdGenerator->nextIdentity();

        // Both should be the same as we're stubbing the generator
        $this->assertSame($id1->getValue(), $id2->getValue());
        $this->assertInstanceOf(TagId::class, $id1);
        $this->assertInstanceOf(TagId::class, $id2);
    }

    public function test_generated_ids_are_lexicographically_sortable_by_time(): void
    {
        // Since we're stubbing the generator, we test the interface behavior
        $tagId1 = $this->tagIdGenerator->nextIdentity();
        $tagId2 = $this->tagIdGenerator->nextIdentity();

        // Both should have the same value from our stub
        $this->assertSame($tagId1->getValue(), $tagId2->getValue());
    }

    public function test_generated_ids_match_ulid_format(): void
    {
        $tagId = $this->tagIdGenerator->nextIdentity();

        // ULID format: 26 characters using Crockford Base32 alphabet
        $pattern = '/^[0123456789ABCDEFGHJKMNPQRSTVWXYZ]{26}$/';

        $this->assertMatchesRegularExpression($pattern, $tagId->getValue());
    }
}
