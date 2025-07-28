<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Author\CreateAuthor;

use App\Blog\Application\Operation\Command\Author\CreateAuthor\Command;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    #[Test]
    public function command_withAllValidData_createsSuccessfully(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $name = 'John Doe';
        $email = 'john@example.com';
        $bio = 'A passionate writer';

        // When
        $command = new Command(
            authorId: $authorId,
            name: $name,
            email: $email,
            bio: $bio
        );

        // Then
        $this->assertTrue($command->authorId->equals($authorId));
        $this->assertSame($name, $command->name);
        $this->assertSame($email, $command->email);
        $this->assertSame($bio, $command->bio);
    }

    #[Test]
    public function command_withEmptyBio_createsSuccessfully(): void
    {
        // Given
        $authorId = new AuthorId('123e4567-e89b-12d3-a456-426614174000');
        $name = 'Jane Smith';
        $email = 'jane@example.com';
        $bio = '';

        // When
        $command = new Command(
            authorId: $authorId,
            name: $name,
            email: $email,
            bio: $bio
        );

        // Then
        $this->assertTrue($command->authorId->equals($authorId));
        $this->assertSame($name, $command->name);
        $this->assertSame($email, $command->email);
        $this->assertSame($bio, $command->bio);
    }

    #[Test]
    public function command_withEmptyName_throwsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Author name cannot be empty');

        // When
        new Command(
            authorId: $authorId,
            name: '',
            email: 'john@example.com',
            bio: 'Bio'
        );
    }

    #[Test]
    public function command_withEmptyEmail_throwsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Author email cannot be empty');

        // When
        new Command(
            authorId: $authorId,
            name: 'John Doe',
            email: '',
            bio: 'Bio'
        );
    }

    #[Test]
    public function command_withInvalidEmailFormat_throwsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        // When
        new Command(
            authorId: $authorId,
            name: 'John Doe',
            email: 'invalid-email',
            bio: 'Bio'
        );
    }
}
