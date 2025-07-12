<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Paginator;

use App\Shared\Infrastructure\Paginator\PagerfantaPaginator;
use App\Shared\Infrastructure\Paginator\PaginatorInterface;
use Pagerfanta\PagerfantaInterface;
use PHPUnit\Framework\TestCase;

final class PagerfantaPaginatorTest extends TestCase
{
    private PagerfantaInterface $mockPagerfanta;
    private PagerfantaPaginator $paginator;

    protected function setUp(): void
    {
        $this->mockPagerfanta = $this->createMock(PagerfantaInterface::class);
        $this->paginator = new PagerfantaPaginator($this->mockPagerfanta);
    }

    public function testImplementsPaginatorInterface(): void
    {
        $this->assertInstanceOf(PaginatorInterface::class, $this->paginator);
    }

    public function testClassIsFinalAndReadonly(): void
    {
        $reflection = new \ReflectionClass(PagerfantaPaginator::class);

        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testConstructorAcceptsPagerfantaInterface(): void
    {
        $pagerfanta = $this->createMock(PagerfantaInterface::class);
        $paginator = new PagerfantaPaginator($pagerfanta);

        $this->assertInstanceOf(PagerfantaPaginator::class, $paginator);
    }

    public function testGetIteratorDelegatesToPagerfanta(): void
    {
        $expectedIterator = new \ArrayIterator(['item1', 'item2', 'item3']);

        $this->mockPagerfanta
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn($expectedIterator);

        $result = $this->paginator->getIterator();

        $this->assertSame($expectedIterator, $result);
    }

    public function testCountUsesIteratorCount(): void
    {
        $items = ['item1', 'item2', 'item3'];
        $iterator = new \ArrayIterator($items);

        $this->mockPagerfanta
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);

        $result = $this->paginator->count();

        $this->assertSame(3, $result);
    }

    public function testGetCurrentPageDelegatesToPagerfanta(): void
    {
        $expectedPage = 2;

        $this->mockPagerfanta
            ->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($expectedPage);

        $result = $this->paginator->getCurrentPage();

        $this->assertSame($expectedPage, $result);
    }

    public function testGetItemsPerPageDelegatesToPagerfanta(): void
    {
        $expectedItemsPerPage = 15;

        $this->mockPagerfanta
            ->expects($this->once())
            ->method('getMaxPerPage')
            ->willReturn($expectedItemsPerPage);

        $result = $this->paginator->getItemsPerPage();

        $this->assertSame($expectedItemsPerPage, $result);
    }

    public function testGetLastPageDelegatesToPagerfanta(): void
    {
        $expectedLastPage = 10;

        $this->mockPagerfanta
            ->expects($this->once())
            ->method('getNbPages')
            ->willReturn($expectedLastPage);

        $result = $this->paginator->getLastPage();

        $this->assertSame($expectedLastPage, $result);
    }

    public function testGetTotalItemsDelegatesToPagerfanta(): void
    {
        $expectedTotal = 150;

        $this->mockPagerfanta
            ->expects($this->once())
            ->method('getNbResults')
            ->willReturn($expectedTotal);

        $result = $this->paginator->getTotalItems();

        $this->assertSame($expectedTotal, $result);
    }

    public function testCanIterateOverPaginator(): void
    {
        $items = ['item1', 'item2', 'item3'];
        $iterator = new \ArrayIterator($items);

        $this->mockPagerfanta
            ->method('getIterator')
            ->willReturn($iterator);

        $result = [];
        foreach ($this->paginator as $item) {
            $result[] = $item;
        }

        $this->assertSame($items, $result);
    }

    public function testCountableInterface(): void
    {
        $items = new \ArrayIterator(['a', 'b', 'c', 'd']);

        $this->mockPagerfanta
            ->method('getIterator')
            ->willReturn($items);

        $count = count($this->paginator);

        $this->assertSame(4, $count);
    }

    public function testWithEmptyIterator(): void
    {
        $emptyIterator = new \ArrayIterator([]);

        $this->mockPagerfanta
            ->method('getIterator')
            ->willReturn($emptyIterator);

        $this->assertSame(0, $this->paginator->count());

        $result = [];
        foreach ($this->paginator as $item) {
            $result[] = $item;
        }

        $this->assertEmpty($result);
    }

    public function testWithComplexData(): void
    {
        $objects = [
            (object) [
                'id' => 1,
                'name' => 'Alice',
            ],
            (object) [
                'id' => 2,
                'name' => 'Bob',
            ],
        ];
        $iterator = new \ArrayIterator($objects);

        $this->mockPagerfanta
            ->method('getIterator')
            ->willReturn($iterator);

        $this->mockPagerfanta
            ->method('getCurrentPage')
            ->willReturn(1);

        $this->mockPagerfanta
            ->method('getMaxPerPage')
            ->willReturn(10);

        $this->mockPagerfanta
            ->method('getNbPages')
            ->willReturn(1);

        $this->mockPagerfanta
            ->method('getNbResults')
            ->willReturn(2);

        $this->assertSame(1, $this->paginator->getCurrentPage());
        $this->assertSame(10, $this->paginator->getItemsPerPage());
        $this->assertSame(1, $this->paginator->getLastPage());
        $this->assertSame(2, $this->paginator->getTotalItems());
        $this->assertSame(2, $this->paginator->count());

        $result = iterator_to_array($this->paginator);
        $this->assertCount(2, $result);
        $this->assertSame('Alice', $result[0]->name);
        $this->assertSame('Bob', $result[1]->name);
    }

    public function testPaginatorMaintainsTypeIntegrity(): void
    {
        $items = new \ArrayIterator(['string1', 'string2']);

        $this->mockPagerfanta
            ->method('getIterator')
            ->willReturn($items);

        foreach ($this->paginator as $item) {
            $this->assertIsString($item);
        }
    }
}
