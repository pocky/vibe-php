<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final readonly class ReviewDecision
{
    private const int MAX_REASON_LENGTH = 1000;
    private const string DECISION_APPROVED = 'approved';
    private const string DECISION_REJECTED = 'rejected';

    private function __construct(
        private string $decision,
        private string|null $reason = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (self::DECISION_REJECTED === $this->decision && null === $this->reason) {
            throw new \InvalidArgumentException('Rejection must include a reason');
        }

        if (null !== $this->reason) {
            $trimmedReason = trim($this->reason);

            if (self::DECISION_REJECTED === $this->decision && '' === $trimmedReason) {
                throw new \InvalidArgumentException('Rejection reason cannot be empty');
            }

            if (self::MAX_REASON_LENGTH < mb_strlen($this->reason)) {
                throw new \InvalidArgumentException(sprintf('Review reason cannot exceed %d characters', self::MAX_REASON_LENGTH));
            }
        }
    }

    public static function approve(string|null $reason = null): self
    {
        return new self(self::DECISION_APPROVED, $reason);
    }

    public static function reject(string|null $reason = null): self
    {
        return new self(self::DECISION_REJECTED, $reason);
    }

    public static function fromArray(array $data): self
    {
        $decision = $data['decision'] ?? '';
        $reason = $data['reason'] ?? null;

        return match ($decision) {
            self::DECISION_APPROVED => self::approve($reason),
            self::DECISION_REJECTED => self::reject($reason),
            default => throw new \InvalidArgumentException(sprintf('Invalid decision type: %s', $decision)),
        };
    }

    public function isApproved(): bool
    {
        return self::DECISION_APPROVED === $this->decision;
    }

    public function isRejected(): bool
    {
        return self::DECISION_REJECTED === $this->decision;
    }

    public function getReason(): string|null
    {
        return $this->reason;
    }

    public function getDecisionType(): string
    {
        return $this->decision;
    }

    public function toArray(): array
    {
        return [
            'decision' => $this->decision,
            'reason' => $this->reason,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->decision === $other->decision
            && $this->reason === $other->reason;
    }
}
