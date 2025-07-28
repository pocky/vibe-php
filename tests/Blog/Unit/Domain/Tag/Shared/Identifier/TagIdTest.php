<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Tag\Shared\Identifier;

use App\Blog\Domain\Shared\Exception\ValidationException;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use PHPUnit\Framework\TestCase;

final class TagIdTest extends TestCase
{
    public function test_it_creates_valid_tag_id(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');

        $this->assertSame('01J1234567890ABCDEFGHJKMNP', $tagId->getValue());
    }

    public function test_it_throws_exception_for_empty_id(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_id.empty');

        new TagId('');
    }

    public function test_it_throws_exception_for_invalid_length(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_id.invalid_format');

        new TagId('123'); // Too short
    }

    public function test_it_throws_exception_for_invalid_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_id.invalid_format');

        new TagId('01J1234567890ABCDEFGHIJ@'); // Contains special character
    }

    public function test_it_accepts_valid_ulid_format(): void
    {
        $validIds = [
            '01ARZ3NDEKTSV4RRFFQ69G5FAV',
            '01F8MECHZX3TBDSZ7XN8G0FRZE',
            '01HXQP4Q4AESV8N0KATRH7BXWP',
        ];

        foreach ($validIds as $validId) {
            $tagId = new TagId($validId);
            $this->assertSame($validId, $tagId->getValue());
        }
    }

    public function test_it_throws_exception_for_lowercase_letters(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_id.invalid_format');

        new TagId('01j1234567890abcdefghjkmnp'); // Lowercase not allowed in ULID
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $id1 = new TagId('01J1234567890ABCDEFGHJKMNP');
        $id2 = new TagId('01J1234567890ABCDEFGHJKMNP');

        $this->assertTrue($id1->equals($id2));
    }

    public function test_equals_returns_false_for_different_value(): void
    {
        $id1 = new TagId('01J1234567890ABCDEFGHJKMNP');
        $id2 = new TagId('01J9876543210ZYXWVTSRQPNMH');

        $this->assertFalse($id1->equals($id2));
    }

    public function test_to_string_returns_value(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');

        $this->assertSame('01J1234567890ABCDEFGHJKMNP', (string) $tagId);
    }

    public function test_it_throws_exception_for_invalid_ulid_characters(): void
    {
        $invalidIds = [
            '01J1234567890ABCDEFGHJKMNL', // L is not valid in Crockford Base32
            '01J1234567890ABCDEFGHJKMNO', // O is not valid
            '01J1234567890ABCDEFGHJKMNU', // U is not valid
            '01J1234567890ABCDEFGHJKMNI', // I is not valid
        ];

        foreach ($invalidIds as $invalidId) {
            try {
                new TagId($invalidId);
                $this->fail('Expected ValidationException for ID: ' . $invalidId);
            } catch (ValidationException $e) {
                $this->assertSame('validation.tag_id.invalid_format', $e->getMessage());
            }
        }
    }

    public function test_from_string_creates_tag_id(): void
    {
        $id = TagId::fromString('01J1234567890ABCDEFGHJKMNP');

        $this->assertInstanceOf(TagId::class, $id);
        $this->assertSame('01J1234567890ABCDEFGHJKMNP', $id->getValue());
    }
}
