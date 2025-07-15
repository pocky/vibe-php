<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final readonly class EditorialComment
{
    private const int MAX_COMMENT_LENGTH = 2000;

    public function __construct(
        private string $comment,
        private string|null $selectedText = null,
        private int|null $positionStart = null,
        private int|null $positionEnd = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmedComment = trim($this->comment);

        if ('' === $trimmedComment) {
            throw new \InvalidArgumentException('Editorial comment cannot be empty');
        }

        if (self::MAX_COMMENT_LENGTH < mb_strlen($this->comment)) {
            throw new \InvalidArgumentException(sprintf('Editorial comment cannot exceed %d characters', self::MAX_COMMENT_LENGTH));
        }

        // Validate position data consistency
        $hasSelectedText = null !== $this->selectedText;
        $hasPositionStart = null !== $this->positionStart;
        $hasPositionEnd = null !== $this->positionEnd;

        if ($hasSelectedText !== $hasPositionStart || $hasSelectedText !== $hasPositionEnd) {
            throw new \InvalidArgumentException('Either provide all selection data or none');
        }

        if (null !== $this->positionStart && null !== $this->positionEnd) {
            if (0 > $this->positionStart || 0 > $this->positionEnd) {
                throw new \InvalidArgumentException('Position values must be non-negative');
            }

            if ($this->positionEnd <= $this->positionStart) {
                throw new \InvalidArgumentException('Position end must be greater than position start');
            }
        }
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getSelectedText(): string|null
    {
        return $this->selectedText;
    }

    public function getPositionStart(): int|null
    {
        return $this->positionStart;
    }

    public function getPositionEnd(): int|null
    {
        return $this->positionEnd;
    }

    public function hasSelection(): bool
    {
        return null !== $this->selectedText;
    }

    public function equals(self $other): bool
    {
        return $this->comment === $other->comment
            && $this->selectedText === $other->selectedText
            && $this->positionStart === $other->positionStart
            && $this->positionEnd === $other->positionEnd;
    }
}
