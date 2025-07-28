<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Paginator;

use App\Shared\Infrastructure\Paginator\PaginatorInterface;
use PHPUnit\Framework\TestCase;

final class PaginatorInterfaceTest extends TestCase
{
    public function testPaginatorInterfaceIsInterface(): void
    {
        $reflectionClass = new \ReflectionClass(PaginatorInterface::class);

        $this->assertTrue($reflectionClass->isInterface());
    }

    public function testPaginatorInterfaceHasRequiredMethods(): void
    {
        $reflectionClass = new \ReflectionClass(PaginatorInterface::class);

        $this->assertTrue($reflectionClass->hasMethod('getItems'));
        $this->assertTrue($reflectionClass->hasMethod('getTotalItems'));
        $this->assertTrue($reflectionClass->hasMethod('getCurrentPage'));
        $this->assertTrue($reflectionClass->hasMethod('getItemsPerPage'));
        $this->assertTrue($reflectionClass->hasMethod('hasNextPage'));
    }

    public function testGetCurrentPageMethodSignature(): void
    {
        $reflectionClass = new \ReflectionClass(PaginatorInterface::class);
        $reflectionMethod = $reflectionClass->getMethod('getCurrentPage');

        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
        $this->assertTrue($reflectionMethod->hasReturnType());
        $this->assertSame('int', $reflectionMethod->getReturnType()->getName());
    }

    public function testGetItemsPerPageMethodSignature(): void
    {
        $reflectionClass = new \ReflectionClass(PaginatorInterface::class);
        $reflectionMethod = $reflectionClass->getMethod('getItemsPerPage');

        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
        $this->assertTrue($reflectionMethod->hasReturnType());
        $this->assertSame('int', $reflectionMethod->getReturnType()->getName());
    }

    public function testGetItemsMethodSignature(): void
    {
        $reflectionClass = new \ReflectionClass(PaginatorInterface::class);
        $reflectionMethod = $reflectionClass->getMethod('getItems');

        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
        $this->assertTrue($reflectionMethod->hasReturnType());
        $this->assertSame('array', $reflectionMethod->getReturnType()->getName());
    }

    public function testGetTotalItemsMethodSignature(): void
    {
        $reflectionClass = new \ReflectionClass(PaginatorInterface::class);
        $reflectionMethod = $reflectionClass->getMethod('getTotalItems');

        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
        $this->assertTrue($reflectionMethod->hasReturnType());
        $this->assertSame('int', $reflectionMethod->getReturnType()->getName());
    }

    public function testHasNextPageMethodSignature(): void
    {
        $reflectionClass = new \ReflectionClass(PaginatorInterface::class);
        $reflectionMethod = $reflectionClass->getMethod('hasNextPage');

        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
        $this->assertTrue($reflectionMethod->hasReturnType());
        $this->assertSame('bool', $reflectionMethod->getReturnType()->getName());
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
        $this->assertSame(['item1', 'item2'], $implementation->getItems());
        $this->assertSame(42, $implementation->getTotalItems());
        $this->assertSame(1, $implementation->getCurrentPage());
        $this->assertSame(10, $implementation->getItemsPerPage());
        $this->assertTrue($implementation->hasNextPage());
    }
}
