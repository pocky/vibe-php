<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Examples of ICU MessageFormat usage for documentation and testing purposes.
 */
final readonly class IcuTranslationExamples
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * Example of basic pluralization.
     */
    public function getArticleCount(int $count): string
    {
        return $this->translator->trans('blog.article.count', [
            'count' => $count,
        ], 'messages');
    }

    /**
     * Example of pluralization with status context.
     */
    public function getArticleCountByStatus(int $count, string $status): string
    {
        return $this->translator->trans('blog.article.count_by_status', [
            'count' => $count,
            'status' => $status,
        ], 'messages');
    }

    /**
     * Example of ordinal selection (1st, 2nd, 3rd, etc.).
     */
    public function getArticleRanking(int $position): string
    {
        return $this->translator->trans('blog.article.ranking', [
            'position' => $position,
        ], 'messages');
    }

    /**
     * Example of date formatting.
     */
    public function getCreationDate(\DateTimeInterface $date): string
    {
        return $this->translator->trans('blog.article.created_at', [
            'date' => $date,
        ], 'messages');
    }

    /**
     * Example of time-based pluralization.
     */
    public function getTimeAgo(int $minutes): string
    {
        return $this->translator->trans('blog.article.updated_recently', [
            'minutes' => $minutes,
        ], 'messages');
    }

    /**
     * Example of future time pluralization.
     */
    public function getPublishSchedule(int $days): string
    {
        return $this->translator->trans('blog.article.publish_in', [
            'days' => $days,
        ], 'messages');
    }

    /**
     * Example of number formatting.
     */
    public function getViewCount(int $count): string
    {
        return $this->translator->trans('blog.article.views', [
            'count' => $count,
        ], 'messages');
    }

    /**
     * Example of reading time estimation.
     */
    public function getReadingTime(int $minutes): string
    {
        return $this->translator->trans('blog.article.read_time', [
            'minutes' => $minutes,
        ], 'messages');
    }

    /**
     * Example of word count formatting.
     */
    public function getWordCount(int $words): string
    {
        return $this->translator->trans('blog.article.word_count', [
            'words' => $words,
        ], 'messages');
    }

    /**
     * Example of conditional selection based on status.
     */
    public function getVisibilityMessage(string $status): string
    {
        return $this->translator->trans('blog.article.visibility', [
            'status' => $status,
        ], 'messages');
    }

    /**
     * Example of complex nested ICU with multiple formatters.
     */
    public function getStatusInfo(
        string $status,
        \DateTimeInterface $date,
        \DateTimeInterface $time,
        int $views
    ): string {
        return $this->translator->trans('blog.article.status_info', [
            'status' => $status,
            'date' => $date,
            'time' => $time,
            'views' => $views,
        ], 'messages');
    }

    /**
     * Advanced example: Dynamic message based on article statistics.
     */
    public function getArticleStatistics(int $views, int $comments, float $rating): string
    {
        // This would use a complex ICU message combining multiple conditions
        return $this->translator->trans('blog.article.statistics', [
            'views' => $views,
            'comments' => $comments,
            'rating' => $rating,
            'popularity' => 1000 < $views ? 'high' : (100 < $views ? 'medium' : 'low'),
        ], 'messages');
    }
}
