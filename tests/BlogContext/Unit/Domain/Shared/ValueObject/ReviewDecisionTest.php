<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\ValueObject\ReviewDecision;
use PHPUnit\Framework\TestCase;

final class ReviewDecisionTest extends TestCase
{
    public function testCreateApprovalDecision(): void
    {
        $decision = ReviewDecision::approve('Great article, ready for publication');

        $this->assertTrue($decision->isApproved());
        $this->assertFalse($decision->isRejected());
        $this->assertSame('Great article, ready for publication', $decision->getReason());
    }

    public function testCreateRejectionDecision(): void
    {
        $decision = ReviewDecision::reject('Needs significant improvements in structure and content');

        $this->assertFalse($decision->isApproved());
        $this->assertTrue($decision->isRejected());
        $this->assertSame('Needs significant improvements in structure and content', $decision->getReason());
    }

    public function testApprovalWithoutReasonIsAllowed(): void
    {
        $decision = ReviewDecision::approve();

        $this->assertTrue($decision->isApproved());
        $this->assertNull($decision->getReason());
    }

    public function testRejectionWithoutReasonThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection must include a reason');

        ReviewDecision::reject();
    }

    public function testEmptyRejectionReasonThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason cannot be empty');

        ReviewDecision::reject('');
    }

    public function testWhitespaceOnlyRejectionReasonThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason cannot be empty');

        ReviewDecision::reject('   ');
    }

    public function testReasonTooLongThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Review reason cannot exceed 1000 characters');

        $longReason = str_repeat('a', 1001);
        ReviewDecision::approve($longReason);
    }

    public function testEquals(): void
    {
        $decision1 = ReviewDecision::approve('Good work');
        $decision2 = ReviewDecision::approve('Good work');
        $decision3 = ReviewDecision::approve('Different reason');
        $decision4 = ReviewDecision::reject('Needs work');

        $this->assertTrue($decision1->equals($decision2));
        $this->assertFalse($decision1->equals($decision3));
        $this->assertFalse($decision1->equals($decision4));
    }

    public function testGetDecisionType(): void
    {
        $approval = ReviewDecision::approve();
        $rejection = ReviewDecision::reject('Needs improvement');

        $this->assertSame('approved', $approval->getDecisionType());
        $this->assertSame('rejected', $rejection->getDecisionType());
    }

    public function testFromArray(): void
    {
        $approvalData = [
            'decision' => 'approved',
            'reason' => 'Well written',
        ];

        $rejectionData = [
            'decision' => 'rejected',
            'reason' => 'Needs work',
        ];

        $approval = ReviewDecision::fromArray($approvalData);
        $rejection = ReviewDecision::fromArray($rejectionData);

        $this->assertTrue($approval->isApproved());
        $this->assertSame('Well written', $approval->getReason());

        $this->assertTrue($rejection->isRejected());
        $this->assertSame('Needs work', $rejection->getReason());
    }

    public function testFromArrayWithInvalidDecisionThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid decision type: pending');

        ReviewDecision::fromArray([
            'decision' => 'pending',
            'reason' => 'Still thinking',
        ]);
    }

    public function testToArray(): void
    {
        $decision = ReviewDecision::approve('Good article');

        $array = $decision->toArray();

        $this->assertSame([
            'decision' => 'approved',
            'reason' => 'Good article',
        ], $array);
    }
}
