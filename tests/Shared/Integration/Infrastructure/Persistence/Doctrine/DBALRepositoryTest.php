<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Infrastructure\Persistence\Doctrine;

use App\Shared\Infrastructure\Persistence\Doctrine\DBALRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class DBALRepositoryTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $mockConnection;

    private DBALRepository $dbalRepository;

    protected function setUp(): void
    {
        $this->mockConnection = $this->createMock(Connection::class);
        $this->dbalRepository = new class($this->mockConnection) extends DBALRepository {};
    }

    public function testClassIsAbstract(): void
    {
        $reflectionClass = new \ReflectionClass(DBALRepository::class);

        $this->assertTrue($reflectionClass->isAbstract());
    }

    public function testConstructorAcceptsConnection(): void
    {
        $connection = $this->createMock(Connection::class);
        $repository = new class($connection) extends DBALRepository {};

        $this->assertInstanceOf(DBALRepository::class, $repository);
    }

    public function testGetConnectionReturnsInjectedConnection(): void
    {
        $connection = $this->dbalRepository->getConnection();

        $this->assertSame($this->mockConnection, $connection);
    }

    public function testGetQueryBuilderCreatesNewQueryBuilder(): void
    {
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);

        $this->mockConnection
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $queryBuilder = $this->dbalRepository->getQueryBuilder();

        $this->assertSame($mockQueryBuilder, $queryBuilder);
    }

    public function testBeginTransactionDelegatesToConnection(): void
    {
        $this->mockConnection
            ->expects($this->once())
            ->method('beginTransaction');

        $this->dbalRepository->beginTransaction();
    }

    public function testBeginTransactionCanThrowException(): void
    {
        $expectedException = new \Doctrine\DBAL\Exception('Transaction failed');

        $this->mockConnection
            ->expects($this->once())
            ->method('beginTransaction')
            ->willThrowException($expectedException);

        $this->expectException(\Doctrine\DBAL\Exception::class);
        $this->expectExceptionMessage('Transaction failed');

        $this->dbalRepository->beginTransaction();
    }

    public function testGetConnectionMethodIsPublic(): void
    {
        $reflectionClass = new \ReflectionClass(DBALRepository::class);
        $reflectionMethod = $reflectionClass->getMethod('getConnection');

        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertSame('getConnection', $reflectionMethod->getName());
    }

    public function testGetQueryBuilderMethodIsPublic(): void
    {
        $reflectionClass = new \ReflectionClass(DBALRepository::class);
        $reflectionMethod = $reflectionClass->getMethod('getQueryBuilder');

        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertSame('getQueryBuilder', $reflectionMethod->getName());
    }

    public function testBeginTransactionMethodIsPublic(): void
    {
        $reflectionClass = new \ReflectionClass(DBALRepository::class);
        $reflectionMethod = $reflectionClass->getMethod('beginTransaction');

        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertSame('beginTransaction', $reflectionMethod->getName());
    }

    public function testConnectionParameterProperties(): void
    {
        $reflectionClass = new \ReflectionClass(DBALRepository::class);
        $constructor = $reflectionClass->getConstructor();
        $this->assertInstanceOf(\ReflectionMethod::class, $constructor);
        $parameters = $constructor->getParameters();

        $connectionParam = $parameters[0];
        $this->assertTrue($connectionParam->hasType());
        $this->assertSame(Connection::class, $connectionParam->getType()->getName());
        $this->assertSame('connection', $connectionParam->getName());
        $this->assertTrue($connectionParam->isPromoted());
    }

    public function testApiAnnotations(): void
    {
        $reflectionClass = new \ReflectionClass(DBALRepository::class);

        $reflectionMethod = $reflectionClass->getMethod('getConnection');
        $this->assertStringContainsString('@api', (string) $reflectionMethod->getDocComment());

        $getQueryBuilderMethod = $reflectionClass->getMethod('getQueryBuilder');
        $this->assertStringContainsString('@api', (string) $getQueryBuilderMethod->getDocComment());

        $beginTransactionMethod = $reflectionClass->getMethod('beginTransaction');
        $this->assertStringContainsString('@api', (string) $beginTransactionMethod->getDocComment());
    }

    public function testReturnTypes(): void
    {
        $reflectionClass = new \ReflectionClass(DBALRepository::class);

        $reflectionMethod = $reflectionClass->getMethod('getConnection');
        $this->assertTrue($reflectionMethod->hasReturnType());
        $this->assertSame(Connection::class, $reflectionMethod->getReturnType()->getName());

        $getQueryBuilderMethod = $reflectionClass->getMethod('getQueryBuilder');
        $this->assertTrue($getQueryBuilderMethod->hasReturnType());
        $this->assertSame(QueryBuilder::class, $getQueryBuilderMethod->getReturnType()->getName());

        $beginTransactionMethod = $reflectionClass->getMethod('beginTransaction');
        $this->assertTrue($beginTransactionMethod->hasReturnType());
        $this->assertSame('void', $beginTransactionMethod->getReturnType()->getName());
    }

    public function testMultipleQueryBuilderCallsCreateNewInstances(): void
    {
        $queryBuilder1 = $this->createMock(QueryBuilder::class);
        $queryBuilder2 = $this->createMock(QueryBuilder::class);

        $this->mockConnection
            ->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($queryBuilder1, $queryBuilder2);

        $queryBuilder = $this->dbalRepository->getQueryBuilder();
        $result2 = $this->dbalRepository->getQueryBuilder();

        $this->assertSame($queryBuilder1, $queryBuilder);
        $this->assertSame($queryBuilder2, $result2);
        $this->assertNotSame($queryBuilder, $result2);
    }

    public function testCanExtendDBALRepository(): void
    {
        $customRepository = new class($this->mockConnection) extends DBALRepository {
            public function customMethod(): string
            {
                return 'custom';
            }
        };

        $this->assertInstanceOf(DBALRepository::class, $customRepository);
        $this->assertSame('custom', $customRepository->customMethod());
        $this->assertSame($this->mockConnection, $customRepository->getConnection());
    }
}
