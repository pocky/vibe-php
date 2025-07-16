<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Page\Admin\Crud;

use App\Tests\BlogContext\Behat\Page\SymfonyPage;
use Behat\Mink\Element\NodeElement;

class IndexPage extends SymfonyPage implements IndexPageInterface
{
    public function countItems(): int
    {
        $tbody = $this->session->getPage()->find('css', 'table tbody');
        if (null === $tbody) {
            return 0;
        }

        $rows = $tbody->findAll('css', 'tr');

        return count($rows);
    }

    public function getColumnFields(string $columnName): array
    {
        $table = $this->getElement('table');
        $headers = $table->findAll('css', 'thead th');

        $columnIndex = null;
        foreach ($headers as $index => $header) {
            if (str_contains(strtolower($header->getText()), strtolower($columnName))) {
                $columnIndex = $index;
                break;
            }
        }

        if (null === $columnIndex) {
            return [];
        }

        $rows = $table->findAll('css', 'tbody tr');
        $fields = [];

        foreach ($rows as $row) {
            $cells = $row->findAll('css', 'td');
            if (isset($cells[$columnIndex])) {
                $fields[] = trim($cells[$columnIndex]->getText());
            }
        }

        return $fields;
    }

    public function sortBy(string $fieldName): void
    {
        $sortLink = $this->session->getPage()->find('css', sprintf('thead th a:contains("%s")', $fieldName));
        if (null !== $sortLink) {
            $sortLink->click();
        }
    }

    public function isSingleResourceOnPage(array $fields): bool
    {
        $tbody = $this->session->getPage()->find('css', 'table tbody');
        if (null === $tbody) {
            return false;
        }

        $rows = $tbody->findAll('css', 'tr');

        foreach ($rows as $row) {
            $rowText = $row->getText();
            $allFieldsFound = array_all($fields, fn ($field) => str_contains($rowText, (string) $field));

            if ($allFieldsFound) {
                return true;
            }
        }

        return false;
    }

    public function deleteResourceOnPage(array $fields): void
    {
        $row = $this->findRowByFields($fields);
        if (!$row instanceof NodeElement) {
            throw new \RuntimeException('Cannot find resource to delete');
        }

        $deleteButton = $row->find('css', 'a.btn-danger, button.btn-danger, .delete-action');
        if (null !== $deleteButton) {
            $deleteButton->click();
        }
    }

    public function filter(array $criteria): void
    {
        foreach ($criteria as $field => $value) {
            $filter = $this->session->getPage()->find('css', sprintf('[name="criteria[%s]"]', $field));
            if (null !== $filter) {
                $filter->setValue($value);
            }
        }

        $filterButton = $this->session->getPage()->find('css', 'button[type="submit"], input[type="submit"]');
        if (null !== $filterButton) {
            $filterButton->click();
        }
    }

    public function bulkDelete(): void
    {
        // Select all checkboxes
        $checkboxes = $this->session->getPage()->findAll('css', 'tbody input[type="checkbox"]');
        foreach ($checkboxes as $checkbox) {
            $checkbox->check();
        }

        // Find and click bulk action
        $bulkSelect = $this->session->getPage()->find('css', 'select[name*="bulk"]');
        if (null !== $bulkSelect) {
            $bulkSelect->selectOption('delete');
        }

        $bulkButton = $this->session->getPage()->find('css', '.bulk-actions button[type="submit"]');
        if (null !== $bulkButton) {
            $bulkButton->click();
        }
    }

    public function getItemPosition(array $fields): int
    {
        $tbody = $this->session->getPage()->find('css', 'table tbody');
        if (null === $tbody) {
            return -1;
        }

        $rows = $tbody->findAll('css', 'tr');

        foreach ($rows as $index => $row) {
            $rowText = $row->getText();
            $allFieldsFound = array_all($fields, fn ($field) => str_contains($rowText, (string) $field));

            if ($allFieldsFound) {
                return $index + 1; // 1-based position
            }
        }

        return -1;
    }

    public function isEmpty(): bool
    {
        return 0 === $this->countItems();
    }

    public function hasNoResultMessage(): bool
    {
        $page = $this->session->getPage();
        $pageText = $page->getText();

        $noResultsVariations = [
            'No results',
            'No data',
            'No items found',
            'There are no items to display',
            'Empty',
            'Nothing to display',
        ];

        foreach ($noResultsVariations as $variation) {
            if (false !== stripos($pageText, $variation)) {
                return true;
            }
        }

        // Check if table is empty
        $tbody = $page->find('css', 'table tbody');
        if ($tbody && '' === trim($tbody->getText())) {
            return true;
        }

        // Check for "0 items" or similar
        return (bool) preg_match('/\\b0\\s+(items?|results?)\\b/i', $pageText);
    }

    public function hasColumnsWithHeaders(array $expectedHeaders): bool
    {
        $table = $this->session->getPage()->find('css', 'table');
        if (null === $table) {
            return false;
        }

        $headers = $table->findAll('css', 'thead th');
        if (empty($headers)) {
            return false;
        }

        foreach ($expectedHeaders as $expectedHeader) {
            $headerFound = false;

            foreach ($headers as $header) {
                $headerText = trim($header->getText());
                // Check for exact match or partial match (case insensitive)
                if (str_contains(strtolower($headerText), strtolower((string) $expectedHeader))) {
                    $headerFound = true;
                    break;
                }
            }

            if (!$headerFound) {
                return false;
            }
        }

        return true;
    }

    protected function getRouteName(): string
    {
        // Override in concrete implementations
        return 'admin_index';
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function getDefinedElements(): array
    {
        return [
            'table' => 'table',
            'rows' => 'tbody tr',
            'no_results' => '.empty-state, .no-results',
        ];
    }

    private function findRowByFields(array $fields): NodeElement|null
    {
        $tbody = $this->session->getPage()->find('css', 'table tbody');
        if (null === $tbody) {
            return null;
        }

        $rows = $tbody->findAll('css', 'tr');

        foreach ($rows as $row) {
            $rowText = $row->getText();
            $allFieldsFound = array_all($fields, fn ($field) => str_contains($rowText, (string) $field));

            if ($allFieldsFound) {
                return $row;
            }
        }

        return null;
    }
}
