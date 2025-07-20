<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final class Timestamps
{
    public function __construct(
        private(set) \DateTimeImmutable $createdAt,
        private(set) \DateTimeImmutable $updatedAt,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->updatedAt < $this->createdAt) {
            throw new \InvalidArgumentException('Updated timestamp cannot be before created timestamp');
        }
    }

    public static function create(): self
    {
        $now = new \DateTimeImmutable();

        return new self($now, $now);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function withUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        return new self($this->createdAt, $updatedAt);
    }

    public function withUpdatedNow(): self
    {
        return new self($this->createdAt, new \DateTimeImmutable());
    }
}
