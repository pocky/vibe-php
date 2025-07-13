<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Paginator;

use App\Shared\Infrastructure\Paginator\PaginatorInterface;
use PHPUnit\Framework\TestCase;

final class PaginatorInterfaceTest extends TestCase
{
    public function testPaginatorInterfaceIsInterface(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);

        $this->assertTrue($reflection->isInterface());
    }

    public function testPaginatorInterfaceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);

        $this->assertTrue($reflection->hasMethod('getItems'));
        $this->assertTrue($reflection->hasMethod('getTotalItems'));
        $this->assertTrue($reflection->hasMethod('getCurrentPage'));
        $this->assertTrue($reflection->hasMethod('getItemsPerPage'));
        $this->assertTrue($reflection->hasMethod('hasNextPage'));
    }

    public function testGetCurrentPageMethodSignature(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);
        $method = $reflection->getMethod('getCurrentPage');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());
        $this->assertTrue($method->hasReturnType());
        $this->assertSame('int', $method->getReturnType()->getName());
    }

    public function testGetItemsPerPageMethodSignature(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);
        $method = $reflection->getMethod('getItemsPerPage');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());
        $this->assertTrue($method->hasReturnType());
        $this->assertSame('int', $method->getReturnType()->getName());
    }

    public function testGetItemsMethodSignature(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);
        $method = $reflection->getMethod('getItems');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());
        $this->assertTrue($method->hasReturnType());
        $this->assertSame('array', $method->getReturnType()->getName());
    }

    public function testGetTotalItemsMethodSignature(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);
        $method = $reflection->getMethod('getTotalItems');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());
        $this->assertTrue($method->hasReturnType());
        $this->assertSame('int', $method->getReturnType()->getName());
    }

    public function testHasNextPageMethodSignature(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);
        $method = $reflection->getMethod('hasNextPage');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());
        $this->assertTrue($method->hasReturnType());
        $this->assertSame('bool', $method->getReturnType()->getName());
    }

    public function testInterfaceCanBeImplemented(): void
    {
        $implementation = new class implements PaginatorInterface {
            public function getItems(): array
            {
                return ['item1', 'item2'];
            }

            public function getTotalItems(): int
            {
                return 42;
            }

            public function getCurrentPage(): int
            {
                return 1;
            }

            public function getItemsPerPage(): int
            {
                return 10;
            }

            public function hasNextPage(): bool
            {
                return true;
            }
        };

        $this->assertInstanceOf(PaginatorInterface::class, $implementation);
        $this->assertEquals(['item1', 'item2'], $implementation->getItems());
        $this->assertSame(42, $implementation->getTotalItems());
        $this->assertSame(1, $implementation->getCurrentPage());
        $this->assertSame(10, $implementation->getItemsPerPage());
        $this->assertTrue($implementation->hasNextPage());
    }
}
