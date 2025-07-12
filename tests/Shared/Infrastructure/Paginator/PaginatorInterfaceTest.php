<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Paginator;

use App\Shared\Infrastructure\Paginator\PaginatorInterface;
use PHPUnit\Framework\TestCase;

final class PaginatorInterfaceTest extends TestCase
{
    public function testPaginatorInterfaceIsInterface(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);

        $this->assertTrue($reflection->isInterface());
    }

    public function testPaginatorInterfaceExtendsCorrectInterfaces(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);

        $this->assertTrue($reflection->implementsInterface(\IteratorAggregate::class));
        $this->assertTrue($reflection->implementsInterface(\Countable::class));
    }

    public function testPaginatorInterfaceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);

        $this->assertTrue($reflection->hasMethod('getCurrentPage'));
        $this->assertTrue($reflection->hasMethod('getItemsPerPage'));
        $this->assertTrue($reflection->hasMethod('getLastPage'));
        $this->assertTrue($reflection->hasMethod('getTotalItems'));
        $this->assertTrue($reflection->hasMethod('getIterator'));
        $this->assertTrue($reflection->hasMethod('count'));
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

    public function testGetLastPageMethodSignature(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);
        $method = $reflection->getMethod('getLastPage');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());
        $this->assertTrue($method->hasReturnType());
        $this->assertSame('int', $method->getReturnType()->getName());
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

    public function testGetIteratorMethodSignature(): void
    {
        $reflection = new \ReflectionClass(\IteratorAggregate::class);
        $method = $reflection->getMethod('getIterator');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());

        // IteratorAggregate::getIterator() is inherited, check it exists
        $paginatorReflection = new \ReflectionClass(PaginatorInterface::class);
        $this->assertTrue($paginatorReflection->hasMethod('getIterator'));
    }

    public function testCountMethodSignature(): void
    {
        $reflection = new \ReflectionClass(\Countable::class);
        $method = $reflection->getMethod('count');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());

        // Countable::count() is inherited, check it exists
        $paginatorReflection = new \ReflectionClass(PaginatorInterface::class);
        $this->assertTrue($paginatorReflection->hasMethod('count'));
    }

    public function testInterfaceCanBeImplemented(): void
    {
        $implementation = new class implements PaginatorInterface {
            public function getCurrentPage(): int
            {
                return 1;
            }

            public function getItemsPerPage(): int
            {
                return 10;
            }

            public function getLastPage(): int
            {
                return 5;
            }

            public function getTotalItems(): int
            {
                return 42;
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator(['item1', 'item2']);
            }

            public function count(): int
            {
                return 2;
            }
        };

        $this->assertInstanceOf(PaginatorInterface::class, $implementation);
        $this->assertSame(1, $implementation->getCurrentPage());
        $this->assertSame(10, $implementation->getItemsPerPage());
        $this->assertSame(5, $implementation->getLastPage());
        $this->assertSame(42, $implementation->getTotalItems());
        $this->assertSame(2, $implementation->count());
        $this->assertInstanceOf(\Traversable::class, $implementation->getIterator());
    }

    public function testInterfaceWithGenerics(): void
    {
        $reflection = new \ReflectionClass(PaginatorInterface::class);
        $docComment = $reflection->getDocComment();

        $this->assertIsString($docComment);
        $this->assertStringContainsString('@template T of object', $docComment);
        $this->assertStringContainsString('@extends \IteratorAggregate<array-key, T>', $docComment);
    }
}
