<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Page\Admin\Editorial;

use App\Tests\BlogContext\Behat\Page\SymfonyPage;

final class DashboardPage extends SymfonyPage implements DashboardPageInterface
{
    public function hasArticlesPendingReview(): bool
    {
        return $this->hasElement('pending_articles_section') && !$this->hasElement('empty_pending_message');
    }

    public function getArticlesPendingReviewCount(): int
    {
        if (!$this->hasElement('pending_articles_table')) {
            return 0;
        }

        $rows = $this->session->getPage()->findAll('css', $this->getDefinedElements()['pending_articles_rows']);

        return count($rows);
    }

    public function hasArticleInPendingList(string $title): bool
    {
        if (!$this->hasElement('pending_articles_table')) {
            return false;
        }

        $pageText = $this->getElement('pending_articles_table')->getText();

        return str_contains($pageText, $title);
    }

    public function reviewArticle(string $title): void
    {
        $row = $this->findPendingArticleRow($title);
        if (!$row instanceof \Behat\Mink\Element\NodeElement) {
            throw new \RuntimeException(sprintf('Cannot find article "%s" in pending review list', $title));
        }

        $reviewButton = $row->find('css', 'a:contains("Review"), .review-action');
        if (null !== $reviewButton) {
            $reviewButton->click();
        }
    }

    public function approveArticle(string $title): void
    {
        $row = $this->findPendingArticleRow($title);
        if (!$row instanceof \Behat\Mink\Element\NodeElement) {
            throw new \RuntimeException(sprintf('Cannot find article "%s" in pending review list', $title));
        }

        $approveButton = $row->find('css', 'a:contains("Approve"), .approve-action, .btn-success');
        if (null !== $approveButton) {
            $approveButton->click();
        }
    }

    public function rejectArticle(string $title): void
    {
        $row = $this->findPendingArticleRow($title);
        if (!$row instanceof \Behat\Mink\Element\NodeElement) {
            throw new \RuntimeException(sprintf('Cannot find article "%s" in pending review list', $title));
        }

        $rejectButton = $row->find('css', 'a:contains("Reject"), .reject-action, .btn-danger');
        if (null !== $rejectButton) {
            $rejectButton->click();
        }
    }

    public function hasEmptyPendingReviewMessage(): bool
    {
        return $this->hasElement('empty_pending_message') || $this->hasNoResultsMessage();
    }

    public function hasReviewStatistics(): bool
    {
        return $this->hasElement('statistics_section');
    }

    public function getStatistic(string $statisticName): string|null
    {
        $statisticElement = $this->session->getPage()->find('css', sprintf('[data-statistic="%s"], .statistic-%s', $statisticName, strtolower(str_replace(' ', '-', $statisticName))));

        return $statisticElement?->getText();
    }

    public function hasSection(string $sectionName): bool
    {
        $sectionId = strtolower(str_replace(' ', '-', $sectionName));
        $selectors = [
            sprintf('#%s', $sectionId),
            sprintf('.%s', $sectionId),
            sprintf('[data-section="%s"]', $sectionId),
        ];

        foreach ($selectors as $selector) {
            if ($this->session->getPage()->find('css', $selector)) {
                return true;
            }
        }

        // Fallback: look for heading containing the section name
        return str_contains($this->session->getPage()->getText(), $sectionName);
    }

    public function isPageLoadedSuccessfully(): bool
    {
        return 200 === $this->session->getStatusCode();
    }

    #[\Override]
    public function getUrl(array $urlParameters = []): string
    {
        return '/admin/editorials';
    }

    protected function getRouteName(): string
    {
        // Not using routes for now, using direct URL
        return '';
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function getDefinedElements(): array
    {
        return [
            'page' => '.editorial-dashboard, .content',
            'pending_articles_section' => '.pending-articles, #pending-articles, [data-section="pending-articles"]',
            'pending_articles_table' => 'table.review-grid, table[data-grid="review"], .review-articles table',
            'pending_articles_rows' => 'table.review-grid tbody tr, table[data-grid="review"] tbody tr, .review-articles table tbody tr',
            'empty_pending_message' => '.empty-state, .no-pending-articles',
            'statistics_section' => '.statistics, .stats, [data-section="statistics"]',
            'review_actions' => '.review-actions, .article-actions',
        ];
    }

    private function findPendingArticleRow(string $title): \Behat\Mink\Element\NodeElement|null
    {
        if (!$this->hasElement('pending_articles_table')) {
            return null;
        }

        $rows = $this->session->getPage()->findAll('css', $this->getDefinedElements()['pending_articles_rows']);

        foreach ($rows as $row) {
            if (str_contains($row->getText(), $title)) {
                return $row;
            }
        }

        return null;
    }

    private function hasNoResultsMessage(): bool
    {
        $page = $this->session->getPage();
        $pageText = $page->getText();

        $noResultsVariations = [
            'No results',
            'No articles found',
            'No articles pending review',
            'There are no articles to review',
            'Empty',
            'Nothing to review',
        ];

        foreach ($noResultsVariations as $variation) {
            if (false !== stripos($pageText, $variation)) {
                return true;
            }
        }

        return false;
    }
}
