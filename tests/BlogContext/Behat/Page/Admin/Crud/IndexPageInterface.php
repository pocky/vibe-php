<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Page\Admin\Crud;

use App\Tests\BlogContext\Behat\Page\PageInterface;

interface IndexPageInterface extends PageInterface
{
    public function countItems(): int;

    public function getColumnFields(string $columnName): array;

    public function sortBy(string $fieldName): void;

    public function isSingleResourceOnPage(array $fields): bool;

    public function deleteResourceOnPage(array $fields): void;

    public function filter(array $criteria): void;

    public function bulkDelete(): void;

    public function getItemPosition(array $fields): int;

    public function isEmpty(): bool;

    public function hasNoResultMessage(): bool;

    public function hasColumnsWithHeaders(array $expectedHeaders): bool;
}
