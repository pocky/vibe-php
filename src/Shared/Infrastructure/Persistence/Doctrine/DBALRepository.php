<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class DBALRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @api
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @api
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->connection->createQueryBuilder();
    }

    /**
     * @api
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }
}
