<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Infrastructure\Persistence\Doctrine;

use App\Shared\Infrastructure\Paginator\PaginatorInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class DoctrineRepositoryTest extends TestCase
{
    public function testClassIsAbstract(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflection->isAbstract());
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflection->isSubclassOf(ServiceEntityRepository::class));
    }

    public function testImplementsIteratorAggregate(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflection->implementsInterface(\IteratorAggregate::class));
    }

    public function testWithPaginationValidatesPositiveIntegers(): void
    {
        $repository = $this->createTestRepository();

        $this->expectException(\InvalidArgumentException::class);
        $repository->withPagination(0, 10);
    }

    public function testWithPaginationValidatesPositiveItemsPerPage(): void
    {
        $repository = $this->createTestRepository();

        $this->expectException(\InvalidArgumentException::class);
        $repository->withPagination(1, -5);
    }

    public function testWithPaginationReturnsNewInstance(): void
    {
        $repository = $this->createTestRepository();
        $result = $repository->withPagination(2, 15);

        $this->assertNotSame($repository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testWithoutPaginationReturnsNewInstance(): void
    {
        $repository = $this->createTestRepository();
        $result = $repository->withoutPagination();

        $this->assertNotSame($repository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testWithPageReturnsNewInstance(): void
    {
        $repository = $this->createTestRepository();
        $result = $repository->withPage(3);

        $this->assertNotSame($repository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testWithItemsPerPageReturnsNewInstance(): void
    {
        $repository = $this->createTestRepository();
        $result = $repository->withItemsPerPage(20);

        $this->assertNotSame($repository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testFilterReturnsNewInstance(): void
    {
        $repository = $this->createTestRepository();
        $filter = function (QueryBuilder $qb): void {
            // Mock filter function
        };

        $result = $repository->callFilter($filter);

        $this->assertNotSame($repository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testHasGenericTemplate(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);
        $docComment = $reflection->getDocComment();

        $this->assertIsString($docComment);
        $this->assertStringContainsString('@template T of object', $docComment);
        $this->assertStringContainsString('@extends ServiceEntityRepository<T>', $docComment);
    }

    public function testMethodVisibility(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);

        $publicMethods = ['withPagination', 'withoutPagination', 'withPage', 'withItemsPerPage', 'getIterator', 'paginator'];
        foreach ($publicMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPublic(), "Method {$methodName} should be public");
        }

        $protectedMethods = ['filter'];
        foreach ($protectedMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isProtected(), "Method {$methodName} should be protected");
        }
    }

    public function testConstructorParametersAndTypes(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(3, $parameters);

        $registryParam = $parameters[0];
        $this->assertSame('registry', $registryParam->getName());
        $this->assertSame(ManagerRegistry::class, $registryParam->getType()->getName());

        $entityClassParam = $parameters[1];
        $this->assertSame('entityClass', $entityClassParam->getName());
        $this->assertSame('string', $entityClassParam->getType()->getName());

        $aliasParam = $parameters[2];
        $this->assertSame('alias', $aliasParam->getName());
        $this->assertSame('string', $aliasParam->getType()->getName());
    }

    public function testReturnTypeAnnotations(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);

        $fluentMethods = ['withPagination', 'withoutPagination', 'withPage', 'withItemsPerPage', 'filter'];
        foreach ($fluentMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $returnType = $method->getReturnType();
            $this->assertNotNull($returnType, "Method {$methodName} should have return type");
            $this->assertSame('static', $returnType->getName(), "Method {$methodName} should return static");
        }
    }

    public function testOverrideAttribute(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);
        $getIteratorMethod = $reflection->getMethod('getIterator');
        $attributes = $getIteratorMethod->getAttributes(\Override::class);

        $this->assertCount(1, $attributes);
    }

    public function testPaginatorMethodExists(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflection->hasMethod('paginator'));

        $method = $reflection->getMethod('paginator');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->hasReturnType());
        $this->assertSame(PaginatorInterface::class, $method->getReturnType()->getName());
    }

    public function testGetIteratorMethodExists(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflection->hasMethod('getIterator'));

        $method = $reflection->getMethod('getIterator');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->hasReturnType());
        $this->assertSame('Traversable', $method->getReturnType()->getName());
    }

    public function testCloneMethodExists(): void
    {
        $reflection = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflection->hasMethod('__clone'));

        $method = $reflection->getMethod('__clone');
        $this->assertTrue($method->isProtected());
    }

    private function createTestRepository(): DoctrineRepository
    {
        $mockRegistry = $this->createMock(ManagerRegistry::class);
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);

        return new class($mockRegistry, \stdClass::class, 'e', $mockQueryBuilder) extends DoctrineRepository {
            public function __construct(
                ManagerRegistry $registry,
                string $entityClass,
                string $alias,
                private readonly QueryBuilder $mockQueryBuilder
            ) {
                // Skip parent constructor to avoid Doctrine setup
                $this->queryBuilder = $this->mockQueryBuilder;
            }

            public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
            {
                return $this->mockQueryBuilder;
            }

            public function callFilter(callable $filter): static
            {
                return $this->filter($filter);
            }
        };
    }
}
