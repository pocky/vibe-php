<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Service\Accessor;

use Behat\Mink\Element\NodeElement;

interface TableAccessorInterface
{
    /**
     * @throws \InvalidArgumentException If row cannot be found
     */
    public function getRowWithFields(NodeElement $nodeElement, array $fields): NodeElement;

    /**
     * @return NodeElement[]
     *
     * @throws \InvalidArgumentException If there is no rows fulfilling given conditions
     */
    public function getRowsWithFields(NodeElement $nodeElement, array $fields): array;

    /**
     * @return string[]
     *
     * @throws \InvalidArgumentException
     */
    public function getIndexedColumn(NodeElement $nodeElement, string $fieldName): array;

    /**
     * @return NodeElement[]
     */
    public function getSortableHeaders(NodeElement $nodeElement): array;

    public function getFieldFromRow(NodeElement $table, NodeElement $row, string $field): NodeElement;

    public function countTableBodyRows(NodeElement $nodeElement): int;
}
