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
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflectionClass->isAbstract());
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflectionClass->isSubclassOf(ServiceEntityRepository::class));
    }

    public function testImplementsIteratorAggregate(): void
    {
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflectionClass->implementsInterface(\IteratorAggregate::class));
    }

    public function testWithPaginationValidatesPositiveIntegers(): void
    {
        $doctrineRepository = $this->createTestRepository();

        $this->expectException(\InvalidArgumentException::class);
        $doctrineRepository->withPagination(0, 10);
    }

    public function testWithPaginationValidatesPositiveItemsPerPage(): void
    {
        $doctrineRepository = $this->createTestRepository();

        $this->expectException(\InvalidArgumentException::class);
        $doctrineRepository->withPagination(1, -5);
    }

    public function testWithPaginationReturnsNewInstance(): void
    {
        $doctrineRepository = $this->createTestRepository();
        $result = $doctrineRepository->withPagination(2, 15);

        $this->assertNotSame($doctrineRepository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testWithoutPaginationReturnsNewInstance(): void
    {
        $doctrineRepository = $this->createTestRepository();
        $result = $doctrineRepository->withoutPagination();

        $this->assertNotSame($doctrineRepository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testWithPageReturnsNewInstance(): void
    {
        $doctrineRepository = $this->createTestRepository();
        $result = $doctrineRepository->withPage(3);

        $this->assertNotSame($doctrineRepository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testWithItemsPerPageReturnsNewInstance(): void
    {
        $doctrineRepository = $this->createTestRepository();
        $result = $doctrineRepository->withItemsPerPage(20);

        $this->assertNotSame($doctrineRepository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testFilterReturnsNewInstance(): void
    {
        $doctrineRepository = $this->createTestRepository();
        $filter = function (QueryBuilder $queryBuilder): void {
            // Mock filter function
        };

        $result = $doctrineRepository->callFilter($filter);

        $this->assertNotSame($doctrineRepository, $result);
        $this->assertInstanceOf(DoctrineRepository::class, $result);
    }

    public function testHasGenericTemplate(): void
    {
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);
        $docComment = $reflectionClass->getDocComment();

        $this->assertIsString($docComment);
        $this->assertStringContainsString('@template T of object', $docComment);
        $this->assertStringContainsString('@extends ServiceEntityRepository<T>', $docComment);
    }

    public function testMethodVisibility(): void
    {
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);

        $publicMethods = ['withPagination', 'withoutPagination', 'withPage', 'withItemsPerPage', 'getIterator', 'paginator'];
        foreach ($publicMethods as $publicMethod) {
            $method = $reflectionClass->getMethod($publicMethod);
            $this->assertTrue($method->isPublic(), sprintf('Method %s should be public', $publicMethod));
        }

        $protectedMethods = ['filter'];
        foreach ($protectedMethods as $protectedMethod) {
            $method = $reflectionClass->getMethod($protectedMethod);
            $this->assertTrue($method->isProtected(), sprintf('Method %s should be protected', $protectedMethod));
        }
    }

    public function testConstructorParametersAndTypes(): void
    {
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);
        $constructor = $reflectionClass->getConstructor();
        $this->assertInstanceOf(\ReflectionMethod::class, $constructor);
        $parameters = $constructor->getParameters();

        $this->assertCount(3, $parameters);

        $registryParam = $parameters[0];
        $this->assertSame('managerRegistry', $registryParam->getName());
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
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);

        $fluentMethods = ['withPagination', 'withoutPagination', 'withPage', 'withItemsPerPage', 'filter'];
        foreach ($fluentMethods as $fluentMethod) {
            $method = $reflectionClass->getMethod($fluentMethod);
            $returnType = $method->getReturnType();
            $this->assertInstanceOf(\ReflectionType::class, $returnType, sprintf('Method %s should have return type', $fluentMethod));
            $this->assertSame('static', $returnType->getName(), sprintf('Method %s should return static', $fluentMethod));
        }
    }

    public function testOverrideAttribute(): void
    {
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);
        $reflectionMethod = $reflectionClass->getMethod('getIterator');
        $attributes = $reflectionMethod->getAttributes(\Override::class);

        $this->assertCount(1, $attributes);
    }

    public function testPaginatorMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflectionClass->hasMethod('paginator'));

        $reflectionMethod = $reflectionClass->getMethod('paginator');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertTrue($reflectionMethod->hasReturnType());
        $this->assertSame(PaginatorInterface::class, $reflectionMethod->getReturnType()->getName());
    }

    public function testGetIteratorMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflectionClass->hasMethod('getIterator'));

        $reflectionMethod = $reflectionClass->getMethod('getIterator');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertTrue($reflectionMethod->hasReturnType());
        $this->assertSame('Traversable', $reflectionMethod->getReturnType()->getName());
    }

    public function testCloneMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(DoctrineRepository::class);

        $this->assertTrue($reflectionClass->hasMethod('__clone'));

        $reflectionMethod = $reflectionClass->getMethod('__clone');
        $this->assertTrue($reflectionMethod->isProtected());
    }

    private function createTestRepository(): DoctrineRepository
    {
        $mockRegistry = $this->createMock(ManagerRegistry::class);
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);

        return new class($mockRegistry, \stdClass::class, 'e', $mockQueryBuilder) extends DoctrineRepository {
            public function __construct(
                ManagerRegistry $managerRegistry,
                string $entityClass,
                string $alias,
                private readonly QueryBuilder $mockQueryBuilder,
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
