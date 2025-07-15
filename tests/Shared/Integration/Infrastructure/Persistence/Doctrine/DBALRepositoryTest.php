<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Infrastructure\Persistence\Doctrine;

use App\Shared\Infrastructure\Persistence\Doctrine\DBALRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class DBALRepositoryTest extends TestCase
{
    private Connection $mockConnection;
    private DBALRepository $repository;

    protected function setUp(): void
    {
        $this->mockConnection = $this->createMock(Connection::class);
        $this->repository = new class($this->mockConnection) extends DBALRepository {};
    }

    public function testClassIsAbstract(): void
    {
        $reflection = new \ReflectionClass(DBALRepository::class);

        $this->assertTrue($reflection->isAbstract());
    }

    public function testConstructorAcceptsConnection(): void
    {
        $connection = $this->createMock(Connection::class);
        $repository = new class($connection) extends DBALRepository {};

        $this->assertInstanceOf(DBALRepository::class, $repository);
    }

    public function testGetConnectionReturnsInjectedConnection(): void
    {
        $result = $this->repository->getConnection();

        $this->assertSame($this->mockConnection, $result);
    }

    public function testGetQueryBuilderCreatesNewQueryBuilder(): void
    {
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);

        $this->mockConnection
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $result = $this->repository->getQueryBuilder();

        $this->assertSame($mockQueryBuilder, $result);
    }

    public function testBeginTransactionDelegatesToConnection(): void
    {
        $this->mockConnection
            ->expects($this->once())
            ->method('beginTransaction');

        $this->repository->beginTransaction();
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

        $this->repository->beginTransaction();
    }

    public function testGetConnectionMethodIsPublic(): void
    {
        $reflection = new \ReflectionClass(DBALRepository::class);
        $method = $reflection->getMethod('getConnection');

        $this->assertTrue($method->isPublic());
        $this->assertSame('getConnection', $method->getName());
    }

    public function testGetQueryBuilderMethodIsPublic(): void
    {
        $reflection = new \ReflectionClass(DBALRepository::class);
        $method = $reflection->getMethod('getQueryBuilder');

        $this->assertTrue($method->isPublic());
        $this->assertSame('getQueryBuilder', $method->getName());
    }

    public function testBeginTransactionMethodIsPublic(): void
    {
        $reflection = new \ReflectionClass(DBALRepository::class);
        $method = $reflection->getMethod('beginTransaction');

        $this->assertTrue($method->isPublic());
        $this->assertSame('beginTransaction', $method->getName());
    }

    public function testConnectionParameterProperties(): void
    {
        $reflection = new \ReflectionClass(DBALRepository::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $connectionParam = $parameters[0];
        $this->assertTrue($connectionParam->hasType());
        $this->assertSame(Connection::class, $connectionParam->getType()->getName());
        $this->assertSame('connection', $connectionParam->getName());
        $this->assertTrue($connectionParam->isPromoted());
    }

    public function testApiAnnotations(): void
    {
        $reflection = new \ReflectionClass(DBALRepository::class);

        $getConnectionMethod = $reflection->getMethod('getConnection');
        $this->assertStringContainsString('@api', $getConnectionMethod->getDocComment());

        $getQueryBuilderMethod = $reflection->getMethod('getQueryBuilder');
        $this->assertStringContainsString('@api', $getQueryBuilderMethod->getDocComment());

        $beginTransactionMethod = $reflection->getMethod('beginTransaction');
        $this->assertStringContainsString('@api', $beginTransactionMethod->getDocComment());
    }

    public function testReturnTypes(): void
    {
        $reflection = new \ReflectionClass(DBALRepository::class);

        $getConnectionMethod = $reflection->getMethod('getConnection');
        $this->assertTrue($getConnectionMethod->hasReturnType());
        $this->assertSame(Connection::class, $getConnectionMethod->getReturnType()->getName());

        $getQueryBuilderMethod = $reflection->getMethod('getQueryBuilder');
        $this->assertTrue($getQueryBuilderMethod->hasReturnType());
        $this->assertSame(QueryBuilder::class, $getQueryBuilderMethod->getReturnType()->getName());

        $beginTransactionMethod = $reflection->getMethod('beginTransaction');
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

        $result1 = $this->repository->getQueryBuilder();
        $result2 = $this->repository->getQueryBuilder();

        $this->assertSame($queryBuilder1, $result1);
        $this->assertSame($queryBuilder2, $result2);
        $this->assertNotSame($result1, $result2);
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
