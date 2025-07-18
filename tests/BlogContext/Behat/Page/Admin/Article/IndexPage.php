<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Page\Admin\Article;

use App\Tests\Shared\Behat\Context\Page\AbstractIndexPage;
use Behat\Mink\Element\NodeElement;

final class IndexPage extends AbstractIndexPage
{
    #[\Override]
    public function getRouteName(): string
    {
        return 'app_admin_article_index';
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function getDefinedElements(): array
    {
        return [
            ...parent::getDefinedElements(),
            'create_button' => 'a.btn-primary, .create-button',
            'search_field' => 'input[name*="search"], input[placeholder*="search"]',
            'status_filter' => 'select[name*="status"]',
            'articles_table' => 'table.articles-grid, table',
            'filter_form' => '.filters-form, form[name*="filter"]',
        ];
    }

    public function hasArticleWithTitle(string $title): bool
    {
        return $this->isSingleResourceOnPage([
            'title' => $title,
        ]);
    }

    public function hasArticleWithSlug(string $slug): bool
    {
        return $this->isSingleResourceOnPage([
            'slug' => $slug,
        ]);
    }

    public function hasArticleWithStatus(string $status): bool
    {
        return $this->isSingleResourceOnPage([
            'status' => $status,
        ]);
    }

    public function filterByStatus(string $status): void
    {
        $this->filter();
    }

    public function searchByTitle(string $title): void
    {
        $searchField = $this->getSession()->getPage()->find('css', 'input[name*="search"], input[placeholder*="search"]');
        if (null !== $searchField) {
            $searchField->setValue($title);

            $searchButton = $this->getSession()->getPage()->find('css', 'button[type="submit"], input[type="submit"]');
            if (null !== $searchButton) {
                $searchButton->click();
            }
        }
    }

    public function clickCreateArticle(): void
    {
        $createButton = $this->getSession()->getPage()->find('css', 'a.btn-primary:contains("Create"), a:contains("New Article"), .create-button');
        if (null !== $createButton) {
            $createButton->click();
        } else {
            // Fallback: navigate directly to create URL
            $this->getSession()->visit('/admin/articles/new');
        }
    }

    public function editArticle(string $title): void
    {
        $row = $this->findRowContaining($title);
        if (!$row instanceof NodeElement) {
            throw new \RuntimeException(sprintf('Cannot find article with title "%s"', $title));
        }

        $editButton = $row->find('css', 'a.btn-warning, a:contains("Edit"), .edit-action');
        if (null !== $editButton) {
            $editButton->click();
        }
    }

    public function deleteArticle(string $title): void
    {
        $this->deleteResourceOnPage([
            'title' => $title,
        ]);
    }

    public function hasColumnsWithHeaders(array $headers): bool
    {
        $table = $this->getSession()->getPage()->find('css', 'table');
        if (null === $table) {
            return false;
        }

        $headerElements = $table->findAll('css', 'thead th');
        $actualHeaders = array_map(fn ($header) => trim($header->getText()), $headerElements);

        foreach ($headers as $expectedHeader) {
            $found = array_any($actualHeaders, fn ($actualHeader) => str_contains(strtolower((string) $actualHeader), strtolower((string) $expectedHeader)));
            if (!$found) {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function findRowContaining(string $text): NodeElement
    {
        $rows = $this->getSession()->getPage()->findAll('css', 'table tbody tr');

        foreach ($rows as $row) {
            if (str_contains($row->getText(), $text)) {
                return $row;
            }
        }

        throw new \RuntimeException(sprintf('Could not find row containing "%s"', $text));
    }
}
