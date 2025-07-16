<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Page\Admin\Editorial;

use App\Tests\BlogContext\Behat\Page\PageInterface;

interface DashboardPageInterface extends PageInterface
{
    public function hasArticlesPendingReview(): bool;

    public function getArticlesPendingReviewCount(): int;

    public function hasArticleInPendingList(string $title): bool;

    public function reviewArticle(string $title): void;

    public function approveArticle(string $title): void;

    public function rejectArticle(string $title): void;

    public function hasEmptyPendingReviewMessage(): bool;

    public function hasReviewStatistics(): bool;

    public function getStatistic(string $statisticName): string|null;

    public function hasSection(string $sectionName): bool;

    public function isPageLoadedSuccessfully(): bool;
}
