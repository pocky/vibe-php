<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Webmozart\Assert\Assert;

abstract class ORMRepository
{
    protected(set) EntityManagerInterface|null $manager;

    public function __construct(
        ManagerRegistry $managerRegistry,
        protected(set) string $class
    ) {
        $manager = $managerRegistry->getManager();
        Assert::isInstanceOf($manager, EntityManagerInterface::class);
        $this->manager = $manager;
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function getClass()
    {
        Assert::notNull($this->manager);

        // @phpstan-ignore-next-line
        return new ($this->manager->getClassMetadata($this->getClassName())->getName());
    }

    public function getClassName(): string
    {
        return $this->class;
    }

    /**
     * @api
     */
    public function getQueryBuilder(): QueryBuilder
    {
        Assert::notNull($this->manager);
        
        return $this->manager->createQueryBuilder();
    }

    /**
     * @api
     */
    public function getQuery(string $sql): Query
    {
        Assert::notNull($this->manager);
        
        return $this->manager->createQuery($sql);
    }

    /**
     * @api
     */
    public function getNativeQuery(string $sql, ResultSetMapping $resultSetMapping): NativeQuery
    {
        Assert::notNull($this->manager);
        
        return $this->manager->createNativeQuery($sql, $resultSetMapping);
    }

    /**
     * @api
     */
    public function getRsm(): Query\ResultSetMappingBuilder
    {
        Assert::notNull($this->manager);
        
        return new Query\ResultSetMappingBuilder($this->manager);
    }
}
