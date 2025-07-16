<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Page\Admin\Article;

use App\Tests\BlogContext\Behat\Page\Admin\Crud\IndexPageInterface as BaseIndexPageInterface;

interface IndexPageInterface extends BaseIndexPageInterface
{
    public function hasArticleWithTitle(string $title): bool;

    public function hasArticleWithSlug(string $slug): bool;

    public function hasArticleWithStatus(string $status): bool;

    public function filterByStatus(string $status): void;

    public function searchByTitle(string $title): void;

    public function getArticlesGrid(): array;

    public function clickCreateArticle(): void;

    public function editArticle(string $title): void;

    public function deleteArticle(string $title): void;

    public function hasColumnsWithHeaders(array $headers): bool;
}
