<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Service\Accessor;

use Behat\Mink\Element\NodeElement;
use Webmozart\Assert\Assert;

final class TableAccessor implements TableAccessorInterface
{
    #[\Override]
    public function getRowWithFields(NodeElement $nodeElement, array $fields): NodeElement
    {
        try {
            return $this->getRowsWithFields($nodeElement, $fields)[0];
        } catch (\InvalidArgumentException $invalidArgumentException) {
            throw new \InvalidArgumentException('Could not find row with given fields', 0, $invalidArgumentException);
        }
    }

    #[\Override]
    public function getRowsWithFields(NodeElement $nodeElement, array $fields): array
    {
        try {
            return $this->findRowsWithFields($nodeElement, $fields);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            throw new \InvalidArgumentException('Could not find any row with given fields', 0, $invalidArgumentException);
        }
    }

    #[\Override]
    public function getFieldFromRow(NodeElement $table, NodeElement $row, $field): NodeElement
    {
        $columnIndex = $this->getColumnIndex($table, $field);

        $columns = $row->findAll('css', 'td,th');
        if (!isset($columns[$columnIndex])) {
            throw new \InvalidArgumentException(sprintf('Could not find column with index %d', $columnIndex));
        }

        return $columns[$columnIndex];
    }

    #[\Override]
    public function getIndexedColumn(NodeElement $nodeElement, $fieldName): array
    {
        $columnIndex = $this->getColumnIndex($nodeElement, $fieldName);

        $rows = $nodeElement->findAll('css', 'tbody > tr');
        Assert::notEmpty($rows, 'There are no rows!');

        $columnFields = [];
        /** @var NodeElement $row */
        foreach ($rows as $row) {
            $cells = $row->findAll('css', 'td');
            $columnFields[] = $cells[$columnIndex]->getText();
        }

        return $columnFields;
    }

    #[\Override]
    public function getSortableHeaders(NodeElement $nodeElement): array
    {
        $sortableHeaders = $nodeElement->findAll('css', 'th.sortable');
        Assert::notEmpty($sortableHeaders, 'There are no sortable headers.');

        $sortableArray = [];
        foreach ($sortableHeaders as $sortableHeader) {
            $fieldName = $this->getColumnFieldName($sortableHeader);

            $sortableArray[$fieldName] = $sortableHeader;
        }

        return $sortableArray;
    }

    #[\Override]
    public function countTableBodyRows(NodeElement $nodeElement): int
    {
        return count($nodeElement->findAll('css', 'tbody > tr'));
    }

    /**
     * @return NodeElement[]
     *
     * @throws \InvalidArgumentException If rows were not found
     */
    private function findRowsWithFields(NodeElement $nodeElement, array $fields): array
    {
        $rows = $nodeElement->findAll('css', 'tr');

        Assert::notEmpty($rows, 'There are no rows!');

        $fields = $this->replaceColumnNamesWithColumnIndexes($nodeElement, $fields);

        $matchedRows = [];
        /** @var NodeElement[] $rows */
        $rows = $nodeElement->findAll('css', 'tr');
        foreach ($rows as $row) {
            /** @var NodeElement[] $columns */
            $columns = $row->findAll('css', 'td, th');
            if ($this->hasRowFields($columns, $fields)) {
                $matchedRows[] = $row;
            }
        }

        return $matchedRows;
    }

    private function hasRowFields(array $columns, array $fields): bool
    {
        foreach ($fields as $index => $searchedValue) {
            if (!isset($columns[$index])) {
                return false;
            }

            $searchedValue = trim((string) $searchedValue);

            if (str_starts_with($searchedValue, '%') && (strlen($searchedValue) - 1) === strrpos($searchedValue, '%')) {
                $searchedValue = substr($searchedValue, 1, -2);
            }

            if (!$this->containsSearchedValue($columns[$index]->getText(), $searchedValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string[] $fields
     *
     * @return string[]
     *
     * @throws \Exception
     */
    private function replaceColumnNamesWithColumnIndexes(NodeElement $nodeElement, array $fields): array
    {
        $replacedFields = [];
        foreach ($fields as $columnName => $expectedValue) {
            $columnIndex = $this->getColumnIndex($nodeElement, $columnName);

            $replacedFields[$columnIndex] = $expectedValue;
        }

        return $replacedFields;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getColumnIndex(NodeElement $nodeElement, string $fieldName): int
    {
        $rows = $nodeElement->findAll('css', 'tr');
        Assert::notEmpty($rows, 'There are no rows!');

        /** @var NodeElement $headerRow */
        $headerRow = $rows[0];
        $headers = $headerRow->findAll('css', 'th,td');

        /** @var NodeElement $column */
        foreach ($headers as $index => $column) {
            $columnFieldName = $this->getColumnFieldName($column);
            if ($fieldName === $columnFieldName) {
                return $index;
            }
        }

        throw new \InvalidArgumentException(sprintf('Column with name "%s" not found!', $fieldName));
    }

    private function containsSearchedValue(string $sourceText, string $searchedValue): bool
    {
        return false !== stripos(trim($sourceText), $searchedValue);
    }

    private function getColumnFieldName(NodeElement $nodeElement): string
    {
        return preg_replace('/.*sylius-table-column-([^ ]+).*$/', '\1', (string) $nodeElement->getAttribute('class'));
    }
}
